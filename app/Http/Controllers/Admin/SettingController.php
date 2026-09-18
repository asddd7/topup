<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Banner;
use App\Models\Setting;
use App\Services\MooGold\MooGoldService;
use Illuminate\Http\Request;
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
        
            $request->validate([

                'app_logo' =>
                    'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',

                'app_favicon' =>
                    'nullable|image|mimes:png,jpg,jpeg,ico,svg,webp|max:1024',

                'usd_idr_rate' =>
                    'nullable|numeric|gt:0',

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


        foreach(
            $request->except(
                '_token',
                '_method',
                'app_logo',
                'app_favicon'
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



        Cache::forget('website_settings');


        return back()->with(

            'success',

            'Pengaturan berhasil diperbarui'

        );

    }

    public function moogoldBalance(MooGoldService $mooGoldService)
    {
        try {

            $response = $mooGoldService->balance();

            $data = $response['data'] ?? $response;

            $balance = (float) ($data['balance'] ?? 0);
            $currency = $data['currency'] ?? 'USD';

            $usdIdrRate = (float) (
                Setting::where('setting_key', 'usd_idr_rate')
                    ->value('setting_value') ?? 0
            );

            $balanceIdr = null;

            if ($usdIdrRate > 0 && strtoupper($currency) === 'USD') {
                $balanceIdr = $balance * $usdIdrRate;
            }

            return response()->json([
                'success' => true,
                'currency' => $currency,
                'balance' => $balance,
                'usd_idr_rate' => $usdIdrRate,
                'balance_idr' => $balanceIdr,
            ]);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil saldo MooGold.',
            ], 500);
        }
    }
}