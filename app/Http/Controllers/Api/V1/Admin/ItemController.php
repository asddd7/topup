<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Game;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    /**
     * GET /api/v1/admin/items
     */
    public function index(Request $request)
    {
        $query = Item::query()
            ->with([
                'game',
                'category',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Filter Game
        |--------------------------------------------------------------------------
        */

        if ($request->filled('game_id')) {
            $query->where(
                'game_id',
                $request->game_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Category
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->category_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('is_active')) {
            $query->where(
                'is_active',
                filter_var(
                    $request->is_active,
                    FILTER_VALIDATE_BOOLEAN
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(
                'item_name',
                'like',
                "%{$search}%"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $items = $query
            ->latest()
            ->paginate(
                $request->integer('per_page', 20)
            );

        return response()->json([
            'success' => true,
            'message' => 'Data item berhasil diambil.',
            'data' => $items,
        ]);
    }


    /**
     * POST /api/v1/admin/items
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'game_id' => [
                'required',
                'integer',
                'exists:games,id',
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:item_categories,id',
            ],

            'item_name' => [
                'required',
                'string',
                'max:255',
            ],

            'qty' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'price' => [
                'required',
                'integer',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'top_seller' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default value
        |--------------------------------------------------------------------------
        */

        $validated['qty'] =
            $validated['qty'] ?? 0;

        $validated['is_active'] =
            $validated['is_active'] ?? true;

        $validated['top_seller'] =
            $validated['top_seller'] ?? false;

        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        $item = Item::create(
            $validated
        );

        /*
        |--------------------------------------------------------------------------
        | Load relationship
        |--------------------------------------------------------------------------
        */

        $item->load([
            'game',
            'category',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dibuat.',
            'data' => $item,
        ], 201);
    }


    /**
     * GET /api/v1/admin/items/{item}
     */
    public function show(Item $item)
    {
        $item->load([
            'game',
            'category',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail item berhasil diambil.',
            'data' => $item,
        ]);
    }


    /**
     * PUT /api/v1/admin/items/{item}
     */
    public function update(
        Request $request,
        Item $item
    ) {
        $validated = $request->validate([

            'game_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:games,id',
            ],

            'category_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:item_categories,id',
            ],

            'item_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'qty' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],

            'price' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],

            'stock' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'image' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'top_seller' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $item->update(
            $validated
        );

        $item->load([
            'game',
            'category',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil diperbarui.',
            'data' => $item,
        ]);
    }


    /**
     * DELETE /api/v1/admin/items/{item}
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus.',
        ]);
    }
}