<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{

    public function index()
    {
        $banners = Banner::with('game')
            ->orderBy('sort_order')
            ->get();

        $games = Game::where('is_active',1)->get();

        return view(
            'admin.banner.index',
            compact(
                'banners',
                'games'
            )
        );
    }



    public function store(Request $request)
    {

        $request->validate([

            'title'=>'required',

            'game_id'=>'nullable|exists:games,id',

            'image'=>'required|image|max:2048',

            'sort_order'=>'nullable|integer'

        ]);


        $image = $request
            ->file('image')
            ->store('banners','public');


        Banner::create([

        'game_id'=>$request->game_id,

        'title'=>$request->title,

        'image'=>$image,

        'link'=>$request->game_id 
                ? null 
                : $request->link,

        'description'=>$request->description,

        'is_active'=>$request->has('is_active'),

        'sort_order'=>$request->sort_order

        ]);

        return back()
            ->with('success','Banner berhasil ditambahkan');
    }



    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'required|max:255',
            'game_id' => 'nullable|exists:games,id',
            'image' => 'nullable|image|max:2048',
            'link' => 'nullable|max:255',
            'description' => 'nullable',
            'sort_order' => 'nullable|integer',
        ]);

        $data = [
            'game_id' => $request->game_id,
            'title' => $request->title,
            'link' => $request->link,
            'description' => $request->description,
            'sort_order' => $request->sort_order,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()
            ->route('admin.banner.index')
            ->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {

        $banner->delete();

        return back()->with(
            'success',
            'Banner berhasil dihapus'
        );

    }

}