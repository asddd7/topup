<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Banner;
use App\Models\Setting;
use App\Models\Game;
use Illuminate\Http\Request;

class SettingController extends BaseAdminController
{

    public function index()
    {

        $settings = Setting::all()->keyBy('setting_key');

        $games = Game::orderBy('sort_order')
                    ->orderBy('game_name')
                    ->get();

        return view(
            'admin.setting.index',
            compact(
                'settings',
                'games'
            )
        );

    }

public function update(Request $request)
{

    /*
    |--------------------------------------------------------------------------
    | WEBSITE SETTINGS
    |--------------------------------------------------------------------------
    */

    foreach(
        $request->except(
            '_token',
            '_method',
            'games'
        ) as $key=>$value
    ){

        $setting = Setting::updateOrCreate(

            [
                'setting_key'=>$key
            ],

            [
                'setting_value'=>$value,
                'group'=>'general'
            ]

        );
    }



    /*
    |--------------------------------------------------------------------------
    | GAME SETTINGS
    |--------------------------------------------------------------------------
    */


    if($request->has('games'))
    {

        foreach($request->games as $id=>$game)
        {

            Game::where('id',$id)
            ->update([

                'input_label'=>
                    $game['input_label'] ?? null,


                'input_placeholder'=>
                    $game['input_placeholder'] ?? null,


                'input_label_2'=>
                    $game['input_label_2'] ?? null,


                'input_placeholder_2'=>
                    $game['input_placeholder_2'] ?? null,

            ]);

        }

    }



    Cache::forget('website_settings');


    return back()->with(

        'success',

        'Pengaturan berhasil diperbarui'

    );

}

}