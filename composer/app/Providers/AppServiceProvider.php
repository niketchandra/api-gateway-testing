<?php

namespace App\Providers;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $sharedSettings = [
            'siteLogoUrl' => '',
            'siteContent' => '',
            'siteFeatures' => [],
        ];

        try {
            if (Schema::hasTable('admin_settings')) {
                $sharedSettings['siteLogoUrl'] = (string) AdminSetting::getValue('site_logo_url', '');
                $sharedSettings['siteContent'] = (string) AdminSetting::getValue('site_content', '');

                $featuresRaw = AdminSetting::getValue('site_features', '[]');
                $decodedFeatures = json_decode((string) $featuresRaw, true);
                $sharedSettings['siteFeatures'] = is_array($decodedFeatures) ? $decodedFeatures : [];
            }
        } catch (\Throwable $e) {
            $sharedSettings = [
                'siteLogoUrl' => '',
                'siteContent' => '',
                'siteFeatures' => [],
            ];
        }

        View::share($sharedSettings);
    }
}
