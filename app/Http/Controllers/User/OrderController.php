<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Game;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * ============================================================
     * ORDER LIST
     * ============================================================
     */
    public function index()
    {
        $orders = Order::with([
            'game',
            'details.item',
        ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view(
            'order.index',
            compact('orders')
        );
    }

    /**
     * ============================================================
     * CREATE ORDER
     * ============================================================
     */
    public function create(Request $request)
    {
        $game = Game::findOrFail(
            $request->game_id
        );

        $items = Item::where(
            'game_id',
            $game->id
        )
            ->where(
                'is_active',
                1
            )
            ->get();

        $selectedItem = null;

        if ($request->item_id) {
            $selectedItem = Item::where(
                'id',
                $request->item_id
            )
                ->where(
                    'game_id',
                    $game->id
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();
        }

        return view(
            'order.create',
            compact(
                'game',
                'items',
                'selectedItem'
            )
        );
    }

    /**
     * ============================================================
     * STORE ORDER
     * ============================================================
     */
    public function store(
        Request $request,
        PromotionService $promotion
    ) {
        /*
        |--------------------------------------------------------------------------
        | 1. BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'game_id' => [
                'required',
                'integer',
                'exists:games,id',
            ],

            'item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],

            'midtrans_payment_type' => [
                'required',
                'string',
                'max:100',
            ],

            'voucher' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. GET ITEM + GAME
        |--------------------------------------------------------------------------
        */

        $item = Item::with('game')
            ->where('id', $request->item_id)
            ->where('game_id', $request->game_id)
            ->where('is_active', 1)
            ->firstOrFail();

        $game = $item->game;

        $gamePlayerFields =
            $game->player_fields ?? [];

        /*
        |--------------------------------------------------------------------------
        | 3. CHECK READONLY SERVER MAPPING
        |--------------------------------------------------------------------------
        |
        | games.moogold_server_id HANYA wajib jika:
        |
        | - item menggunakan MooGold
        | - game memiliki server/region field
        | - field tersebut readonly
        |
        | Untuk server input manual seperti ML:
        | games.moogold_server_id tidak wajib.
        |
        */

        $hasReadonlyServerField =
            $this->hasReadonlyServerField(
                $gamePlayerFields
            );

        if (
            !empty($item->moogold_product_id) &&
            $hasReadonlyServerField &&
            empty($game->moogold_server_id)
        ) {
            throw new \RuntimeException(
                'Server MooGold belum dikonfigurasi untuk game ini.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. BUILD PLAYER VALIDATION RULES
        |--------------------------------------------------------------------------
        */

        $playerRules = [];

        foreach ($gamePlayerFields as $field) {

            $fieldName = trim(
                (string) ($field['name'] ?? '')
            );

            if ($fieldName === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | SERVER READONLY
            |--------------------------------------------------------------------------
            |
            | Nilai berasal dari games.moogold_server_id.
            | Browser tidak menjadi sumber kebenaran.
            |
            */

            if (
                $this->isServerPlayerField($field) &&
                $this->isReadonlyPlayerField($field)
            ) {
                $playerRules[$fieldName] = [
                    'nullable',
                    'max:255',
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | NORMAL / INPUT FIELD
            |--------------------------------------------------------------------------
            */

            $type = strtolower(
                trim(
                    (string) (
                        $field['type']
                        ?? 'text'
                    )
                )
            );

            $rules = [];

            if (!empty($field['required'])) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }

            if ($type === 'number') {

                $rules[] =
                    'numeric';

            } elseif ($type === 'email') {

                $rules[] =
                    'email';

                $rules[] =
                    'max:255';

            } else {

                $rules[] =
                    'max:255';
            }

            $playerRules[$fieldName] =
                $rules;
        }

        /*
        |--------------------------------------------------------------------------
        | 5. VALIDATE PLAYER INPUT
        |--------------------------------------------------------------------------
        */

        $validatedPlayerData = [];

        if (!empty($playerRules)) {

            $validatedPlayerData =
                $request->validate(
                    $playerRules
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 6. BUILD PLAYER DATA
        |--------------------------------------------------------------------------
        */

        $playerData = [];

        foreach ($gamePlayerFields as $field) {

            $fieldName = trim(
                (string) ($field['name'] ?? '')
            );

            if ($fieldName === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | SERVER READONLY
            |--------------------------------------------------------------------------
            |
            | Selalu gunakan mapping server dari database game.
            |
            */

            if (
                $this->isServerPlayerField($field) &&
                $this->isReadonlyPlayerField($field)
            ) {
                if (
                    !empty(
                        $game->moogold_server_id
                    )
                ) {
                    $playerData[$fieldName] =
                        (string)
                        $game->moogold_server_id;
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | NORMAL INPUT
            |--------------------------------------------------------------------------
            */

            $value =
                $validatedPlayerData[$fieldName]
                ?? $request->input($fieldName);

            if (
                $value !== null &&
                $value !== ''
            ) {
                $playerData[$fieldName] =
                    (string) $value;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 7. SAFETY CHECK
        |--------------------------------------------------------------------------
        */

        if (
            empty($playerData) &&
            !empty($gamePlayerFields)
        ) {
            throw new \RuntimeException(
                'Data player tidak berhasil diproses.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 8. TRANSACTION
        |--------------------------------------------------------------------------
        */

        $order = DB::transaction(
            function () use (
                $request,
                $item,
                $playerData,
                $promotion
            ) {
                /*
                |--------------------------------------------------------------------------
                | OFFICIAL PRICE
                |--------------------------------------------------------------------------
                */

                $subtotal =
                    (float) $item->price;

                /*
                |--------------------------------------------------------------------------
                | PROMOTION
                |--------------------------------------------------------------------------
                */

                $promo =
                    $promotion->calculate(
                        subtotal:
                            $subtotal,

                        gameId:
                            (int)
                            $item->game_id,

                        itemId:
                            (int)
                            $item->id,

                        paymentType:
                            $request
                                ->midtrans_payment_type,

                        voucherCode:
                            $request->filled(
                                'voucher'
                            )
                                ? $request->voucher
                                : null,

                        user:
                            auth()->user(),

                        lockForUpdate:
                            true
                    );

                /*
                |--------------------------------------------------------------------------
                | FINAL PRICE
                |--------------------------------------------------------------------------
                */

                $discountTotal =
                    round(
                        (float)
                        $promo['discount_total'],
                        2
                    );

                $totalPrice =
                    round(
                        (float)
                        $promo['total'],
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | INVOICE
                |--------------------------------------------------------------------------
                */

                do {

                    $invoice =
                        'INV-' .
                        now()->format('Ymd') .
                        '-' .
                        strtoupper(
                            Str::random(6)
                        );

                } while (
                    Order::where(
                        'invoice_number',
                        $invoice
                    )->exists()
                );

                /*
                |--------------------------------------------------------------------------
                | LEGACY DISCOUNT ID
                |--------------------------------------------------------------------------
                */

                $firstDiscountId =
                    $promo['discounts'][0]['id']
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | GUEST TOKEN
                |--------------------------------------------------------------------------
                */

                $guestToken =
                    Auth::check()
                        ? null
                        : (string)
                            Str::uuid();

                /*
                |--------------------------------------------------------------------------
                | CREATE ORDER
                |--------------------------------------------------------------------------
                */

                $order =
                    Order::create([
                        'invoice_number' =>
                            $invoice,

                        'user_id' =>
                            Auth::id(),

                        'game_id' =>
                            $item->game_id,

                        'payment_id' =>
                            null,

                        'midtrans_payment_type' =>
                            strtolower(
                                trim(
                                    $request
                                        ->midtrans_payment_type
                                )
                            ),

                        'discount_id' =>
                            $firstDiscountId,

                        'player_data' =>
                            $playerData,

                        /*
                        |--------------------------------------------------------------------------
                        | LEGACY
                        |--------------------------------------------------------------------------
                        */

                        'player_uid' =>
                            null,

                        'server_id' =>
                            null,

                        'nickname' =>
                            null,

                        'subtotal' =>
                            $subtotal,

                        'discount' =>
                            $discountTotal,

                        'total_price' =>
                            $totalPrice,

                        'status' =>
                            'Waiting Payment',

                        'guest_token' =>
                            $guestToken,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | SAVE PROMOTIONS
                |--------------------------------------------------------------------------
                */

                foreach (
                    $promo['discounts']
                    as $applied
                ) {

                    $discountId =
                        (int)
                        $applied['id'];

                    $discount =
                        Discount::where(
                            'id',
                            $discountId
                        )
                            ->lockForUpdate()
                            ->first();

                    if (!$discount) {
                        throw new \RuntimeException(
                            'Promo tidak ditemukan.'
                        );
                    }

                    if (
                        $discount->usage_limit !== null
                        &&
                        $discount->quota_used >=
                        $discount->usage_limit
                    ) {
                        throw new \RuntimeException(
                            'Promo "' .
                            $discount->discount_name .
                            '" sudah habis.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ORDER DISCOUNT
                    |--------------------------------------------------------------------------
                    */

                    $order
                        ->orderDiscounts()
                        ->create([
                            'discount_id' =>
                                $discountId,

                            'discount_amount' =>
                                (float)
                                $applied['discount'],
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | USAGE
                    |--------------------------------------------------------------------------
                    */

                    $discount
                        ->usages()
                        ->create([
                            'order_id' =>
                                $order->id,

                            'user_id' =>
                                Auth::id(),

                            'discount_amount' =>
                                (float)
                                $applied['discount'],
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | ORDER DETAIL
                |--------------------------------------------------------------------------
                */

                $order
                    ->details()
                    ->create([
                        'item_id' =>
                            $item->id,

                        'qty' =>
                            1,

                        'price' =>
                            $totalPrice,

                        'subtotal' =>
                            $totalPrice,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | ADMIN NOTIFICATION
                |--------------------------------------------------------------------------
                */

                $admins =
                    User::where(
                        'role_id',
                        1
                    )->get();

                foreach ($admins as $admin) {

                    Notification::create([
                        'user_id' =>
                            $admin->id,

                        'order_id' =>
                            $order->id,

                        'title' =>
                            'Order Baru',

                        'message' =>
                            'Order ' .
                            $order->invoice_number .
                            ' menunggu proses',
                    ]);
                }

                return $order;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 9. MIDTRANS
        |--------------------------------------------------------------------------
        */

        $routeParameters = [
            'order' =>
                $order->id,

            'payment' =>
                $order->midtrans_payment_type,
        ];

        if (!$order->user_id) {

            $routeParameters['token'] =
                $order->guest_token;
        }

        return redirect()->route(
            'midtrans.payment',
            $routeParameters
        );
    }

    /**
     * ============================================================
     * SHOW ORDER
     * ============================================================
     */
    public function show(
        Request $request,
        $invoice
    ) {
        $order = Order::where(
            'invoice_number',
            $invoice
        )
            ->with([
                'game',
                'details.item',
            ])
            ->firstOrFail();

        if ($order->user_id) {

            if (!Auth::check()) {
                abort(
                    403,
                    'Silakan login untuk melihat order ini.'
                );
            }

            if (
                (int)
                    $order->user_id !==
                (int)
                    Auth::id()
            ) {
                abort(
                    403,
                    'Anda tidak memiliki akses ke order ini.'
                );
            }

        } else {

            if (
                !$request->filled('token')
                ||
                !hash_equals(
                    (string)
                        $order->guest_token,
                    (string)
                        $request->token
                )
            ) {
                abort(
                    403,
                    'Token order tidak valid.'
                );
            }
        }

        return view(
            'order.show',
            compact('order')
        );
    }

    /**
     * ============================================================
     * PAYMENT PAGE
     * ============================================================
     */
    public function payment(
        Request $request,
        $invoice
    ) {
        $order = Order::where(
            'invoice_number',
            $invoice
        )
            ->with([
                'payment',
                'details.item',
                'discount',
            ])
            ->firstOrFail();

        if ($order->user_id) {

            if (!Auth::check()) {
                abort(
                    403,
                    'Silakan login untuk melihat pembayaran.'
                );
            }

            if (
                (int)
                    $order->user_id !==
                (int)
                    Auth::id()
            ) {
                abort(
                    403,
                    'Anda tidak memiliki akses ke pembayaran ini.'
                );
            }

        } else {

            if (
                !$request->filled('token')
                ||
                !hash_equals(
                    (string)
                        $order->guest_token,
                    (string)
                        $request->token
                )
            ) {
                abort(
                    403,
                    'Token order tidak valid.'
                );
            }
        }

        return view(
            'order.payment',
            compact('order')
        );
    }

    /**
     * ============================================================
     * UPLOAD PAYMENT PROOF
     * ============================================================
     */
    public function uploadProof(
        Request $request,
        $invoice
    ) {
        $order = Order::where(
            'invoice_number',
            $invoice
        )->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        if ($order->user_id) {

            if (!Auth::check()) {
                abort(
                    403,
                    'Silakan login untuk mengakses order ini.'
                );
            }

            if (
                (int)
                    $order->user_id !==
                (int)
                    Auth::id()
            ) {
                abort(
                    403,
                    'Anda tidak memiliki akses ke order ini.'
                );
            }

        } else {

            if (
                !$request->filled('token')
                ||
                !hash_equals(
                    (string)
                        $order->guest_token,
                    (string)
                        $request->token
                )
            ) {
                abort(
                    403,
                    'Token order tidak valid.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        if (
            $order->status !==
            'Waiting Payment'
        ) {
            return back()->with(
                'error',
                'Order tidak dapat menerima bukti pembayaran pada status ' .
                $order->status .
                '.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILE VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'payment_proof' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if (!$request->hasFile('payment_proof')) {
            return back()->with(
                'error',
                'File bukti pembayaran tidak ditemukan.'
            );
        }

        if (
            !$request
                ->file('payment_proof')
                ->isValid()
        ) {
            return back()->with(
                'error',
                'File gagal diupload. Silakan coba gambar lain.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STORE FILE
        |--------------------------------------------------------------------------
        */

        try {

            $file =
                $request
                    ->file('payment_proof')
                    ->store(
                        'payment-proof',
                        'public'
                    );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Gagal menyimpan bukti pembayaran: ' .
                $e->getMessage()
            );
        }

        if (!$file) {
            return back()->with(
                'error',
                'File bukti pembayaran gagal disimpan.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE ORDER
        |--------------------------------------------------------------------------
        */

        $order->update([
            'payment_proof' =>
                $file,

            'status' =>
                'Waiting Payment',
        ]);

        /*
        |--------------------------------------------------------------------------
        | PAYMENT LOG
        |--------------------------------------------------------------------------
        */

        $order
            ->paymentLogs()
            ->create([
                'status' =>
                    'Pending',

                'message' =>
                    'Bukti pembayaran diunggah. Menunggu verifikasi admin.',

                'logged_at' =>
                    now(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | ADMIN NOTIFICATION
        |--------------------------------------------------------------------------
        */

        $admins =
            User::where(
                'role_id',
                1
            )->get();

        foreach ($admins as $admin) {

            Notification::create([
                'user_id' =>
                    $admin->id,

                'order_id' =>
                    $order->id,

                'title' =>
                    'Pembayaran Diterima',

                'message' =>
                    'Order ' .
                    $order->invoice_number .
                    ' sudah upload bukti pembayaran.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        if ($order->user_id) {

            return redirect()
                ->route(
                    'order.payment',
                    $order->invoice_number
                )
                ->with(
                    'success',
                    'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.'
                );
        }

        return redirect(
            route(
                'order.payment',
                $order->invoice_number
            ) .
            '?token=' .
            urlencode(
                $order->guest_token
            )
        )->with(
            'success',
            'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.'
        );
    }

    /**
     * ============================================================
     * CALCULATE PROMOTION
     * ============================================================
     */
    public function calculatePromotion(
        Request $request,
        PromotionService $promotion
    ) {
        $request->validate([
            'game_id' =>
                'required|exists:games,id',

            'item_id' =>
                'required|exists:items,id',

            'midtrans_payment_type' =>
                'nullable|string|max:100',

            'voucher_code' =>
                'nullable|string|max:255',
        ]);

        $item = Item::where(
            'id',
            $request->item_id
        )
            ->where(
                'game_id',
                $request->game_id
            )
            ->where(
                'is_active',
                1
            )
            ->firstOrFail();

        $subtotal =
            (float) $item->price;

        $result =
            $promotion->calculate(
                subtotal:
                    $subtotal,

                gameId:
                    (int)
                    $item->game_id,

                itemId:
                    (int)
                    $item->id,

                paymentType:
                    $request
                        ->midtrans_payment_type
                        ?: null,

                voucherCode:
                    $request->filled(
                        'voucher_code'
                    )
                        ? $request
                            ->voucher_code
                        : null,

                user:
                    auth()->user(),
            );

        return response()->json(
            $result
        );
    }

    /**
     * ============================================================
     * CHECK ORDER
     * ============================================================
     */
    public function checkOrder(
        Request $request,
        $invoice
    ) {
        $order = Order::where(
            'invoice_number',
            $invoice
        )->firstOrFail();

        if (
            !hash_equals(
                (string)
                    $order->guest_token,
                (string)
                    $request->token
            )
        ) {
            abort(403);
        }

        $order->load([
            'game',
            'user',
            'details.item',
            'payment',
        ]);

        return view(
            'order.show',
            compact('order')
        );
    }

    /**
     * ============================================================
     * CHECK SERVER PLAYER FIELD
     * ============================================================
     */
    private function isServerPlayerField(
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
     * CHECK READONLY MODE
     * ============================================================
     */
    private function isReadonlyPlayerField(
        array $field
    ): bool {
        return (
            strtolower(
                trim(
                    (string) (
                        $field['input_mode']
                        ?? 'input'
                    )
                )
            ) === 'readonly'
        );
    }

    /**
     * ============================================================
     * CHECK WHETHER GAME HAS READONLY SERVER
     * ============================================================
     */
    private function hasReadonlyServerField(
        array $fields
    ): bool {
        foreach ($fields as $field) {

            if (
                $this->isServerPlayerField(
                    $field
                ) &&
                $this->isReadonlyPlayerField(
                    $field
                )
            ) {
                return true;
            }
        }

        return false;
    }
}