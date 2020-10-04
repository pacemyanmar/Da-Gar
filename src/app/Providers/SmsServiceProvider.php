<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Registries\SmsProviderRegistry;
use App\Services\BluePlanetSMS;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('blueplanet', function($app) {
            $blueplanet = new BluePlanetSMS();
            return $blueplanet->setApiUrl(Settings::get('boom_api_url','https://boomsms.net/api/sms/json'))
            ->setAccessToken(Settings::get('boom_api_key'))
            ->setSenderId(Settings::get('sender_id', 'PACE'));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        
    }
}
