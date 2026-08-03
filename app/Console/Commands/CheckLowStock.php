<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Models\Item;
use App\Models\Notification;
use App\Models\User;


class CheckLowStock extends Command
{


protected $signature = 'stock:check';


public function handle()
{
    $admins = User::where('role_id', 1)->get();

    // Hapus notifikasi stock yang sudah normal
    foreach ($admins as $admin) {

        $normalItems = Item::where('stock', '>=', 10)->get();

        foreach ($normalItems as $item) {

        Notification::where('user_id', $admin->id)
            ->where('item_id', $item->id)
            ->where('title', 'Stock Rendah')
            ->delete();
        }
    }

    // Generate notifikasi stock rendah
    $items = Item::where('stock', '<', 10)->get();

    foreach ($items as $item) {

        foreach ($admins as $admin) {

        Notification::updateOrCreate(

        [
            'user_id' => $admin->id,
            'item_id' => $item->id,
            'title'   => 'Stock Rendah'
        ],

        [
            'message' => $item->item_name.' tersisa '.$item->stock,
            'is_read' => 0
        ]

        );

        }

    }

    $this->info('Stock notification generated');
}

}
