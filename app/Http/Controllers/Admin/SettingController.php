<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{

    public function index()
    {

        $settings = Setting::all()
            ->keyBy('setting_key');

        return view(
            'admin.setting.index',
            compact('settings')
        );

    }

    public function update(Request $request)
    {

        foreach(
            $request->except(
                '_token',
                '_method'
            ) as $key=>$value
        ){

            Setting::updateOrCreate(

                [

                    'setting_key'=>$key

                ],

                [

                    'setting_value'=>$value,

                    'group'=>'general'

                ]

            );

        }
        Cache::forget('website_settings');
        return back()->with(

            'success',

            'Pengaturan berhasil diperbarui'

        );

    }

}