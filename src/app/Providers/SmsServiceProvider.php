<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Registries\SmsProviderRegistry;
use App\Services\BluePlanetSMS;
use Akaunting\Setting\Facade as Settings;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SmsProviderRegistry::class, function($app){
            new SmsProviderRegistry();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->make(new SmsProviderRegistry())
            ->register("blueplanet", (new BluePlanetSMS())
                    ->setApiUrl(Settings::get('boom_api_url','https://boomsms.net/api/sms/json'))
                    ->setAccessToken(Settings::get('boom_api_key'))
                    ->setSenderId(Settings::get('sender_id', 'PACE'))
        );
    }
}
