<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Order;
use App\Models\Game;
use App\Models\Item;
use App\Models\User;
use App\Models\Notification;
use App\Models\Discount;

use App\Services\PromotionService;

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
        | 1. Basic validation
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'game_id' =>
                'required|exists:games,id',

            'item_id' =>
                'required|exists:items,id',

            'payment_id' =>
                'required|exists:payments,id',

            'voucher' =>
                'nullable|string|max:255',

        ]);


        /*
        |--------------------------------------------------------------------------
        | 2. Get item
        |--------------------------------------------------------------------------
        */

        $item = Item::with('game')

            ->where(
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


        /*
        |--------------------------------------------------------------------------
        | 3. Validate player data
        |--------------------------------------------------------------------------
        */

        $playerData = [];

        foreach (
            $item->game->player_fields ?? []
            as $field
        ) {

            $rules = [];

            if (
                !empty($field['required'])
            ) {

                $rules[] = 'required';
            }

            if (
                ($field['type'] ?? '') === 'number'
            ) {

                $rules[] = 'numeric';
            }

            if (
                ($field['type'] ?? '') === 'email'
            ) {

                $rules[] = 'email';
            }

            if (
                count($rules) > 0
            ) {

                $request->validate([

                    $field['name'] =>
                        implode(
                            '|',
                            $rules
                        ),

                ]);
            }

            $playerData[
                $field['name']
            ] = $request->input(
                $field['name']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Transaction
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
                | Harga resmi dari database
                |--------------------------------------------------------------------------
                */

                $subtotal = (float) $item->price;


                /*
                |--------------------------------------------------------------------------
                | Calculate promotion
                |--------------------------------------------------------------------------
                */

                $promo = $promotion->calculate(

                    subtotal:
                        $subtotal,

                    gameId:
                        (int) $item->game_id,

                    itemId:
                        (int) $item->id,

                    paymentId:
                        (int) $request->payment_id,

                    voucherCode:
                        $request->filled('voucher')
                            ? $request->voucher
                            : null,

                    user:
                        auth()->user(),

                    lockForUpdate:
                        true
                );


                /*
                |--------------------------------------------------------------------------
                | Final price
                |--------------------------------------------------------------------------
                */

                $discountTotal = round(
                    (float) $promo['discount_total'],
                    2
                );

                $totalPrice = round(
                    (float) $promo['total'],
                    2
                );


                /*
                |--------------------------------------------------------------------------
                | Generate invoice
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
                | Legacy discount_id
                |--------------------------------------------------------------------------
                */

                $firstDiscountId =
                    $promo['discounts'][0]['id']
                    ?? null;


                /*
                |--------------------------------------------------------------------------
                | Guest token
                |--------------------------------------------------------------------------
                */

                $guestToken =
                    Auth::check()
                        ? null
                        : (string) Str::uuid();


                /*
                |--------------------------------------------------------------------------
                | Create order
                |--------------------------------------------------------------------------
                */

                $order = Order::create([

                    'invoice_number' =>
                        $invoice,

                    'user_id' =>
                        Auth::id(),

                    'game_id' =>
                        $item->game_id,

                    'payment_id' =>
                        $request->payment_id,

                    'discount_id' =>
                        $firstDiscountId,

                    'player_data' =>
                        $playerData,

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
                | Save ALL applied promotions
                |--------------------------------------------------------------------------
                */

                foreach (
                    $promo['discounts']
                    as $applied
                ) {

                    $discountId =
                        (int) $applied['id'];


                    /*
                    |--------------------------------------------------------------------------
                    | Lock discount
                    |--------------------------------------------------------------------------
                    */

                    $discount = Discount::where(
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


                    /*
                    |--------------------------------------------------------------------------
                    | Check global quota
                    |--------------------------------------------------------------------------
                    |
                    | usage_limit:
                    | NULL = unlimited
                    |
                    */

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
                    | Save order discount
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
                    | Save discount usage
                    |--------------------------------------------------------------------------
                    |
                    | Ini yang mengisi tabel:
                    |
                    | discount_usages
                    |
                    */

                    $discount->usages()->create([

                        'order_id' =>
                            $order->id,

                        'user_id' =>
                            Auth::id(),

                        'discount_amount' =>
                            (float)
                            $applied['discount'],

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | PENTING
                    |--------------------------------------------------------------------------
                    |
                    | quota_used TIDAK di-increment di sini.
                    |
                    | Controller hanya mencatat:
                    |
                    | 1. order_discounts
                    | 2. discount_usages
                    |
                    | Pengelolaan quota global dilakukan oleh
                    | satu mekanisme saja agar tidak double count.
                    |
                    */
                }


                /*
                |--------------------------------------------------------------------------
                | Order detail
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
                | Notification admin
                |--------------------------------------------------------------------------
                */

                $admins = User::where(
                    'role_id',
                    1
                )->get();


                foreach (
                    $admins
                    as $admin
                ) {

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
        | Redirect
        |--------------------------------------------------------------------------
        */

        if (
            $order->guest_token
        ) {

            return redirect(

                route(
                    'order.payment',
                    $order->invoice_number
                )
                . '?token=' .
                $order->guest_token

            );
        }


        return redirect()->route(

            'order.payment',

            $order->invoice_number

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


        /*
        |--------------------------------------------------------------------------
        | Guest verification
        |--------------------------------------------------------------------------
        */

        if (
            !$order->user_id
            &&
            $order->guest_token !=
            $request->token
        ) {

            abort(
                403,
                'Token order tidak valid'
            );
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


        /*
        |--------------------------------------------------------------------------
        | Guest verification
        |--------------------------------------------------------------------------
        */

        if (
            !$order->user_id
            &&
            $order->guest_token !==
            $request->token
        ) {

            abort(
                403,
                'Token order tidak valid'
            );
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
        )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Guest verification
        |--------------------------------------------------------------------------
        */

        if (
            !$order->user_id
            &&
            $order->guest_token !==
            $request->token
        ) {

            abort(
                403,
                'Token tidak valid'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate payment proof
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'payment_proof' =>
                'required|image|max:2048',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Store file
        |--------------------------------------------------------------------------
        */

        $file = $request
            ->file('payment_proof')
            ->store(
                'payment-proof',
                'public'
            );


        /*
        |--------------------------------------------------------------------------
        | Update order
        |--------------------------------------------------------------------------
        */

        $order->update([

            'payment_proof' =>
                $file,

            'status' =>
                'Paid',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Payment log
        |--------------------------------------------------------------------------
        */

        $order
            ->paymentLogs()
            ->create([

                'status' =>
                    'Paid',

                'message' =>
                    'User upload bukti pembayaran',

            ]);


        /*
        |--------------------------------------------------------------------------
        | Notification admin
        |--------------------------------------------------------------------------
        */

        $admins = User::where(
            'role_id',
            1
        )->get();


        foreach (
            $admins
            as $admin
        ) {

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
                    ' sudah upload bukti pembayaran',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect(

            route(
                'order.payment',
                $order->invoice_number
            )
            . '?token=' .
            $order->guest_token

        )
            ->with(
                'success',
                'Bukti pembayaran berhasil dikirim'
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

            'payment_id' =>
                'nullable|exists:payments,id',

            'voucher_code' =>
                'nullable|string|max:255',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Get item
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Price from database
        |--------------------------------------------------------------------------
        */

        $subtotal =
            (float) $item->price;


        /*
        |--------------------------------------------------------------------------
        | Calculate promotion
        |--------------------------------------------------------------------------
        */

        $result =
            $promotion->calculate(

                subtotal:
                    $subtotal,

                gameId:
                    (int) $item->game_id,

                itemId:
                    (int) $item->id,

                paymentId:
                    $request->payment_id
                        ? (int)
                        $request->payment_id
                        : null,

                voucherCode:
                    $request->filled(
                        'voucher_code'
                    )
                        ? $request->voucher_code
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
        )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Guest verification
        |--------------------------------------------------------------------------
        */

        if (
            $order->guest_token !=
            $request->token
        ) {

            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | Load relationships
        |--------------------------------------------------------------------------
        */

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
}