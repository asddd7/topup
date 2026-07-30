<?php

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Models\Discount;
use Carbon\Carbon;
use Illuminate\Http\Request;


class VoucherController extends Controller
{


public function check(Request $request)
{


$request->validate([

    'code'=>'required',

    'game_id'=>'required',

    'item_id'=>'required',

    'price'=>'required'

]);



$discount = Discount::where(
    'code',
    $request->code
)

->where('is_active',1)



->where(function($q) use($request){

    $q->whereNull('game_id')
      ->orWhere(
          'game_id',
          $request->game_id
      );

})



->where(function($q) use($request){

    $q->whereNull('item_id')
      ->orWhere(
          'item_id',
          $request->item_id
      );

})

->first();



if(!$discount)
{

return response()->json([

    'status'=>false,

    'message'=>'Voucher tidak ditemukan'

]);

}



$today=Carbon::today();



if(
$discount->start_date &&
$today->lt($discount->start_date)
)
{

return response()->json([

'status'=>false,

'message'=>'Voucher belum aktif'

]);

}




if(
$discount->end_date &&
$today->gt($discount->end_date)
)
{

return response()->json([

'status'=>false,

'message'=>'Voucher sudah expired'

]);

}




if($discount->discount_type=="percent")
{

$discountValue =
$request->price *
($discount->amount/100);

}
else
{

$discountValue =
$discount->amount;

}



$total =
$request->price-$discountValue;



return response()->json([


'status'=>true,


'discount_id'=>$discount->id,


'discount'=>$discountValue,


'total'=>$total,


'message'=>'Voucher berhasil digunakan'


]);


}



}