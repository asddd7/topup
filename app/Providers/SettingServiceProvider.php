<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
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