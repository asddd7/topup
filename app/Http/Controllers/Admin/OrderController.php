<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;

use Illuminate\Http\Request;


class OrderController extends Controller
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



$oldStatus = $order->status;



$order->update([
    'status' => $request->status,
    'notes'  => $request->notes
]);



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
    if($order->status != 'Paid'){

        return back()->with(
            'error',
            'Order belum berstatus Paid.'
        );

    }

    $order->update([

        'status'=>'Completed'

    ]);

    $order->paymentLogs()->create([

        'status'=>'Completed',

        'message'=>'Pembayaran berhasil dikonfirmasi Admin.'

    ]);

    Notification::where(
        'order_id',
        $order->id
    )->update([

        'is_read'=>1,

        'read_at'=>now()

    ]);

    return back()->with(
        'success',
        'Order berhasil diselesaikan.'
    );
}

public function reject(Order $order)
{

    $order->update([

        'status'=>'Waiting Payment',

        'payment_proof'=>null

    ]);

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