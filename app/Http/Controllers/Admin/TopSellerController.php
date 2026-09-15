<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopSellerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $gameId = $request->input('game_id');
        $stockStatus = $request->input('stock_status', 'all');

        $itemsQuery = Item::with('game')
            ->when($search !== '', function ($query) use ($search) {

                $query->where(function ($q) use ($search) {

                    $q->where('item_name', 'like', "%{$search}%")
                        ->orWhereHas('game', function ($gameQuery) use ($search) {

                            $gameQuery->where(
                                'game_name',
                                'like',
                                "%{$search}%"
                            );

                        });

                });

            })
            ->when($gameId, function ($query) use ($gameId) {

                $query->where('game_id', $gameId);

            })
            ->when($stockStatus !== 'all', function ($query) use ($stockStatus) {

                if ($stockStatus === 'instock') {

                    $query->whereRaw(
                        "LOWER(REPLACE(COALESCE(moogold_stock_status, ''), ' ', '')) = ?",
                        ['instock']
                    );

                } elseif ($stockStatus === 'outofstock') {

                    $query->whereRaw(
                        "LOWER(REPLACE(COALESCE(moogold_stock_status, ''), ' ', '')) = ?",
                        ['outofstock']
                    );

                } elseif ($stockStatus === 'unknown') {

                    $query->where(function ($q) {

                        $q->whereNull('moogold_stock_status')
                            ->orWhere('moogold_stock_status', '')
                            ->orWhereRaw(
                                "LOWER(REPLACE(moogold_stock_status, ' ', '')) NOT IN (?, ?)",
                                ['instock', 'outofstock']
                            );

                    });

                }

            })
            ->orderByDesc('top_seller')
            ->orderBy('game_id')
            ->orderBy('item_name');

        $items = $itemsQuery->get();

        $games = Game::query()
            ->orderBy('game_name')
            ->get([
                'id',
                'game_name',
            ]);

        return view('admin.top-seller.index', compact(
            'items',
            'games',
            'search',
            'gameId',
            'stockStatus'
        ));
    }


    public function update(Request $request)
    {
        $visibleItemIds = $request->input('visible_items', []);
        $topSellerIds = $request->input('top_seller', []);

        $visibleItemIds = array_map('intval', $visibleItemIds);
        $topSellerIds = array_map('intval', $topSellerIds);

        DB::transaction(function () use (
            $visibleItemIds,
            $topSellerIds
        ) {

            /*
             * Hanya item yang sedang tampil di hasil pencarian/filter
             * yang akan diubah.
             *
             * Item di luar filter tetap mempertahankan statusnya.
             */
            if (!empty($visibleItemIds)) {

                Item::whereIn('id', $visibleItemIds)
                    ->update([
                        'top_seller' => false,
                    ]);


                /*
                 * Hanya item yang masih In Stock yang boleh
                 * menjadi Top Seller.
                 */
                if (!empty($topSellerIds)) {

                Item::whereIn('id', $topSellerIds)
                    ->whereRaw(
                        "LOWER(REPLACE(moogold_stock_status, ' ', '')) = ?",
                        ['instock']
                    )
                    ->update([
                        'top_seller' => true,
                    ]);
                }
            }
        });


        /*
         * Pertahankan filter setelah menyimpan.
         */
        return redirect()
            ->route('top-seller.index', [
                'search' => $request->input('search'),
                'game_id' => $request->input('game_id'),
                'stock_status' => $request->input('stock_status', 'all'),
            ])
            ->with(
                'success',
                'Pengaturan Top Seller berhasil disimpan.'
            );
    }
}