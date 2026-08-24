<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Game;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * POST /api/v1/orders
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_id' => [
                'required',
                'integer',
                'exists:games,id',
            ],

            'player_uid' => [
                'required',
                'string',
                'max:100',
            ],

            'server_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'nickname' => [
                'nullable',
                'string',
                'max:100',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],

            'items.*.qty' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Ambil game
        |--------------------------------------------------------------------------
        */

        $game = Game::query()
            ->where('id', $validated['game_id'])
            ->where('is_active', 1)
            ->first();

        if (!$game) {
            return response()->json([
                'success' => false,
                'message' => 'Game tidak tersedia.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        $order = DB::transaction(function () use ($validated, $game) {

            $subtotal = 0;

            $details = [];


            /*
            |--------------------------------------------------------------------------
            | Validasi & hitung item
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $requestItem) {

                $item = Item::query()
                    ->where('id', $requestItem['item_id'])
                    ->where('game_id', $game->id)
                    ->where('is_active', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$item) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Item tidak tersedia atau bukan milik game yang dipilih.',
                        ],
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Cek stock
                |--------------------------------------------------------------------------
                */

                if ($item->stock < $requestItem['qty']) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Stock {$item->item_name} tidak mencukupi.",
                        ],
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Hitung subtotal item
                |--------------------------------------------------------------------------
                */

                $qty = $requestItem['qty'];

                $price = (float) $item->price;

                $itemSubtotal = $price * $qty;

                $subtotal += $itemSubtotal;


                $details[] = [
                    'item' => $item,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $itemSubtotal,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Generate invoice
            |--------------------------------------------------------------------------
            */

            do {
                $invoice = 'INV-' .
                    now()->format('Ymd') .
                    '-' .
                    strtoupper(Str::random(6));

            } while (
                Order::where(
                    'invoice_number',
                    $invoice
                )->exists()
            );


            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            $userId = auth()->id();


            /*
            |--------------------------------------------------------------------------
            | Create Order
            |--------------------------------------------------------------------------
            */

            $order = Order::create([
                'invoice_number' => $invoice,

                'user_id' => $userId,

                'game_id' => $game->id,

                'player_uid' => $validated['player_uid'],

                'server_id' => $validated['server_id'] ?? null,

                'nickname' => $validated['nickname'] ?? null,

                'subtotal' => $subtotal,

                'discount' => 0,

                'total_price' => $subtotal,

                'status' => 'pending',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create Order Details
            |--------------------------------------------------------------------------
            */

            foreach ($details as $detail) {

                $order->details()->create([
                    'item_id' => $detail['item']->id,

                    'qty' => $detail['qty'],

                    'price' => $detail['price'],

                    'subtotal' => $detail['subtotal'],
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Load relationship
            |--------------------------------------------------------------------------
            */

            $order->load([
                'game',
                'details.item.category',
            ]);


            return $order;
        });


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => 'Order berhasil dibuat.',

            'data' => [
                'order' => new OrderResource($order),
            ],
        ], 201);
    }


    /**
     * GET /api/v1/orders
     */
    public function index(): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->with([
                'game',
                'details.item.category',
            ])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,

            'message' => 'Daftar order berhasil diambil.',

            'data' => OrderResource::collection($orders),
        ]);
    }


    /**
     * GET /api/v1/orders/{order}
     */
    public function show(Order $order): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Pastikan order milik user
        |--------------------------------------------------------------------------
        */

        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }


        $order->load([
            'game',
            'details.item.category',
            'payment',
        ]);


        return response()->json([
            'success' => true,

            'message' => 'Detail order berhasil diambil.',

            'data' => [
                'order' => new OrderResource($order),
            ],
        ]);
    }

/**
 * POST /api/v1/orders/{order}/payment
 *
 * Memilih metode pembayaran untuk order.
 */
/**
 * POST /api/v1/orders/{order}/payment
 *
 * Memilih metode pembayaran untuk order.
 */
public function payment(Request $request, Order $order): JsonResponse
{
    /*
    |--------------------------------------------------------------------------
    | Validasi payment
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([
        'payment_id' => [
            'required',
            'integer',
            'exists:payments,id',
        ],
    ]);


    /*
    |--------------------------------------------------------------------------
    | Pastikan order milik user
    |--------------------------------------------------------------------------
    */

    if ($order->user_id !== auth()->id()) {
        return response()->json([
            'success' => false,
            'message' => 'Order tidak ditemukan.',
        ], 404);
    }


    /*
    |--------------------------------------------------------------------------
    | Status yang masih boleh memilih pembayaran
    |--------------------------------------------------------------------------
    */

    if (!in_array($order->status, [
        'Pending',
        'Waiting Payment',
    ], true)) {
        return response()->json([
            'success' => false,
            'message' => 'Order tidak dapat memilih metode pembayaran pada status saat ini.',
        ], 422);
    }


    /*
    |--------------------------------------------------------------------------
    | Ambil payment aktif
    |--------------------------------------------------------------------------
    */

    $payment = Payment::query()
        ->where('id', $validated['payment_id'])
        ->where('is_active', 1)
        ->first();

    if (!$payment) {
        return response()->json([
            'success' => false,
            'message' => 'Metode pembayaran tidak tersedia.',
        ], 404);
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan payment
    |--------------------------------------------------------------------------
    */

    $order->update([
        'payment_id' => $payment->id,
        'status' => 'Waiting Payment',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Payment Log
    |--------------------------------------------------------------------------
    */

    $order->paymentLogs()->create([
        'status' => 'Pending',

        'message' =>
            'Metode pembayaran ' .
            $payment->payment_name .
            ' dipilih.',

        'logged_at' => now(),
    ]);


    /*
    |--------------------------------------------------------------------------
    | Load relationship
    |--------------------------------------------------------------------------
    */

    $order->load([
        'game',
        'payment',
        'details.item.category',
        'paymentLogs',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return response()->json([
        'success' => true,
        'message' => 'Metode pembayaran berhasil dipilih.',
        'data' => [
            'order' => new OrderResource($order),
        ],
    ]);
}

/**
 * POST /api/v1/orders/{order}/payment-proof
 *
 * Upload bukti pembayaran.
 */
public function paymentProof(
    Request $request,
    Order $order
): JsonResponse {

    /*
    |--------------------------------------------------------------------------
    | Pastikan order milik user
    |--------------------------------------------------------------------------
    */

    if ($order->user_id !== auth()->id()) {
        return response()->json([
            'success' => false,
            'message' => 'Order tidak ditemukan.',
        ], 404);
    }


    /*
    |--------------------------------------------------------------------------
    | Pastikan status order
    |--------------------------------------------------------------------------
    */

    if ($order->status !== 'Waiting Payment') {
        return response()->json([
            'success' => false,
            'message' => 'Order tidak dapat mengunggah bukti pembayaran pada status saat ini.',
        ], 422);
    }


    /*
    |--------------------------------------------------------------------------
    | Validasi file
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([
        'payment_proof' => [
            'required',
            'file',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:5120',
        ],
    ]);


    /*
    |--------------------------------------------------------------------------
    | Hapus bukti lama jika ada
    |--------------------------------------------------------------------------
    */

    if ($order->payment_proof) {

        $oldPath = $order->payment_proof;

        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan bukti pembayaran
    |--------------------------------------------------------------------------
    */

    $path = $request
        ->file('payment_proof')
        ->store('payment-proofs', 'public');


    /*
    |--------------------------------------------------------------------------
    | Update order
    |--------------------------------------------------------------------------
    */

    $order->update([
        'payment_proof' => $path,
    ]);


    /*
    |--------------------------------------------------------------------------
    | Payment Log
    |--------------------------------------------------------------------------
    |
    | payment_logs.status hanya menerima:
    | Pending, Paid, Failed, Expired, Refund
    |
    */

    $order->paymentLogs()->create([
        'status' => 'Pending',

        'message' =>
            'Bukti pembayaran berhasil diunggah.',

        'logged_at' => now(),
    ]);


    /*
    |--------------------------------------------------------------------------
    | Load relationship
    |--------------------------------------------------------------------------
    */

    $order->load([
        'game',
        'payment',
        'details.item.category',
        'paymentLogs',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return response()->json([
        'success' => true,

        'message' => 'Bukti pembayaran berhasil diunggah.',

        'data' => [
            'order' => new OrderResource($order),
        ],
    ]);
}
}