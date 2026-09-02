<?php

namespace App\Services\MooGold;

use App\Jobs\CheckMooGoldOrderStatus;
use App\Models\MooGoldOrder;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MooGoldOrderService
{
    /**
     * Creation lease.
     *
     * Selama record masih berstatus "creating" dan
     * last_attempt_at masih baru, worker lain TIDAK boleh
     * menjalankan create_order().
     *
     * HTTP timeout MooGold saat ini 30 detik.
     * 120 detik memberikan buffer yang cukup untuk kondisi
     * worker / network.
     */
    protected int $creationLeaseSeconds = 120;

    public function __construct(
        protected MooGoldService $mooGold
    ) {
    }

    /**
     * =========================================================
     * CREATE MOOGOLD ORDER
     * =========================================================
     *
     * IDEMPOTENCY:
     *
     * 1 OrderDetail
     *      =
     * 1 MooGoldOrder
     *      =
     * 1 Partner Order ID
     *
     * Partner Order ID:
     *
     * MG-{order_id}-{order_detail_id}
     *
     * Contoh:
     *
     * MG-26-26
     *
     * RULE:
     *
     * - Jangan pernah generate Partner Order ID baru saat retry.
     * - Selalu recovery berdasarkan Partner Order ID.
     * - Jangan HTTP retry otomatis pada create_order.
     * - Timeout/error dianggap UNKNOWN sampai recovery memastikan
     *   transaksi memang tidak ditemukan.
     */
    public function createFromOrderDetail(
        OrderDetail $orderDetail
    ): MooGoldOrder {

        /*
        |--------------------------------------------------------------------------
        | LOAD RELATION
        |--------------------------------------------------------------------------
        */

        $orderDetail->loadMissing([
            'order',
            'item',
        ]);

        $order = $orderDetail->order;
        $item = $orderDetail->item;

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (!$order) {
            throw new RuntimeException(
                'Order tidak ditemukan.'
            );
        }

        if (!$item) {
            throw new RuntimeException(
                'Item pada OrderDetail tidak ditemukan.'
            );
        }

        if ($order->status !== 'Paid') {
            throw new RuntimeException(
                'Order belum berstatus Paid. MooGold tidak boleh diproses.'
            );
        }

        if (
            empty($item->moogold_category_id) ||
            empty($item->moogold_variation_id)
        ) {
            throw new RuntimeException(
                'Item "' .
                $item->item_name .
                '" belum memiliki mapping MooGold.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PLAYER DATA
        |--------------------------------------------------------------------------
        */

        $playerData = $order->player_data;

        if (!is_array($playerData)) {
            $playerData = json_decode(
                (string) $playerData,
                true
            );
        }

        if (!is_array($playerData)) {
            throw new RuntimeException(
                'Player data order tidak valid.'
            );
        }

        $userId =
            $playerData['uid']
            ?? $playerData['user_id']
            ?? $playerData['User ID']
            ?? null;

        $server =
            $playerData['server']
            ?? $playerData['server_id']
            ?? $playerData['Server ID']
            ?? null;

        if (empty($userId)) {
            throw new RuntimeException(
                'Player UID / User ID belum tersedia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DETERMINISTIC PARTNER ORDER ID
        |--------------------------------------------------------------------------
        */

        $partnerOrderId =
            $this->buildPartnerOrderId(
                $order->id,
                $orderDetail->id
            );

        /*
        |--------------------------------------------------------------------------
        | REQUEST PAYLOAD SNAPSHOT
        |--------------------------------------------------------------------------
        */

        $requestPayload = [
            'category' =>
                (string) $item->moogold_category_id,

            'product-id' =>
                (string) $item->moogold_variation_id,

            'quantity' =>
                (string) $orderDetail->qty,

            'User ID' =>
                (string) $userId,
        ];

        if (
            $server !== null &&
            $server !== ''
        ) {
            $requestPayload['Server ID'] =
                (string) $server;
        }

        /*
        |--------------------------------------------------------------------------
        | FIND / CREATE LOCAL MAPPING
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder =
            MooGoldOrder::firstOrCreate(
                [
                    'order_detail_id' =>
                        $orderDetail->id,
                ],
                [
                    'order_id' =>
                        $order->id,

                    'item_id' =>
                        $item->id,

                    'external_order_id' =>
                        $partnerOrderId,

                    'moogold_category_id' =>
                        $item->moogold_category_id,

                    'moogold_product_id' =>
                        $item->moogold_product_id,

                    'moogold_variation_id' =>
                        $item->moogold_variation_id,

                    'moogold_status' =>
                        'pending',

                    'request_payload' =>
                        $requestPayload,

                    'attempts' =>
                        0,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | =========================================================
        | STEP 1 — SHORT DB LOCK / CLAIM
        | =========================================================
        |
        | Tidak ada HTTP request di dalam transaction.
        |
        | Transaction hanya digunakan untuk:
        |
        | - lock row
        | | check existing MooGold ID
        | | recovery/claim state
        | | mark creating
        |
        |--------------------------------------------------------------------------
        */

        $claim = DB::transaction(
            function () use (
                $mooGoldOrder,
                $partnerOrderId,
                $requestPayload
            ) {

                $lockedOrder =
                    MooGoldOrder::query()
                        ->where(
                            'id',
                            $mooGoldOrder->id
                        )
                        ->lockForUpdate()
                        ->first();

                if (!$lockedOrder) {
                    throw new RuntimeException(
                        'MooGoldOrder tidak ditemukan saat locking.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | ALWAYS USE DETERMINISTIC PARTNER ID
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedOrder->external_order_id !==
                    $partnerOrderId
                ) {
                    $lockedOrder->external_order_id =
                        $partnerOrderId;
                }

                /*
                |--------------------------------------------------------------------------
                | ALREADY CREATED
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $lockedOrder->moogold_order_id
                    )
                ) {

                    return [
                        'action' => 'existing',
                        'order' => $lockedOrder->fresh(),
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | RECENT CREATE IN PROGRESS
                |--------------------------------------------------------------------------
                |
                | Worker lain mungkin sedang berada di:
                |
                | createOrder()
                |
                | Jangan membuat order kedua.
                */

                if (
                    $this->isCreationLeaseActive(
                        $lockedOrder
                    )
                ) {

                    Log::warning(
                        'MooGold order masih dalam creation lease. '
                        . 'Create kedua dibatalkan.',
                        [
                            'moo_gold_order_id' =>
                                $lockedOrder->id,

                            'partner_order_id' =>
                                $partnerOrderId,

                            'last_attempt_at' =>
                                $lockedOrder->last_attempt_at,
                        ]
                    );

                    return [
                        'action' => 'in_progress',
                        'order' => $lockedOrder->fresh(),
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | UPDATE SNAPSHOT
                |--------------------------------------------------------------------------
                */

                $lockedOrder->request_payload =
                    $requestPayload;

                /*
                |--------------------------------------------------------------------------
                | CLAIM CREATE
                |--------------------------------------------------------------------------
                */

                $lockedOrder->moogold_status =
                    'creating';

                $lockedOrder->error_message =
                    null;

                $lockedOrder->last_attempt_at =
                    now();

                $lockedOrder->attempts =
                    ((int) $lockedOrder->attempts) + 1;

                $lockedOrder->save();

                return [
                    'action' => 'create',
                    'order' => $lockedOrder->fresh(),
                ];
            }
        );

        /*
        |--------------------------------------------------------------------------
        | EXISTING ORDER
        |--------------------------------------------------------------------------
        */

        if ($claim['action'] === 'existing') {

            $result =
                $claim['order']->fresh();

            $this->scheduleStatusCheck(
                $result
            );

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | ANOTHER WORKER IS CREATING
        |--------------------------------------------------------------------------
        */

        if ($claim['action'] === 'in_progress') {

            /*
            |--------------------------------------------------------------------------
            | Jangan create.
            |
            | Kita lempar exception agar queue dapat retry.
            |--------------------------------------------------------------------------
            */

            throw new RuntimeException(
                'MooGold order sedang diproses oleh worker lain. '
                . 'Create order kedua dibatalkan.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | =========================================================
        | STEP 2 — RECOVERY BEFORE CREATE
        | =========================================================
        |
        | Walaupun kita sudah claim create, kita tetap mencari
        | Partner Order ID terlebih dahulu.
        |
        | Ini melindungi kasus:
        |
        | previous request berhasil
        | ↓
        | Laravel crash sebelum save
        | ↓
        | worker hidup kembali
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder =
            $claim['order']->fresh();

        try {

            $recovered =
                $this->recoverByPartnerOrderId(
                    $mooGoldOrder
                );

            if ($recovered) {

                Log::info(
                    'MooGold order ditemukan sebelum create '
                    . 'melalui Partner Order ID.',
                    [
                        'moo_gold_order_id' =>
                            $recovered->id,

                        'moogold_order_id' =>
                            $recovered->moogold_order_id,

                        'partner_order_id' =>
                            $recovered->external_order_id,
                    ]
                );

                return $recovered->fresh();
            }

        } catch (Throwable $recoveryError) {

            /*
            |--------------------------------------------------------------------------
            | RECOVERY ERROR
            |--------------------------------------------------------------------------
            |
            | Jangan create order kalau endpoint recovery sendiri
            | tidak dapat memastikan kondisi transaksi.
            |--------------------------------------------------------------------------
            */

            $mooGoldOrder->update([
                'moogold_status' =>
                    'unknown',

                'error_message' =>
                    'Recovery sebelum create gagal: ' .
                    $recoveryError->getMessage(),
            ]);

            Log::error(
                'Recovery sebelum create gagal. '
                . 'Create MooGold dibatalkan demi keamanan.',
                [
                    'moo_gold_order_id' =>
                        $mooGoldOrder->id,

                    'partner_order_id' =>
                        $partnerOrderId,

                    'error' =>
                        $recoveryError->getMessage(),
                ]
            );

            throw $recoveryError;
        }

        /*
        |--------------------------------------------------------------------------
        | =========================================================
        | STEP 3 — CREATE MOO GOLD
        | =========================================================
        */

        try {

            $response =
                $this->mooGold->createOrder(
                    (int)
                    $item->moogold_category_id,

                    $partnerOrderId,

                    (string)
                    $item->moogold_variation_id,

                    (int)
                    $orderDetail->qty,

                    (string)
                    $userId,

                    $server !== null &&
                    $server !== ''
                        ? (string) $server
                        : null
                );

        } catch (Throwable $createError) {

            /*
            |--------------------------------------------------------------------------
            | =====================================================
            | CRITICAL RECOVERY
            | =====================================================
            |
            | Jangan langsung failed.
            |
            | MooGold mungkin sudah membuat order tetapi response
            | tidak sampai Laravel.
            |--------------------------------------------------------------------------
            */

            Log::warning(
                'Create MooGold mengalami error. '
                . 'Recovery Partner Order ID dijalankan.',
                [
                    'moo_gold_order_id' =>
                        $mooGoldOrder->id,

                    'partner_order_id' =>
                        $partnerOrderId,

                    'error' =>
                        $createError->getMessage(),
                ]
            );

            try {

                $recovered =
                    $this->recoverByPartnerOrderId(
                        $mooGoldOrder->fresh()
                    );

                if ($recovered) {

                    Log::warning(
                        'Order MooGold ditemukan setelah create error.',
                        [
                            'moo_gold_order_id' =>
                                $recovered->id,

                            'moogold_order_id' =>
                                $recovered->moogold_order_id,

                            'partner_order_id' =>
                                $recovered->external_order_id,
                        ]
                    );

                    return $recovered->fresh();
                }

            } catch (Throwable $recoveryError) {

                /*
                |--------------------------------------------------------------------------
                | UNKNOWN
                |--------------------------------------------------------------------------
                |
                | Kita TIDAK tahu apakah create berhasil.
                |
                | Status harus UNKNOWN.
                |--------------------------------------------------------------------------
                */

                $mooGoldOrder->update([
                    'moogold_status' =>
                        'unknown',

                    'error_message' =>
                        $recoveryError->getMessage(),
                ]);

                Log::error(
                    'Recovery setelah create error juga gagal. '
                    . 'Transaksi dianggap UNKNOWN.',
                    [
                        'moo_gold_order_id' =>
                            $mooGoldOrder->id,

                        'partner_order_id' =>
                            $partnerOrderId,

                        'create_error' =>
                            $createError->getMessage(),

                        'recovery_error' =>
                            $recoveryError->getMessage(),
                    ]
                );

                throw $createError;
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE ERROR + RECOVERY CONFIRMED NOT FOUND
            |--------------------------------------------------------------------------
            |
            | Tetap UNKNOWN.
            |
            | Queue retry akan menggunakan Partner Order ID yang SAMA.
            |--------------------------------------------------------------------------
            */

            $mooGoldOrder->update([
                'moogold_status' =>
                    'unknown',

                'error_message' =>
                    $createError->getMessage(),
            ]);

            throw $createError;
        }

        /*
        |--------------------------------------------------------------------------
        | EXTRACT RESPONSE
        |--------------------------------------------------------------------------
        */

        $moogoldOrderId =
            $this->extractOrderId(
                $response
            );

        $moogoldStatus =
            $this->extractStatus(
                $response
            );

        /*
        |--------------------------------------------------------------------------
        | RESPONSE WITHOUT ORDER ID
        |--------------------------------------------------------------------------
        |
        | Jangan langsung failed.
        |--------------------------------------------------------------------------
        */

        if (empty($moogoldOrderId)) {

            Log::warning(
                'MooGold create_order tidak mengembalikan Order ID.',
                [
                    'moo_gold_order_id' =>
                        $mooGoldOrder->id,

                    'partner_order_id' =>
                        $partnerOrderId,

                    'response' =>
                        $response,
                ]
            );

            $mooGoldOrder->update([
                'response_payload' =>
                    $response,

                'moogold_status' =>
                    'unknown',
            ]);

            try {

                $recovered =
                    $this->recoverByPartnerOrderId(
                        $mooGoldOrder->fresh()
                    );

                if ($recovered) {
                    return $recovered->fresh();
                }

            } catch (Throwable $recoveryError) {

                $mooGoldOrder->update([
                    'moogold_status' =>
                        'unknown',

                    'error_message' =>
                        $recoveryError->getMessage(),
                ]);

                throw $recoveryError;
            }

            throw new RuntimeException(
                'MooGold tidak mengembalikan Order ID '
                . 'dan transaksi belum dapat direcovery berdasarkan '
                . 'Partner Order ID.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE SUCCESSFUL CREATE
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder->update([
            'response_payload' =>
                $response,

            'ordered_at' =>
                $mooGoldOrder->ordered_at
                ?? now(),

            'moogold_status' =>
                $moogoldStatus,

            'moogold_order_id' =>
                $moogoldOrderId,

            'error_message' =>
                null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | SYNC MAIN ORDER
        |--------------------------------------------------------------------------
        */

        $order->update([
            'moogold_order_id' =>
                $moogoldOrderId,

            'moogold_status' =>
                $moogoldStatus,

            'moogold_response' =>
                $response,

            'moogold_ordered_at' =>
                $order->moogold_ordered_at
                ?? now(),

            'status' =>
                $this->mapOrderStatus(
                    $moogoldStatus
                ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | STATUS CHECK
        |--------------------------------------------------------------------------
        */

        $fresh =
            $mooGoldOrder->fresh();

        $this->scheduleStatusCheck(
            $fresh
        );

        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        Log::info(
            'MooGold order berhasil dibuat.',
            [
                'order_id' =>
                    $order->id,

                'order_detail_id' =>
                    $orderDetail->id,

                'moo_gold_order_id' =>
                    $fresh->id,

                'moogold_order_id' =>
                    $fresh->moogold_order_id,

                'partner_order_id' =>
                    $fresh->external_order_id,

                'moogold_status' =>
                    $fresh->moogold_status,

                'attempts' =>
                    $fresh->attempts,
            ]
        );

        return $fresh;
    }


    /**
     * =========================================================
     * BUILD PARTNER ORDER ID
     * =========================================================
     */
    protected function buildPartnerOrderId(
        int $orderId,
        int $orderDetailId
    ): string {

        return 'MG-' .
            $orderId .
            '-' .
            $orderDetailId;
    }


    /**
     * =========================================================
     * CREATION LEASE CHECK
     * =========================================================
     */
    protected function isCreationLeaseActive(
        MooGoldOrder $mooGoldOrder
    ): bool {

        if (
            $mooGoldOrder->moogold_status !==
            'creating'
        ) {
            return false;
        }

        if (
            empty(
                $mooGoldOrder->last_attempt_at
            )
        ) {
            return false;
        }

        return $mooGoldOrder->last_attempt_at
            ->gt(
                now()->subSeconds(
                    $this->creationLeaseSeconds
                )
            );
    }


    /**
     * =========================================================
     * RECOVER BY PARTNER ORDER ID
     * =========================================================
     *
     * IMPORTANT:
     *
     * null = endpoint berhasil dan transaksi tidak ditemukan.
     *
     * exception = endpoint recovery gagal / kondisi UNKNOWN.
     */
    protected function recoverByPartnerOrderId(
        MooGoldOrder $mooGoldOrder
    ): ?MooGoldOrder {

        $partnerOrderId =
            trim(
                (string)
                $mooGoldOrder->external_order_id
            );

        if ($partnerOrderId === '') {
            throw new RuntimeException(
                'Partner Order ID tidak tersedia.'
            );
        }

        Log::info(
            'Recovery MooGold berdasarkan Partner Order ID.',
            [
                'moo_gold_order_id' =>
                    $mooGoldOrder->id,

                'partner_order_id' =>
                    $partnerOrderId,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | CALL MOO GOLD
        |--------------------------------------------------------------------------
        */

        $response =
            $this->mooGold->orderByPartnerOrderId(
                $partnerOrderId
            );

        /*
        |--------------------------------------------------------------------------
        | EXTRACT ORDER ID
        |--------------------------------------------------------------------------
        */

        $moogoldOrderId =
            $this->extractOrderId(
                $response
            );

        /*
        |--------------------------------------------------------------------------
        | NOT FOUND
        |--------------------------------------------------------------------------
        */

        if (
            $moogoldOrderId === null ||
            $moogoldOrderId === ''
        ) {

            Log::info(
                'Partner Order ID belum ditemukan di MooGold.',
                [
                    'moo_gold_order_id' =>
                        $mooGoldOrder->id,

                    'partner_order_id' =>
                        $partnerOrderId,

                    'response' =>
                        $response,
                ]
            );

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            $this->extractStatus(
                $response
            );

        /*
        |--------------------------------------------------------------------------
        | SAVE RECOVERED ORDER
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder->update([
            'external_order_id' =>
                $partnerOrderId,

            'moogold_order_id' =>
                $moogoldOrderId,

            'moogold_status' =>
                $status,

            'response_payload' =>
                $response,

            'ordered_at' =>
                $mooGoldOrder->ordered_at
                ?? now(),

            'error_message' =>
                null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | SYNC MAIN ORDER
        |--------------------------------------------------------------------------
        */

        $order =
            $mooGoldOrder->order;

        if ($order) {

            $order->update([
                'moogold_order_id' =>
                    $moogoldOrderId,

                'moogold_status' =>
                    $status,

                'moogold_response' =>
                    $response,

                'moogold_ordered_at' =>
                    $order->moogold_ordered_at
                    ?? now(),

                'status' =>
                    $this->mapOrderStatus(
                        $status
                    ),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        Log::info(
            'MooGold order berhasil direcovery.',
            [
                'moo_gold_order_id' =>
                    $mooGoldOrder->id,

                'moogold_order_id' =>
                    $moogoldOrderId,

                'partner_order_id' =>
                    $partnerOrderId,

                'status' =>
                    $status,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | STATUS CHECK
        |--------------------------------------------------------------------------
        */

        $fresh =
            $mooGoldOrder->fresh();

        $this->scheduleStatusCheck(
            $fresh
        );

        return $fresh;
    }


    /**
     * =========================================================
     * SCHEDULE STATUS CHECK
     * =========================================================
     */
    protected function scheduleStatusCheck(
        MooGoldOrder $mooGoldOrder
    ): void {

        if (
            empty(
                $mooGoldOrder->moogold_order_id
            )
        ) {
            return;
        }

        if (
            $this->isFinalStatus(
                (string)
                $mooGoldOrder->moogold_status
            )
        ) {
            return;
        }

        CheckMooGoldOrderStatus::dispatch(
            $mooGoldOrder->id
        )->delay(
            now()->addMinutes(2)
        );

        Log::info(
            'Status check MooGold dijadwalkan.',
            [
                'moo_gold_order_id' =>
                    $mooGoldOrder->id,

                'moogold_order_id' =>
                    $mooGoldOrder->moogold_order_id,

                'partner_order_id' =>
                    $mooGoldOrder->external_order_id,

                'status' =>
                    $mooGoldOrder->moogold_status,
            ]
        );
    }


    /**
     * =========================================================
     * CHECK STATUS
     * =========================================================
     */
    public function checkStatus(
        MooGoldOrder $mooGoldOrder
    ): MooGoldOrder {

        if (
            empty(
                $mooGoldOrder->moogold_order_id
            )
        ) {
            throw new RuntimeException(
                'MooGold Order ID belum tersedia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL STATUS
        |--------------------------------------------------------------------------
        */

        $currentStatus =
            strtolower(
                trim(
                    (string)
                    $mooGoldOrder->moogold_status
                )
            );

        if (
            $this->isFinalStatus(
                $currentStatus
            )
        ) {
            return $mooGoldOrder->fresh();
        }

        /*
        |--------------------------------------------------------------------------
        | REQUEST STATUS
        |--------------------------------------------------------------------------
        */

        $response =
            $this->mooGold->order(
                (int)
                $mooGoldOrder->moogold_order_id
            );

        $status =
            $this->extractStatus(
                $response
            );

        /*
        |--------------------------------------------------------------------------
        | SAVE RESPONSE
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder->response_payload =
            $response;

        $mooGoldOrder->moogold_status =
            $status;

        $order =
            $mooGoldOrder->order;

        /*
        |--------------------------------------------------------------------------
        | MAP STATUS
        |--------------------------------------------------------------------------
        */

        switch ($status) {

            case 'pending':
            case 'processing':
            case 'sending':

                if ($order) {
                    $order->status =
                        'Processing';
                }

                break;

            case 'success':
            case 'successful':
            case 'completed':
            case 'complete':
            case 'successfully':

                if ($order) {
                    $order->status =
                        'Completed';
                }

                if (
                    !$mooGoldOrder->completed_at
                ) {
                    $mooGoldOrder->completed_at =
                        now();
                }

                $mooGoldOrder->error_message =
                    null;

                break;

            case 'refunded':

                if ($order) {
                    $order->status =
                        'Cancelled';
                }

                $mooGoldOrder->error_message =
                    'MooGold order refunded.';

                break;

            case 'failed':

                if ($order) {
                    $order->status =
                        'Cancelled';
                }

                $mooGoldOrder->error_message =
                    'MooGold order failed.';

                break;

            default:

                Log::warning(
                    'Unknown MooGold order status.',
                    [
                        'moo_gold_order_id' =>
                            $mooGoldOrder->id,

                        'moogold_order_id' =>
                            $mooGoldOrder->moogold_order_id,

                        'partner_order_id' =>
                            $mooGoldOrder->external_order_id,

                        'status' =>
                            $status,

                        'response' =>
                            $response,
                    ]
                );

                break;
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder->save();

        /*
        |--------------------------------------------------------------------------
        | MAIN ORDER
        |--------------------------------------------------------------------------
        */

        if ($order) {

            $order->moogold_order_id =
                $mooGoldOrder->moogold_order_id;

            $order->moogold_status =
                $status;

            $order->moogold_response =
                $response;

            $order->save();
        }

        return $mooGoldOrder->fresh();
    }


    /**
     * =========================================================
     * MAP MOOGOLD STATUS
     * =========================================================
     */
    protected function mapOrderStatus(
        string $status
    ): string {

        return match (
            strtolower(
                trim($status)
            )
        ) {

            'success',
            'successful',
            'completed',
            'complete',
            'successfully'
                => 'Completed',

            'failed',
            'refunded'
                => 'Cancelled',

            default
                => 'Processing',
        };
    }


    /**
     * =========================================================
     * FINAL STATUS
     * =========================================================
     */
    protected function isFinalStatus(
        string $status
    ): bool {

        return in_array(
            strtolower(
                trim($status)
            ),
            [
                'success',
                'successful',
                'completed',
                'complete',
                'successfully',
                'failed',
                'refunded',
            ],
            true
        );
    }


    /**
     * =========================================================
     * EXTRACT MOOGOLD ORDER ID
     * =========================================================
     */
    protected function extractOrderId(
        array $response
    ): ?string {

        $orderId =

            $response['order_id']

            ?? $response['orderId']

            ?? $response['order']['order_id']

            ?? $response['order']['id']

            ?? $response['data']['order_id']

            ?? $response['data']['orderId']

            ?? $response['data']['order']['order_id']

            ?? null;

        if (
            $orderId === null ||
            $orderId === ''
        ) {
            return null;
        }

        return (string) $orderId;
    }


    /**
     * =========================================================
     * EXTRACT MOOGOLD STATUS
     * =========================================================
     */
    protected function extractStatus(
        array $response
    ): string {

        $status =

            $response['order_status']

            ?? $response['status']

            ?? $response['order']['order_status']

            ?? $response['order']['status']

            ?? $response['data']['order_status']

            ?? $response['data']['status']

            ?? 'processing';

        return strtolower(
            trim(
                (string) $status
            )
        );
    }


    /**
     * =========================================================
     * BUILD ORDER PAYLOAD
     * =========================================================
     */
    public function buildOrderPayload(
        OrderDetail $orderDetail
    ): array {

        $orderDetail->loadMissing([
            'order',
            'item',
        ]);

        $order =
            $orderDetail->order;

        $item =
            $orderDetail->item;

        if (!$order) {
            throw new RuntimeException(
                'Order tidak ditemukan.'
            );
        }

        if (!$item) {
            throw new RuntimeException(
                'Item tidak ditemukan.'
            );
        }

        $playerData =
            $order->player_data;

        if (!is_array($playerData)) {
            $playerData =
                json_decode(
                    (string) $playerData,
                    true
                );
        }

        if (!is_array($playerData)) {
            throw new RuntimeException(
                'Player data tidak valid.'
            );
        }

        $userId =
            $playerData['uid']
            ?? $playerData['user_id']
            ?? $playerData['User ID']
            ?? null;

        $server =
            $playerData['server']
            ?? $playerData['server_id']
            ?? $playerData['Server ID']
            ?? null;

        if (empty($userId)) {
            throw new RuntimeException(
                'Player UID belum tersedia.'
            );
        }

        $data = [

            'category' =>
                (string)
                $item->moogold_category_id,

            'product-id' =>
                (string)
                $item->moogold_variation_id,

            'quantity' =>
                (string)
                $orderDetail->qty,

            'User ID' =>
                (string)
                $userId,
        ];

        if (
            $server !== null &&
            $server !== ''
        ) {
            $data['Server ID'] =
                (string)
                $server;
        }

        return [

            'path' =>
                'order/create_order',

            'partnerOrderId' =>
                $this->buildPartnerOrderId(
                    $order->id,
                    $orderDetail->id
                ),

            'data' =>
                $data,
        ];
    }
}

