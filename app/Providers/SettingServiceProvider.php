<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Jangan query settings sebelum tabel tersedia
        |--------------------------------------------------------------------------
        |
        | Penting untuk:
        | - php artisan migrate
        | - fresh installation
        | - deployment Railway
        | - database baru
        |
        */

        if (!Schema::hasTable('settings')) {
            return;
        }

        Cache::rememberForever('settings', function () {

            return Setting::pluck(
                'setting_value',
                'setting_key'
            )->toArray();

        });
    }
}