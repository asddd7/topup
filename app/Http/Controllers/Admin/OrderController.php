<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;

use App\Models\Order;

use Illuminate\Http\Request;


class OrderController extends Controller
{


public function index(Request $request)
{


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
'status'
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



$order->update([

'status'=>$request->status,

'notes'=>$request->notes

]);



$order->paymentLogs()->create([

'status'=>$request->status,

'message'=>'Status diperbarui admin'

]);



return back()->with(

'success',

'Order berhasil diperbarui'

);


}





public function confirm(Order $order)
{


$order->update([

'status'=>'Processing'

]);



$order->paymentLogs()->create([

'status'=>'Paid',

'message'=>'Pembayaran dikonfirmasi admin'

]);



return response()->json([

'success'=>true,

'message'=>'Pembayaran berhasil dikonfirmasi'

]);


}

public function reject(Order $order)
{


$order->update([

'status'=>'Waiting Payment',

'payment_proof'=>null

]);


$order->paymentLogs()->create([

'status'=>'Failed',

'message'=>'Pembayaran ditolak admin, menunggu upload ulang'

]);


return response()->json([

'success'=>true,

'message'=>'Pembayaran ditolak, user dapat upload ulang'

]);

}

public function paymentConfirmation()
{


$orders = Order::with([
    'user',
    'game',
    'payment'
])

->whereIn(
    'status',
    [
        'Paid',
        'Waiting Payment'
    ]
)

->latest()

->get();



return view(
    'admin.order.payment-confirmation',
    compact('orders')
);


}
}