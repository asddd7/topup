<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Item;
use App\Models\User;
use App\Models\Notification;
use App\Models\Discount;
use App\Models\Order;

use Illuminate\Http\Request;


class OrderController extends BaseAdminController
{


public function index(Request $request)
{

    $statusList = [

        'Pending',

        'Waiting Payment',

        'Paid',

        'Processing',

        'Completed',

        'Cancelled'

    ];


    $status = $request->status;



    $orders = Order::with([

        'user',

        'game',

        'payment'

    ])

    ->when(
        $status,
        function($query) use($status){

            $query->where(
                'status',
                $status
            );

        }

    )

    ->latest()

    ->get();



    return view(
        'admin.order.index',
        compact(
            'orders',
            'status',
            'statusList'
        )
    );


}





public function show(Order $order)
{


$order->load([

'user',

'game',

'payment',

'discount',

'details.item',

'paymentLogs'

]);



return view(
'admin.order.show',
compact(
'order'
)
);


}






public function update(
    Request $request,
    Order $order
)
{


$request->validate([

    'status'=>'required'

]);


// ==========================
// Kurangi Kuota Discount
// ==========================

if($order->discount_id)
{

    $discount = Discount::find(
        $order->discount_id
    );


    if($discount)
    {

$discount->increment(
    'quota_used',
    1
);


$discount->refresh();


if(
    $discount->usage_limit
    &&
    $discount->quota_used >= $discount->usage_limit
){

    $discount->update([
        'is_active'=>0
    ]);

}

    }

}


$oldStatus = $order->status;

$old = $order->toArray();
$order->update([
    'status' => $request->status,
    'notes'  => $request->notes
]);
$this->activity->log(
    'Order',
    'Update Status',
    'Update status order '.$order->invoice_number.
    ' dari '.$old['status'].
    ' menjadi '.$order->status,
    $order,
    $old,
    $order->fresh()->toArray()
);


$order->paymentLogs()->create([

    'status'=>$request->status,

    'message'=>'Status diperbarui admin'

]);




/*
|--------------------------------------------------------------------------
| Hapus Notifikasi jika Order Completed
|--------------------------------------------------------------------------
*/

if($request->status == 'Completed')
{

    Notification::where(
        'order_id',
        $order->id
    )
    ->update([

        'is_read'=>1,

        'read_at'=>now()

    ]);

}




return back()->with(

'success',

'Order berhasil diperbarui'

);


}




public function confirm(Order $order)
{
    if ($order->status != 'Paid') {
        return back()->with(
            'error',
            'Order belum berstatus Paid.'
        );
    }

    // Load detail beserta item
    $order->load('details.item');

    // ==========================
    // Cek stok terlebih dahulu
    // ==========================
    foreach ($order->details as $detail) {

        if ($detail->item->stock < $detail->qty) {

            return back()->with(
                'error',
                'Stock '.$detail->item->item_name.' tidak mencukupi.'
            );

        }

    }

    // ==========================
    // Kurangi stok
    // ==========================
    foreach ($order->details as $detail) {

        $item = $detail->item;

        $item->decrement('stock', $detail->qty);

        $item->refresh();

        // Jika stok dibawah 10 buat notif
        if ($item->stock < 10) {

            foreach (User::where('role_id',1)->get() as $admin) {

                \App\Models\Notification::updateOrCreate(

                    [
                        'user_id'=>$admin->id,
                        'item_id'=>$item->id,
                        'title'=>'Stock Rendah'
                    ],

                    [
                        'message'=>$item->item_name.' tersisa '.$item->stock,
                        'is_read'=>0
                    ]

                );

            }

        }

    }

    // ==========================
    // Update Order
    // ==========================
    $old = $order->toArray();

    $order->update([
        'status'=>'Completed'
    ]);

    $this->activity->log(
        'Order',
        'Confirm Payment',
        'Konfirmasi pembayaran '.$order->invoice_number,
        $order,
        $old,
        $order->fresh()->toArray()
    );

    // ==========================
    // Payment Log
    // ==========================
    $order->paymentLogs()->create([
        'status'=>'Completed',
        'message'=>'Pembayaran berhasil dikonfirmasi Admin.'
    ]);

    // ==========================
    // Notifikasi order selesai
    // ==========================
    Notification::where(
        'order_id',
        $order->id
    )->update([
        'is_read'=>1,
        'read_at'=>now()
    ]);

    return back()->with(
        'success',
        'Order berhasil dikonfirmasi.'
    );
}

public function reject(Order $order)
{
$old = $order->toArray();
    $order->update([

        'status'=>'Waiting Payment',

        'payment_proof'=>null

    ]);
$this->activity->log(
    'Order',
    'Reject Payment',
    'Menolak pembayaran order '.$order->invoice_number,
    $order,
    $old,
    $order->fresh()->toArray()
);
    $order->paymentLogs()->create([

        'status'=>'Failed',

        'message'=>'Pembayaran ditolak. Silakan upload ulang.'

    ]);

    return back()->with(
        'success',
        'Pembayaran berhasil ditolak.'
    );

}
}