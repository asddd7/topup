<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $settings = Cache::rememberForever(
            'website_settings',
            function () {

                return Setting::pluck(
                    'setting_value',
                    'setting_key'
                )->toArray();

            }
        );

        View::share(
            'settings',
            $settings
        );
    }
}