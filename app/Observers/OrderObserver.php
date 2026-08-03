<?php

namespace App\Observers;


use App\Models\Order;
use App\Models\Notification;


class OrderObserver
{


public function updated(Order $order)
{


if(
    $order->wasChanged('status') &&
    in_array($order->status,[
        'Completed',
        'Cancelled'
    ])
){

    Notification::where(
        'order_id',
        $order->id
    )->delete();

}


}


}