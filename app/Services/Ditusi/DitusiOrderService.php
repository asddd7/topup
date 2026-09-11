<?php

namespace App\Services\Ditusi;

use App\Models\DitusiOrder;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class DitusiOrderService
{
    public function __construct(
        protected DitusiService $ditusi
    ) {
    }

    /**
     * Create or recover a DITUSI transaction for one OrderDetail.
     *
     * Rule:
     * 1 OrderDetail = 1 DitusiOrder
     */
    public function createFromOrderDetail(
        OrderDetail $orderDetail
    ): DitusiOrder {
        $orderDetail->loadMissing([
            'order',
            'item',
        ]);

        $order = $orderDetail->order;
        $item = $orderDetail->item;

        if (!$order) {
            throw new RuntimeException(
                "OrderDetail #{$orderDetail->id} tidak memiliki Order."
            );
        }

        if (!$item) {
            throw new RuntimeException(
                "OrderDetail #{$orderDetail->id} tidak memiliki Item."
            );
        }

        /*
         * DITUSI hanya diproses jika mapping item aktif.
         */
        if (!$item->ditusi_enabled) {
            throw new RuntimeException(
                "DITUSI tidak aktif untuk Item #{$item->id}."
            );
        }

        $productCode = trim(
            (string) $item->ditusi_product_code
        );

        if ($productCode === '') {
            throw new RuntimeException(
                "DITUSI product code belum diisi untuk Item #{$item->id}."
            );
        }

        /*
         * Deterministic reference ID.
         *
         * Retry terhadap OrderDetail yang sama harus menggunakan
         * reference yang sama agar tidak membuat transaksi ganda.
         */
        $partnerReference = sprintf(
            'DT-%s-%s',
            $order->id,
            $orderDetail->id
        );

        /*
         * Ambil record yang sudah ada terlebih dahulu.
         */
        $existing = DitusiOrder::query()
            ->where('order_detail_id', $orderDetail->id)
            ->first();

        if ($existing) {
            Log::info('DITUSI order sudah ada, menggunakan transaksi existing.', [
                'ditusi_order_id' => $existing->id,
                'order_id' => $order->id,
                'order_detail_id' => $orderDetail->id,
                'transaction_reference_id' => $existing->transaction_reference_id,
                'ditusi_transaction_id' => $existing->ditusi_transaction_id,
                'status' => $existing->status,
            ]);

            /*
             * Kalau transaction ID sudah ada, tidak boleh create ulang.
             */
            if ($existing->ditusi_transaction_id) {
                return $existing;
            }
        }

        /*
         * Gunakan transaction DB hanya untuk membuat reservation row.
         * HTTP request ke DITUSI TIDAK dilakukan di dalam transaction DB.
         */
        $ditusiOrder = DB::transaction(function () use (
            $order,
            $orderDetail,
            $item,
            $partnerReference,
            $productCode
        ) {
            $locked = DitusiOrder::query()
                ->where('order_detail_id', $orderDetail->id)
                ->lockForUpdate()
                ->first();

            if ($locked) {
                return $locked;
            }

            return DitusiOrder::create([
                'order_id' => $order->id,
                'order_detail_id' => $orderDetail->id,
                'item_id' => $item->id,
                'transaction_reference_id' => $partnerReference,
                'product_code' => $productCode,
                'amount' => max(
                    1,
                    (int) $orderDetail->qty
                ),
                'initial_price' => null,
                'status' => 'pending',
                'request_payload' => null,
                'response_payload' => null,
                'voucher_code' => null,
                'error_message' => null,
                'attempts' => 0,
                'completed_at' => null,
            ]);
        });

        /*
         * Kalau record ternyata sudah memiliki transaction ID,
         * langsung gunakan record tersebut.
         */
        if ($ditusiOrder->ditusi_transaction_id) {
            return $ditusiOrder;
        }

        /*
         * Buat formDetails dari player_data Order.
         */
        $formDetails = $this->buildFormDetails($order->player_data);

        if (empty($formDetails)) {
            throw new RuntimeException(
                "Player data Order #{$order->id} tidak dapat digunakan sebagai formDetails DITUSI."
            );
        }

        /*
         * Amount DITUSI = quantity.
         *
         * Harga final ditentukan DITUSI berdasarkan productCode.
         */
        $amount = max(
            1,
            (int) $orderDetail->qty
        );

        $requestPayload = [
            'productCode' => $productCode,
            'amount' => $amount,
            'transactionReferenceId' => $partnerReference,
            'formDetails' => $formDetails,
        ];

        /*
         * initialPrice menggunakan harga order detail jika tersedia.
         *
         * DITUSI mendokumentasikan field ini sebagai optional.
         */
        $initialPrice = null;

        /*
         * Tandai sedang melakukan create dan naikkan attempts.
         */
        $ditusiOrder->update([
            'status' => 'processing',
            'request_payload' => $requestPayload,
            'error_message' => null,
            'attempts' => ((int) $ditusiOrder->attempts) + 1,
        ]);

        Log::info('Membuat transaksi DITUSI dari OrderDetail.', [
            'ditusi_order_id' => $ditusiOrder->id,
            'order_id' => $order->id,
            'order_detail_id' => $orderDetail->id,
            'product_code' => $productCode,
            'amount' => $amount,
            'transaction_reference_id' => $partnerReference,
            'form_details' => $formDetails,
            'initial_price' => $initialPrice,
        ]);

        try {
            $response = $this->ditusi->createTransaction(
                productCode: $productCode,
                amount: $amount,
                transactionReferenceId: $partnerReference,
                formDetails: $formDetails,
                initialPrice: null,
            );
        } catch (Throwable $e) {
            /*
             * Jangan langsung membuat transaksi kedua.
             *
             * Setelah error, kita coba recovery menggunakan
             * transaction reference yang sama.
             */
            Log::warning('DITUSI create transaction gagal, mencoba recovery.', [
                'ditusi_order_id' => $ditusiOrder->id,
                'transaction_reference_id' => $partnerReference,
                'error' => $e->getMessage(),
            ]);

            try {
                $recovered = $this->ditusi->getTransactionStatus(
                    $partnerReference
                );

                $recoveredTransaction = $this->extractTransactionData(
                    $recovered
                );

                if (!empty($recoveredTransaction['transactionId'])) {
                    return $this->saveTransactionResponse(
                        $ditusiOrder,
                        $recovered,
                        $recoveredTransaction
                    );
                }
            } catch (Throwable $recoveryException) {
                Log::warning(
                    'Recovery transaksi DITUSI juga gagal.',
                    [
                        'ditusi_order_id' => $ditusiOrder->id,
                        'transaction_reference_id' => $partnerReference,
                        'error' => $recoveryException->getMessage(),
                    ]
                );
            }

            $ditusiOrder->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        $transactionData = $this->extractTransactionData(
            $response
        );

        return $this->saveTransactionResponse(
            $ditusiOrder,
            $response,
            $transactionData
        );
    }

    /**
     * Check an existing DITUSI transaction status.
     */
    public function checkStatus(
        DitusiOrder $ditusiOrder
    ): DitusiOrder {
        if (!$ditusiOrder->ditusi_transaction_id) {
            throw new RuntimeException(
                "DITUSI Order #{$ditusiOrder->id} belum memiliki transaction ID."
            );
        }

        if ($this->isFinalStatus($ditusiOrder->status)) {
            return $ditusiOrder;
        }

        Log::info('Checking DITUSI transaction status.', [
            'ditusi_order_id' => $ditusiOrder->id,
            'transaction_id' => $ditusiOrder->ditusi_transaction_id,
            'current_status' => $ditusiOrder->status,
        ]);

        try {
            $response = $this->ditusi->getTransactionStatus(
                $ditusiOrder->ditusi_transaction_id
            );

            $transactionData = $this->extractTransactionData(
                $response
            );

            return $this->saveTransactionResponse(
                $ditusiOrder,
                $response,
                $transactionData
            );
        } catch (Throwable $e) {
            $ditusiOrder->update([
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Save DITUSI create/status response.
     */
    protected function saveTransactionResponse(
        DitusiOrder $ditusiOrder,
        array $response,
        array $transactionData
    ): DitusiOrder {
        $ditusiTransactionId =
            $transactionData['transactionId']
            ?? $ditusiOrder->ditusi_transaction_id;

        $rawStatus = strtoupper(
            trim(
                (string) (
                    $transactionData['transactionStatus']
                    ?? ''
                )
            )
        );

        $status = $this->normalizeStatus(
            $rawStatus
        );

        $voucherCode = $transactionData['voucherCode']
            ?? null;

        $updates = [
            'response_payload' => $response,
            'status' => $status,
            'error_message' => null,
        ];

        if ($ditusiTransactionId) {
            $updates['ditusi_transaction_id'] =
                $ditusiTransactionId;
        }

        if ($voucherCode !== null) {
            $updates['voucher_code'] =
                is_array($voucherCode)
                    ? $voucherCode
                    : [$voucherCode];
        }

        if ($status === 'success' || $status === 'refunded') {
            $updates['completed_at'] =
                $ditusiOrder->completed_at ?? now();
        } else {
            $updates['completed_at'] = null;
        }

        $ditusiOrder->update($updates);

        Log::info('DITUSI order berhasil diperbarui.', [
            'ditusi_order_id' => $ditusiOrder->id,
            'transaction_id' => $ditusiOrder->ditusi_transaction_id,
            'raw_status' => $rawStatus,
            'status' => $status,
            'voucher_code' => $voucherCode,
        ]);

        return $ditusiOrder->fresh();
    }

    /**
     * Extract transaction data from both create and status responses.
     */
    protected function extractTransactionData(
        array $response
    ): array {
        $data = $response['data'] ?? [];

        if (!is_array($data)) {
            return [];
        }

        /*
         * Some responses may put transaction data directly
         * inside data, while others may wrap it differently.
         */
        return [
            'transactionId' =>
                $data['transactionId']
                ?? $data['transaction_id']
                ?? null,

            'transactionReferenceId' =>
                $data['transactionReferenceId']
                ?? $data['partnerReferenceId']
                ?? $data['originalTransactionReferenceId']
                ?? null,

            'grossAmount' =>
                $data['grossAmount']
                ?? $data['gross_amount']
                ?? null,

            'transactionStatus' =>
                $data['transactionStatus']
                ?? $data['statusTransaction']
                ?? $data['status']
                ?? null,

            'voucherCode' =>
                $data['voucherCode']
                ?? $data['voucher_code']
                ?? null,

            'transactionTime' =>
                $data['transactionTime']
                ?? null,
        ];
    }

    /**
     * Convert DITUSI status to our internal status.
     */
    protected function normalizeStatus(
        string $status
    ): string {
        return match ($status) {
            'SUCCESS',
            'SUCCESSFUL',
            'COMPLETED',
            'COMPLETE' => 'success',

            'REFUNDED',
            'REFUND' => 'refunded',

            'PROCESS',
            'PROCESSING' => 'processing',

            'PENDING ORDER',
            'PENDING',
            'WAITING PAYMENT' => 'pending',

            'FAILED',
            'FAIL',
            'ERROR' => 'failed',

            default => 'processing',
        };
    }

    /**
     * Check whether an internal status is final.
     */
    protected function isFinalStatus(
        string $status
    ): bool {
        return in_array(
            strtolower(trim($status)),
            [
                'success',
                'refunded',
                'failed',
            ],
            true
        );
    }

    /**
     * Build DITUSI formDetails from Order.player_data.
     *
     * We preserve the original order data and map common keys
     * to the field names expected by DITUSI.
     */
protected function buildFormDetails(
    mixed $playerData
): array {
    if (is_string($playerData)) {
        $decoded = json_decode(
            $playerData,
            true
        );

        $playerData = is_array($decoded)
            ? $decoded
            : [];
    }

    if (!is_array($playerData)) {
        return [];
    }

    $formDetails = [];

    foreach ($playerData as $key => $value) {
        if (
            $value === null ||
            $value === ''
        ) {
            continue;
        }

        $formDetails[(string) $key] = (string) $value;
    }

    return $formDetails;
}

    /**
     * Return the first non-empty value from possible keys.
     */
    protected function firstValue(
        array $data,
        array $keys
    ): mixed {
        foreach ($keys as $key) {
            if (
                array_key_exists($key, $data)
                && $data[$key] !== null
                && $data[$key] !== ''
            ) {
                return $data[$key];
            }
        }

        return null;
    }
}

