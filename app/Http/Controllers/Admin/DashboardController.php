<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Game;
use App\Models\Item;


class DashboardController extends Controller
{

public function index()
{


$totalOrder =
Order::count();



$waitingPayment =
Order::where(
'status',
'Waiting Payment'
)
->count();



$totalGame =
Game::count();



$totalItem =
Item::count();



$income =
Order::where(
'status',
'Completed'
)
->sum('total_price');



$recentOrders =
Order::with('game')
->latest()
->limit(5)
->get();



return view(
'admin.dashboard',
compact(

'totalOrder',
'waitingPayment',
'totalGame',
'totalItem',
'income',
'recentOrders'

));

}


}