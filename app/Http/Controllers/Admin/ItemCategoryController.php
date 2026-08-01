<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\ItemCategory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ItemCategoryController extends BaseAdminController
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

        $item_category = ItemCategory::create([
            'category_name' => $request->category_name,
            'use_qty'       => $request->has('use_qty'),
        ]);

        $this->activity->log(
        'ItemCategory',
        'Create',
        'Create item category : '.$item_category->category_name,
        $item_category,
        null,
        $item_category->toArray()
    );

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
        $old = $item_category->toArray();
        $item_category->update([
            'category_name' => $request->category_name,
            'use_qty'       => $request->has('use_qty'),
        ]);

        
        $this->activity->log(
            'ItemCategory',
            'Update',
            'Update item category : '.$item_category->category_name,
            $item_category,
            $old,
            $item_category->fresh()->toArray()
        );

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
        $old = $item_category->toArray();
        $this->activity->log(
            'ItemCategory',
            'Delete',
            'Delete item category : '.$item_category->category_name,
            $item_category,
            $old,
            null
        );
        $item_category->delete();

        return back()->with(
            'success',
            'Kategori berhasil dihapus.'
        );
    }
}