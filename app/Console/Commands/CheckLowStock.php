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


$admins = User::where(
    'role_id',
    1
)->get();



$items = Item::where(
    'stock',
    '<',
    10
)->get();



foreach($items as $item){


foreach($admins as $admin){


Notification::firstOrCreate(

[

'user_id'=>$admin->id,

'title'=>'Stock Rendah',

'message'=>
$item->item_name.
' tersisa '.
$item->stock

],

[

'is_read'=>0

]

);


}


}


$this->info(
'Stock notification generated'
);


}


}