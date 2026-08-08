<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use App\Models\Notification;
use App\Models\User;

class CheckLowStock extends Command
{
    protected $signature = 'stock:check';

    protected $description = 'Check low stock and create admin notifications';

    public function handle()
    {
        /*
        |--------------------------------------------------------------------------
        | Ambil semua admin
        |--------------------------------------------------------------------------
        */

        $admins = User::where('role_id', 1)->get();


        /*
        |--------------------------------------------------------------------------
        | Hapus notifikasi jika stock sudah normal
        |--------------------------------------------------------------------------
        */

        foreach ($admins as $admin) {

            $normalItems = Item::where('stock', '>=', 10)->get();

            foreach ($admins as $admin) {

                foreach ($normalItems as $item) {

                    Notification::where('user_id', $admin->id)
                        ->where('item_id', $item->id)
                        ->where('title', 'Stock Rendah')
                        ->delete();
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Ambil item stock rendah
        |--------------------------------------------------------------------------
        */

        $items = Item::with('game')
            ->where('stock', '<', 10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Buat notification
        |--------------------------------------------------------------------------
        */

        foreach ($items as $item) {

            foreach ($admins as $admin) {

                $gameName = $item->game
                    ? $item->game->game_name
                    : 'Game Tidak Diketahui';


                $message =
                    $gameName
                    .' - '
                    .$item->item_name
                    .' tersisa '
                    .$item->stock;


                Notification::updateOrCreate(
                    [
                        'user_id' => $admin->id,
                        'item_id' => $item->id,
                        'title'   => 'Stock Rendah'
                    ],
                    [
                        'message' => $message,
                        'is_read' => 0
                    ]
                );
            }
        }


        $this->info('Stock notification generated successfully.');

        return Command::SUCCESS;
    }
}