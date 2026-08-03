<?php

namespace App\Observers;


use App\Models\Order;
use App\Models\Notification;


class OrderObserver
{


public function updated(Order $order)
{


    if(
        $order->isDirty('status')
        &&
        $order->status == 'Completed'
    ){


        Notification::where(
            'order_id',
            $order->id
        )
        ->update([

            'is_read'=>1,

            'read_at'=>now()

        ]);


    }


}


}