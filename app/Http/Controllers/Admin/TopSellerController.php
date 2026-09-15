<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopSellerController extends Controller
{
    public function index()
    {
        $items = Item::with('game')
            ->orderByDesc('top_seller')
            ->latest()
            ->get();

        return view('admin.top-seller.index', compact('items'));
    }


    public function update(Request $request)
    {
        $topSellerIds = $request->input('top_seller', []);

        DB::transaction(function () use ($topSellerIds) {

            Item::query()->update([
                'top_seller' => false,
            ]);

            if (!empty($topSellerIds)) {

                Item::whereIn('id', $topSellerIds)
                    ->update([
                        'top_seller' => true,
                    ]);
            }
        });

        return redirect()
            ->route('top-seller.index')
            ->with('success', 'Pengaturan Top Seller berhasil disimpan.');
    }
}