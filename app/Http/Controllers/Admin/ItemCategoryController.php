<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $categories = ItemCategory::latest()->get();

        return view(
            'admin.item_category.index',
            compact('categories')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|max:255',
        ]);

        ItemCategory::create([
            'category_name' => $request->category_name,
            'use_qty'       => $request->has('use_qty'),
        ]);

        return back()->with(
            'success',
            'Kategori berhasil ditambahkan'
        );
    }

    public function update(Request $request, ItemCategory $item_category)
    {
        $request->validate([
            'category_name' => 'required|max:255',
        ]);

        $item_category->update([
            'category_name' => $request->category_name,
            'use_qty'       => $request->has('use_qty'),
        ]);

        return back()->with(
            'success',
            'Kategori berhasil diperbarui'
        );
    }

    public function destroy(ItemCategory $item_category)
    {
        if ($item_category->items()->exists()) {

            return back()->with(
                'error',
                'Kategori tidak dapat dihapus karena masih digunakan oleh item.'
            );
        }

        $item_category->delete();

        return back()->with(
            'success',
            'Kategori berhasil dihapus.'
        );
    }
}