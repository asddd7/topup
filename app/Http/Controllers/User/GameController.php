<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Item;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | WEB
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | API - GAME LIST
    |--------------------------------------------------------------------------
    */

    public function index(): JsonResponse
    {
        $games = Game::query()
            ->where('is_active', 1)
            ->orderBy('game_name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar game berhasil diambil.',
            'data' => [
                'games' => $games,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | API - GAME DETAIL
    |--------------------------------------------------------------------------
    */

    public function showApi(Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game tidak tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail game berhasil diambil.',
            'data' => [
                'game' => $game,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | API - GAME ITEMS
    |--------------------------------------------------------------------------
    */

    public function items(Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game tidak tersedia.',
            ], 404);
        }

        $items = Item::query()
            ->where('game_id', $game->id)
            ->where('is_active', 1)
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Item game berhasil diambil.',
            'data' => [
                'game' => $game,
                'items' => $items,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | API - GAME PAYMENTS
    |--------------------------------------------------------------------------
    */

    public function payments(Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game tidak tersedia.',
            ], 404);
        }

        $payments = Payment::query()
            ->where('is_active', 1)
            ->orderBy('payment_type')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Payment berhasil diambil.',
            'data' => [
                'game' => $game,
                'payments' => $payments,
            ],
        ]);
    }
}