<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Game;
use App\Models\Item;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        $topSellers = Item::with('game')
            ->where('is_active', 1)
            ->where('top_seller', 1)
            ->take(8)
            ->get();

        $games = Game::where('is_active', 1)
            ->orderBy('game_name')
            ->get();

        return view('dashboard', compact(
            'banners',
            'topSellers',
            'games'
        ));
    }
}