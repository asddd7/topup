<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends BaseAdminController
{

    public function index()
    {
        $settings = Setting::all()->keyBy('setting_key');

        return view(
            'admin.setting.index',
            compact('settings')
        );
    }

    public function update(Request $request)
    {

        /*
        |--------------------------------------------------------------------------
        | WEBSITE SETTINGS
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'app_name' => 'nullable|string|max:100',
            'whatsapp' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'maintenance' => 'required|boolean',
            'allow_guest_checkout' => 'required|boolean',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'app_favicon' => 'nullable|image|mimes:png,jpg,jpeg,ico,svg,webp|max:1024',
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPLOAD FAVICON
        |--------------------------------------------------------------------------
        */

        if($request->hasFile('app_favicon')){


            $oldFavicon = Setting::where(
                'setting_key',
                'app_favicon'
            )->first();


            if(
                $oldFavicon &&
                $oldFavicon->setting_value &&
                Storage::disk('public')
                    ->exists($oldFavicon->setting_value)
            ){

                Storage::disk('public')
                    ->delete($oldFavicon->setting_value);

            }


            $faviconPath = $request
                ->file('app_favicon')
                ->store('settings','public');


            Setting::updateOrCreate(

                [
                    'setting_key'=>'app_favicon'
                ],

                [
                    'setting_value'=>$faviconPath,
                    'group'=>'general'
                ]

            );


        }
        if($request->hasFile('app_logo')){

            //hapus logo lama
            $oldLogo = Setting::where(
                'setting_key',
                'app_logo'
            )->first();

            if(
                $oldLogo &&
                $oldLogo->setting_value &&
                Storage::disk('public')->exists($oldLogo->setting_value)
            ){
                Storage::disk('public')
                    ->delete($oldLogo->setting_value);
            }

            $logoPath = $request
                ->file('app_logo')
                ->store('settings','public');

            Setting::updateOrCreate(

                [
                    'setting_key'=>'app_logo'
                ],

                [
                    'setting_value'=>$logoPath,
                    'group'=>'general'
                ]

            );

        }


        $groups = [
            'app_name' => 'general',
            'whatsapp' => 'contact',
            'email' => 'contact',
            'address' => 'contact',
            'facebook' => 'social',
            'instagram' => 'social',
            'youtube' => 'social',
            'maintenance' => 'system',
            'allow_guest_checkout' => 'system',
        ];

        foreach ($groups as $key => $group) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => (string) $validated[$key],
                    'group' => $group,
                ]
            );
        }



        Cache::forgetMany(['website_settings', 'settings']);


        return back()->with(

            'success',

            'Pengaturan berhasil diperbarui'

        );

    }
}