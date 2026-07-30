<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{


protected $fillable=[

'invoice_number',

'user_id',

'game_id',

'player_data',

'payment_id',

'discount_id',

'player_uid',

'server_id',

'nickname',

'subtotal',

'discount',

'total_price',

'payment_proof',

'status',

'notes',

'user_id',

'guest_name',

'guest_email',

'guest_phone',

'guest_token'

];



protected $casts=[

'player_data'=>'array'

];



public function user()
{
    return $this->belongsTo(User::class);
}



public function game()
{
    return $this->belongsTo(Game::class);
}



public function details()
{
    return $this->hasMany(
        OrderDetail::class
    );
}



public function discount()
{
    return $this->belongsTo(
        Discount::class
    );
}

public function payment()
{
    return $this->belongsTo(Payment::class);
}

public function paymentLogs()
{
    return $this->hasMany(PaymentLog::class);
}
}