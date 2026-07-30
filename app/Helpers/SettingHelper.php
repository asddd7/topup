<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('setting')) {

    function setting($key, $default = null)
    {
        $settings = Cache::rememberForever('website_settings', function () {

            return Setting::pluck(
                'setting_value',
                'setting_key'
            )->toArray();

        });

        return $settings[$key] ?? $default;
    }
}

if (! function_exists('clearSettingCache')) {

    function clearSettingCache()
    {
        Cache::forget('website_settings');
    }
}