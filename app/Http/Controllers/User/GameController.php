<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Item;
use App\Models\Payment;
use Illuminate\Http\Request;

class GameController extends Controller
{

    public function show(Game $game)
    {
        $items = Item::where('game_id', $game->id)
            ->where('is_active', 1)
            ->get();

        $payments = Payment::where('is_active', 1)
            ->orderBy('payment_type')
            ->get();

        return view('game.show', compact(
            'game',
            'items',
            'payments'
        ));
    }

}