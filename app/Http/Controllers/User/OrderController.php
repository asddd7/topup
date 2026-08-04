<?php

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;

use App\Models\Order;
use App\Models\Game;
use App\Models\Item;
use App\Models\Notification;
use App\Models\User;
use App\Services\DiscountService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;



class OrderController extends Controller
{


public function index()
{

    $orders = Order::with([
        'game',
        'details.item'
    ])

    ->where(
        'user_id',
        Auth::id()
    )

    ->latest()

    ->get();



    return view(
        'order.index',
        compact('orders')
    );

}




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



    $selectedItem=null;



    if($request->item_id)
    {

        $selectedItem =
        Item::find(
            $request->item_id
        );

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





public function store(
    Request $request,
    DiscountService $discountService
)
{


$request->validate([

    'game_id'=>'required',
    'item_id'=>'required',
    'payment_id'=>'required',
    'subtotal'=>'required',
    'total_price'=>'required',

]);





$item = Item::with('game')
->findOrFail(
    $request->item_id
);




$subtotal = $item->price;


$discountAmount = (float) $request->discount;


$discount = null;


$totalPrice = (float) $request->total_price;


// safety check
if($totalPrice <= 0){

    $totalPrice = $subtotal - $discountAmount;

}


if($totalPrice < 0){

    $totalPrice = 0;

}




/*
|--------------------------------------------------------------------------
| Voucher
|--------------------------------------------------------------------------
*/


if($request->voucher)
{


    $discount =
    $discountService->findVoucher(
        $request->voucher
    );



    if(!$discount)
    {

        return back()
        ->with(
            'error',
            'Voucher tidak ditemukan'
        );

    }





    if(
        !$discountService->validateDiscount(

            $discount,

            $item->game_id,

            $item->id,

            $subtotal

        )
    )
    {


        return back()
        ->with(
            'error',
            'Voucher tidak berlaku'
        );

    }




    $result =
    $discountService->calculate(
        $discount,
        $subtotal
    );



    $discountAmount =
    $result['discount'];



    $totalPrice =
    $result['total'];

}





/*
|--------------------------------------------------------------------------
| Player Data
|--------------------------------------------------------------------------
*/


$playerData=[];



$playerUid=null;

$serverId=null;

$nickname=null;



switch($item->game->player_input_type)
{

case 'uid':

    $request->validate([
        'uid_player'=>'required'
    ]);

    $playerUid = $request->uid_player;

    $playerData = [
        'uid'=>$request->uid_player
    ];

break;


case 'uid_server':

    $request->validate([
        'uid_player'=>'required',
        'server_id'=>'required'
    ]);

    $playerUid = $request->uid_player;
    $serverId = $request->server_id;

    $playerData = [
        'uid'=>$request->uid_player,
        'server'=>$request->server_id
    ];

break;


case 'riot_id':

    $request->validate([
        'riot_id'=>'required',
        'riot_tag'=>'required'
    ]);

    $playerUid = $request->riot_id;
    $playerData = [
        'riot_id'=>$request->riot_id,
        'tag'=>$request->riot_tag
    ];

    
break;


case 'email':

    $request->validate([
        'player_email'=>'required|email'
    ]);

    $playerUid = $request->email;
    $playerData = [
        'email'=>$request->player_email
    ];

break;


/* TAMBAHKAN DI SINI */
case 'login':

    $request->validate([
        'login_id'=>'required'
    ]);

    $playerUid = $request->login;
    $playerData = [
        'login_id'=>$request->login_id
    ];

break;


case 'none':

    $playerData = [];

break;

}






/*
|--------------------------------------------------------------------------
| Create Order
|--------------------------------------------------------------------------
*/
$item = Item::findOrFail(
    $request->item_id
);


do {

    $invoice = 'INV-'
        .date('Ymd')
        .'-'
        .strtoupper(Str::random(6));


} while(
    Order::where(
        'invoice_number',
        $invoice
    )->exists()
);


$order = Order::create([

    'invoice_number'=>$invoice,

    'user_id'=>Auth::check()
        ? Auth::id()
        : null,

    'game_id'=>$request->game_id,

    'payment_id'=>$request->payment_id,

    'discount_id'=>$request->discount_id,

    'player_data'=>$playerData,

    'player_uid'=>$playerUid,

    'server_id'=>$serverId,

    'nickname'=>$nickname,

    'subtotal'=>$subtotal,

    'discount'=>$discountAmount,

    'total_price'=>$totalPrice,

    'status'=>'Waiting Payment',

    'guest_token'=>Auth::check()
    ? null
    : Str::uuid(),

]);



/*
|--------------------------------------------------------------------------
| Notification Admin - New Order
|--------------------------------------------------------------------------
*/


$admins = User::where(
    'role_id',
    1
)->get();



foreach($admins as $admin)
{

    Notification::create([

        'user_id'=>$admin->id,

        'order_id'=>$order->id,

        'title'=>'Order Baru',

        'message'=>
        'Order '.$order->invoice_number.
        ' menunggu proses'

    ]);

}
/*
|--------------------------------------------------------------------------
| Detail Order
|--------------------------------------------------------------------------
*/


$order->details()->create([

    'item_id'=>$item->id,

    'qty'=>1,

    'price'=>$totalPrice,

    'subtotal'=>$totalPrice

]);






if($order->guest_token){

    return redirect(
        route(
            'order.payment',
            $order->invoice_number
        )
        .'?token='.$order->guest_token
    );

}


return redirect()->route(
    'order.payment',
    $order->invoice_number
);

}






public function show(Request $request,$invoice)
{

    $order = Order::where(
        'invoice_number',
        $invoice
    )
    ->with([
        'game',
        'details.item'
    ])
    ->firstOrFail();



    /*
    |--------------------------------------------------------------------------
    | Guest Verification
    |--------------------------------------------------------------------------
    */

    if(
        !$order->user_id &&
        $order->guest_token != $request->token
    ){

        abort(403,'Token order tidak valid');

    }



    return view(
        'order.show',
        compact('order')
    );

}






public function checkVoucher(
Request $request,
DiscountService $discountService
)
{


$request->validate([


'code'=>'required',

'game_id'=>'required',

'item_id'=>'required',

'price'=>'required'


]);





$discount =
$discountService->findVoucher(
$request->code
);





if(!$discount)
{

return response()->json([

'status'=>false,

'message'=>'Voucher tidak ditemukan'

]);

}





if(
!$discountService->validateDiscount(

$discount,

$request->game_id,

$request->item_id,

$request->price

)

)
{

return response()->json([

'status'=>false,

'message'=>'Voucher tidak berlaku'

]);

}






$result =
$discountService->calculate(

$discount,

$request->price

);





return response()->json([


'status'=>true,


'discount_id'=>$discount->id,


'discount'=>
$result['discount'],



'total'=>
$result['total'],



'message'=>
'Voucher berhasil digunakan'


]);


}

public function payment(Request $request,$invoice)
{


$order = Order::where(
    'invoice_number',
    $invoice
)
->with([
    'payment',
    'details.item',
    'discount'
])
->firstOrFail();



/*
|--------------------------------------------------------------------------
| Guest Verification
|--------------------------------------------------------------------------
*/


if(
    !$order->user_id
    &&
    $order->guest_token !== $request->token
){

    abort(403,'Token order tidak valid');

}



return view(
    'order.payment',
    compact('order')
);


}

public function uploadProof(
Request $request,
$invoice
)
{


$order = Order::where(
    'invoice_number',
    $invoice
)
->firstOrFail();



if(
    !$order->user_id
    &&
    $order->guest_token !== $request->token
){

abort(403,'Token tidak valid');

}



$request->validate([

    'payment_proof'=>'required|image|max:2048'

]);



$file=$request
->file('payment_proof')
->store(
    'payment-proof',
    'public'
);



$order->update([

    'payment_proof'=>$file,

    'status'=>'Paid'

]);



$order->paymentLogs()->create([

    'status'=>'Paid',

    'message'=>'User upload bukti pembayaran'

]);

$admins = User::where(
    'role_id',
    1
)->get();



foreach($admins as $admin)
{

    Notification::create([

        'user_id'=>$admin->id,

        'order_id'=>$order->id,

        'title'=>'Pembayaran Diterima',

        'message'=>
        'Order '.$order->invoice_number.
        ' sudah upload bukti pembayaran'

    ]);

}

return redirect(

route(
'order.payment',
$order->invoice_number
)
.'?token='.$order->guest_token

)
->with(
'success',
'Bukti pembayaran berhasil dikirim'
);


}

public function checkOrder(Request $request,$invoice)
{


$order = Order::where(
'invoice_number',
$invoice
)
->firstOrFail();



if(
$order->guest_token != $request->token
){

abort(403);

}



$order->load([

'game',
'details.item',
'payment'

]);



return view(
'order.show',
compact('order')
);


}
}