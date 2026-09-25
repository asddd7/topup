<?php

namespace App\Integrations\MooGold;

use App\Jobs\Providers\MooGold\CheckMooGoldOrderStatus;
use App\Models\MooGoldOrder;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MooGoldOrderService
{
    /**
     * Creation lease.
     */
    protected int $creationLeaseSeconds = 120;

    public function __construct(
        protected MooGoldService $mooGold
    ) {
    }

    /**
     * ============================================================
     * CREATE MOO GOLD ORDER
     * ============================================================
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
            'order.game',
            'item',
        ]);

        $order = $orderDetail->order;
        $item = $orderDetail->item;

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

        /*
        |--------------------------------------------------------------------------
        | VALIDATE MAPPING
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | RESOLVE MOO GOLD PLAYER DATA
        |--------------------------------------------------------------------------
        |
        | Mapping sekarang berdasarkan:
        |
        | game.player_fields[].name
        | game.player_fields[].moogold_field
        |
        | Contoh:
        |
        | name = region
        | moogold_field = Region
        |
        | akan menjadi:
        |
        | 'Region' => 'SEA'
        |
        */

        $moogoldPlayerData =
            $this->resolveMooGoldPlayerData(
                $order,
                $playerData
            );

        if (empty($moogoldPlayerData)) {
            throw new RuntimeException(
                'Player data MooGold tidak tersedia untuk order ini.'
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
                (string)
                    $item->moogold_category_id,

            'product-id' =>
                (string)
                    $item->moogold_variation_id,

            'quantity' =>
                (string)
                    $orderDetail->qty,

            ...$moogoldPlayerData,
        ];

        /*
        |--------------------------------------------------------------------------
        | LOG PLAYER MAPPING
        |--------------------------------------------------------------------------
        */

        Log::info(
            'MooGold player field mapping.',
            [
                'order_id' =>
                    $order->id,

                'order_detail_id' =>
                    $orderDetail->id,

                'game_id' =>
                    $order->game?->id,

                'player_data' =>
                    $playerData,

                'moogold_player_data' =>
                    $moogoldPlayerData,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | LOCAL MOO GOLD ORDER
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
        | SHORT DB LOCK / CLAIM
        |--------------------------------------------------------------------------
        */

        $claim =
            DB::transaction(
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
                    | FORCE DETERMINISTIC PARTNER ID
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedOrder->external_order_id
                        !==
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
                            'action' =>
                                'existing',

                            'order' =>
                                $lockedOrder->fresh(),
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CREATION LEASE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $this->isCreationLeaseActive(
                            $lockedOrder
                        )
                    ) {
                        Log::warning(
                            'MooGold order masih dalam creation lease. Create kedua dibatalkan.',
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
                            'action' =>
                                'in_progress',

                            'order' =>
                                $lockedOrder->fresh(),
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
                    | CLAIM
                    |--------------------------------------------------------------------------
                    */

                    $lockedOrder->moogold_status =
                        'creating';

                    $lockedOrder->error_message =
                        null;

                    $lockedOrder->last_attempt_at =
                        now();

                    $lockedOrder->attempts =
                        (
                            (int)
                            $lockedOrder->attempts
                        ) + 1;

                    $lockedOrder->save();

                    return [
                        'action' =>
                            'create',

                        'order' =>
                            $lockedOrder->fresh(),
                    ];
                }
            );

        /*
        |--------------------------------------------------------------------------
        | EXISTING
        |--------------------------------------------------------------------------
        */

        if (
            $claim['action'] ===
            'existing'
        ) {
            $result =
                $claim['order']->fresh();

            $this->scheduleStatusCheck(
                $result
            );

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | IN PROGRESS
        |--------------------------------------------------------------------------
        */

        if (
            $claim['action'] ===
            'in_progress'
        ) {
            throw new RuntimeException(
                'MooGold order sedang diproses oleh worker lain. Create order kedua dibatalkan.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE REQUEST
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder =
            $claim['order']->fresh();

        Log::info(
            'MooGold create_order akan dijalankan.',
            [
                'moo_gold_order_id' =>
                    $mooGoldOrder->id,

                'partner_order_id' =>
                    $partnerOrderId,

                'attempts' =>
                    $mooGoldOrder->attempts,

                'player_data' =>
                    $moogoldPlayerData,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
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

                    $moogoldPlayerData
                );

        } catch (Throwable $createError) {

            Log::warning(
                'Create MooGold mengalami error. Recovery Partner Order ID dijalankan.',
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
                    return $recovered->fresh();
                }

            } catch (Throwable $recoveryError) {

                $mooGoldOrder->update([
                    'moogold_status' =>
                        'unknown',

                    'error_message' =>
                        $recoveryError->getMessage(),
                ]);

                Log::error(
                    'Recovery setelah create error juga gagal.',
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
        | NO ORDER ID
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
                'MooGold tidak mengembalikan Order ID dan transaksi belum dapat direcovery berdasarkan Partner Order ID.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE SUCCESS
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
        | MAIN ORDER
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
        ]);

        $this->syncMainOrderStatus(
            $order->fresh()
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
     * ============================================================
     * RESOLVE MOO GOLD PLAYER DATA
     * ============================================================
     *
     * Mengubah:
     *
     * player_data:
     *
     * [
     *     'user_id' => '00088624',
     *     'region'  => 'SEA',
     * ]
     *
     * berdasarkan game.player_fields menjadi:
     *
     * [
     *     'User ID' => '00088624',
     *     'Region'  => 'SEA',
     * ]
     *
     * atau untuk game server:
     *
     * [
     *     'User ID'   => '1963315211',
     *     'Server ID' => '19248',
     * ]
     *
     * ============================================================
     */
    protected function resolveMooGoldPlayerData(
        Order $order,
        array $playerData
    ): array {

        $game =
            $order->game;

        if (!$game) {
            throw new RuntimeException(
                'Game pada order tidak ditemukan.'
            );
        }

        $playerFields =
            $game->player_fields ?? [];

        if (!is_array($playerFields)) {
            throw new RuntimeException(
                'Player fields game tidak valid.'
            );
        }

        $result = [];

        foreach ($playerFields as $field) {

            if (!is_array($field)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | LOCAL FIELD NAME
            |--------------------------------------------------------------------------
            */

            $localName =
                trim(
                    (string) (
                        $field['name']
                        ?? ''
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | MOO GOLD FIELD NAME
            |--------------------------------------------------------------------------
            */

            $mooGoldField =
                trim(
                    (string) (
                        $field['moogold_field']
                        ?? ''
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | FIELD TANPA MAPPING
            |--------------------------------------------------------------------------
            */

            if (
                $localName === '' ||
                $mooGoldField === ''
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | GET VALUE FROM ORDER PLAYER DATA
            |--------------------------------------------------------------------------
            */

            $value = null;

            if (
                array_key_exists(
                    $localName,
                    $playerData
                )
            ) {
                $value =
                    $playerData[$localName];
            }

            /*
            |--------------------------------------------------------------------------
            | READONLY FIELD
            |--------------------------------------------------------------------------
            |
            | Contoh:
            |
            | name          = region
            | input_mode    = readonly
            | moogold_field = Region
            |
            | Jika player_data tidak memiliki region,
            | gunakan games.moogold_server_id.
            |
            */

            $inputMode =
                strtolower(
                    trim(
                        (string) (
                            $field['input_mode']
                            ?? 'input'
                        )
                    )
                );

            if (
                $inputMode === 'readonly' &&
                (
                    $value === null ||
                    $value === ''
                )
            ) {
                $value =
                    $game->moogold_server_id;
            }

            /*
            |--------------------------------------------------------------------------
            | LEGACY ALIAS
            |--------------------------------------------------------------------------
            |
            | Untuk menjaga kompatibilitas dengan order lama,
            | jika local name tidak ditemukan, coba alias.
            |
            */

            if (
                $value === null ||
                $value === ''
            ) {

                $normalizedLocalName =
                    strtolower(
                        trim(
                            (string)
                                preg_replace(
                                    '/[^a-zA-Z0-9]+/',
                                    '_',
                                    $localName
                                )
                        )
                    );

                $aliases = match (
                    $normalizedLocalName
                ) {

                    'uid',
                    'userid',
                    'user_id',
                    'player_id',
                    'account_id'
                        => [
                            'uid',
                            'user_id',
                            'User ID',
                            'userid',
                            'player_id',
                            'account_id',
                        ],

                    'server',
                    'server_id'
                        => [
                            'server',
                            'server_id',
                            'Server',
                            'Server ID',
                        ],

                    'region',
                    'region_id'
                        => [
                            'region',
                            'region_id',
                            'Region',
                            'Region ID',
                        ],

                    default
                        => [
                            $localName,
                        ],
                };

                foreach ($aliases as $alias) {

                    if (
                        array_key_exists(
                            $alias,
                            $playerData
                        ) &&
                        $playerData[$alias] !== null &&
                        $playerData[$alias] !== ''
                    ) {
                        $value =
                            $playerData[$alias];

                        break;
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | REQUIRED
            |--------------------------------------------------------------------------
            */

            $required =
                (bool) (
                    $field['required']
                    ?? false
                );

            if (
                $required &&
                (
                    $value === null ||
                    $value === ''
                )
            ) {
                throw new RuntimeException(
                    'MooGold field "' .
                    $mooGoldField .
                    '" belum memiliki nilai.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SKIP OPTIONAL EMPTY FIELD
            |--------------------------------------------------------------------------
            */

            if (
                $value === null ||
                $value === ''
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | MOO GOLD FIELD
            |--------------------------------------------------------------------------
            |
            | INILAH BAGIAN UTAMA.
            |
            | Tidak peduli apakah:
            |
            | Server ID
            | Region
            | Zone
            | Role ID
            | atau field lainnya.
            |
            | Nama field mengikuti moogold_field.
            |
            */

            $result[$mooGoldField] =
                (string) $value;
        }

        return $result;
    }

    /**
     * ============================================================
     * BUILD PARTNER ORDER ID
     * ============================================================
     */
    protected function buildPartnerOrderId(
        int $orderId,
        int $orderDetailId
    ): string {
        return
            'MG-' .
            $orderId .
            '-' .
            $orderDetailId;
    }

    /**
     * ============================================================
     * RESOLVE USER ID
     * ============================================================
     *
     * Dipertahankan untuk kompatibilitas dengan code lama.
     * ============================================================
     */
    protected function resolveUserId(
        array $playerData
    ): ?string {

        $userId =
            $playerData['uid']
            ?? $playerData['user_id']
            ?? $playerData['User ID']
            ?? $playerData['userid']
            ?? $playerData['role_id']
            ?? $playerData['player_id']
            ?? $playerData['account_id']
            ?? null;

        if (
            $userId === null ||
            $userId === ''
        ) {
            return null;
        }

        return (string) $userId;
    }

    /**
     * ============================================================
     * RESOLVE SERVER
     * ============================================================
     *
     * Method lama dipertahankan untuk kompatibilitas.
     *
     * Catatan:
     *
     * Untuk create_order baru, method ini TIDAK lagi dipakai
     * untuk menentukan nama field API.
     *
     * Nama field sekarang berasal dari:
     *
     * game.player_fields[].moogold_field
     *
     * ============================================================
     */
    protected function resolveServer(
        Order $order,
        array $playerData
    ): ?string {

        $game =
            $order->game;

        $playerFields =
            $game?->player_fields
            ?? [];

        foreach ($playerFields as $field) {

            if (
                !$this->isServerPlayerField(
                    $field
                )
            ) {
                continue;
            }

            $fieldName =
                trim(
                    (string) (
                        $field['name']
                        ?? ''
                    )
                );

            $inputMode =
                strtolower(
                    trim(
                        (string) (
                            $field['input_mode']
                            ?? 'input'
                        )
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | READONLY
            |--------------------------------------------------------------------------
            */

            if (
                $inputMode ===
                'readonly'
            ) {
                return !empty(
                    $game?->moogold_server_id
                )
                    ? (string)
                        $game->moogold_server_id
                    : null;
            }

            /*
            |--------------------------------------------------------------------------
            | INPUT
            |--------------------------------------------------------------------------
            */

            if (
                $fieldName !== '' &&
                array_key_exists(
                    $fieldName,
                    $playerData
                ) &&
                $playerData[$fieldName] !== ''
            ) {
                return (string)
                    $playerData[$fieldName];
            }

            /*
            |--------------------------------------------------------------------------
            | LEGACY ALIASES
            |--------------------------------------------------------------------------
            */

            $server =
                $playerData['server']
                ?? $playerData['server_id']
                ?? $playerData['Server']
                ?? $playerData['Server ID']
                ?? null;

            if (
                $server !== null &&
                $server !== ''
            ) {
                return (string) $server;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LEGACY FALLBACK
        |--------------------------------------------------------------------------
        */

        $server =
            $playerData['server']
            ?? $playerData['server_id']
            ?? $playerData['Server']
            ?? $playerData['Server ID']
            ?? null;

        if (
            $server !== null &&
            $server !== ''
        ) {
            return (string) $server;
        }

        /*
        |--------------------------------------------------------------------------
        | GAME-LEVEL FALLBACK
        |--------------------------------------------------------------------------
        */

        return !empty(
            $game?->moogold_server_id
        )
            ? (string)
                $game->moogold_server_id
            : null;
    }

    /**
     * ============================================================
     * SERVER FIELD DETECTION
     * ============================================================
     */
    protected function isServerPlayerField(
        array $field
    ): bool {

        $type =
            strtolower(
                trim(
                    (string) (
                        $field['type']
                        ?? ''
                    )
                )
            );

        if ($type === 'server') {
            return true;
        }

        $moogoldField =
            strtolower(
                trim(
                    (string) (
                        $field['moogold_field']
                        ?? ''
                    )
                )
            );

        $moogoldField =
            trim(
                (string)
                    preg_replace(
                        '/[^a-z0-9]+/',
                        '_',
                        $moogoldField
                    ),
                '_'
            );

        return in_array(
            $moogoldField,
            [
                'server',
                'server_id',
                'region',
                'region_id',
            ],
            true
        );
    }

    /**
     * ============================================================
     * CREATION LEASE
     * ============================================================
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

        return $mooGoldOrder
            ->last_attempt_at
            ->gt(
                now()->subSeconds(
                    $this->creationLeaseSeconds
                )
            );
    }

    /**
     * ============================================================
     * RECOVER BY PARTNER ORDER ID
     * ============================================================
     */
    protected function recoverByPartnerOrderId(
        MooGoldOrder $mooGoldOrder
    ): ?MooGoldOrder {

        $partnerOrderId =
            trim(
                (string)
                    $mooGoldOrder
                        ->external_order_id
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

        $response =
            $this->mooGold
                ->orderByPartnerOrderId(
                    $partnerOrderId
                );

        $moogoldOrderId =
            $this->extractOrderId(
                $response
            );

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

        $status =
            $this->extractStatus(
                $response
            );

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
            ]);

            $this->syncMainOrderStatus(
                $order->fresh()
            );
        }

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

        /*
        |--------------------------------------------------------------------------
        | TIDAK ADA MOO GOLD ORDER ID
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $mooGoldOrder->moogold_order_id
            )
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | SUDAH FINAL
        |--------------------------------------------------------------------------
        */

        if (
            $this->isFinalStatus(
                (string)
                $mooGoldOrder->moogold_status
            )
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | FIRST STATUS CHECK
        |--------------------------------------------------------------------------
        |
        | Jangan tunggu 2 menit.
        |
        | Setelah create_order berhasil, beri MooGold sedikit
        | waktu untuk memproses order kemudian cek kembali.
        |--------------------------------------------------------------------------
        */

        $nextCheck =
            now()->addSeconds(10);


        CheckMooGoldOrderStatus::dispatch(
            $mooGoldOrder->id
        )->delay(
            $nextCheck
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

                'next_check' =>
                    $nextCheck,
            ]
        );
    }


    /**
     * ============================================================
     * CHECK STATUS
     * ============================================================
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

        $currentStatus =
            strtolower(
                trim(
                    (string)
                        $mooGoldOrder
                            ->moogold_status
                )
            );

        if (
            $this->isFinalStatus(
                $currentStatus
            )
        ) {
            return $mooGoldOrder->fresh();
        }

        $response =
            $this->mooGold->order(
                (int)
                    $mooGoldOrder
                        ->moogold_order_id
            );

        $status =
            $this->extractStatus(
                $response
            );

        $mooGoldOrder->response_payload =
            $response;

        $mooGoldOrder->moogold_status =
            $status;

        $order =
            $mooGoldOrder->order;

        switch ($status) {

            case 'pending':
            case 'processing':
            case 'sending':

                break;

            case 'success':
            case 'successful':
            case 'completed':
            case 'complete':
            case 'successfully':

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

                $mooGoldOrder->error_message =
                    'MooGold order refunded.';

                break;

            case 'failed':

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

        $mooGoldOrder->save();

        if ($order) {

            $order->moogold_order_id =
                $mooGoldOrder->moogold_order_id;

            $order->moogold_status =
                $status;

            $order->moogold_response =
                $response;

            $order->save();

            $this->syncMainOrderStatus(
                $order->fresh()
            );
        }

        return $mooGoldOrder->fresh();
    }

    /**
     * ============================================================
     * SYNC MAIN ORDER STATUS
     * ============================================================
     */
    protected function syncMainOrderStatus(
        Order $order
    ): void {

        $order->loadMissing([
            'details.mooGoldOrder',
        ]);

        $details =
            $order->details;

        if ($details->isEmpty()) {
            return;
        }

        $mooGoldOrders =
            $details
                ->map(
                    fn ($detail) =>
                        $detail->mooGoldOrder
                )
                ->filter();

        $allDetailsHaveMooGoldOrder =
            $mooGoldOrders->count() ===
            $details->count();

        $statuses =
            $mooGoldOrders
                ->map(
                    fn ($mooGoldOrder) =>
                        strtolower(
                            trim(
                                (string)
                                    $mooGoldOrder
                                        ->moogold_status
                            )
                        )
                );

        /*
        |--------------------------------------------------------------------------
        | FAILED / REFUNDED
        |--------------------------------------------------------------------------
        */

        if (
            $statuses->contains(
                fn ($status) =>
                    in_array(
                        $status,
                        [
                            'failed',
                            'refunded',
                        ],
                        true
                    )
            )
        ) {

            $order->status =
                'Cancelled';

            $order->save();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ACTIVE / UNKNOWN
        |--------------------------------------------------------------------------
        */

        if (
            $statuses->contains(
                fn ($status) =>
                    in_array(
                        $status,
                        [
                            'pending',
                            'processing',
                            'sending',
                            'creating',
                            'unknown',
                        ],
                        true
                    )
            )
        ) {

            $order->status =
                'Processing';

            $order->save();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | NOT ALL FULFILLMENT CREATED
        |--------------------------------------------------------------------------
        */

        if (
            !$allDetailsHaveMooGoldOrder
        ) {

            if (
                $mooGoldOrders->isNotEmpty()
            ) {

                $order->status =
                    'Processing';

                $order->save();
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ALL SUCCESS
        |--------------------------------------------------------------------------
        */

        $allSuccessful =
            $statuses->count() ===
            $details->count()
            &&
            $statuses->every(
                fn ($status) =>
                    in_array(
                        $status,
                        [
                            'success',
                            'successful',
                            'completed',
                            'complete',
                            'successfully',
                        ],
                        true
                    )
            );

        if ($allSuccessful) {

            $order->status =
                'Completed';

            $order->save();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK
        |--------------------------------------------------------------------------
        */

        if (
            $mooGoldOrders->isNotEmpty()
        ) {

            $order->status =
                'Processing';

            $order->save();
        }
    }

    /**
     * ============================================================
     * MAP ORDER STATUS
     * ============================================================
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
     * ============================================================
     * FINAL STATUS
     * ============================================================
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
     * ============================================================
     * EXTRACT ORDER ID
     * ============================================================
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
     * ============================================================
     * EXTRACT STATUS
     * ============================================================
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
     * ============================================================
     * BUILD ORDER PAYLOAD
     * ============================================================
     */
    public function buildOrderPayload(
        OrderDetail $orderDetail
    ): array {

        $orderDetail->loadMissing([
            'order.game',
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

        /*
        |--------------------------------------------------------------------------
        | PLAYER DATA
        |--------------------------------------------------------------------------
        */

        $playerData =
            $order->player_data;

        if (!is_array($playerData)) {

            $playerData =
                json_decode(
                    (string)
                        $playerData,
                    true
                );
        }

        if (!is_array($playerData)) {
            throw new RuntimeException(
                'Player data tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DYNAMIC MOO GOLD PLAYER DATA
        |--------------------------------------------------------------------------
        */

        $moogoldPlayerData =
            $this->resolveMooGoldPlayerData(
                $order,
                $playerData
            );

        if (empty($moogoldPlayerData)) {
            throw new RuntimeException(
                'Player data MooGold tidak tersedia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BUILD DATA
        |--------------------------------------------------------------------------
        */

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

            ...$moogoldPlayerData,
        ];

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