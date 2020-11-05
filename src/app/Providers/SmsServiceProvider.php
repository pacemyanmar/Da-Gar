<?php

namespace App\Providers;

use App\Services\BluePlanetSMS2;
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
        if(config('sms.providers.blueplanet.active') == 'blueplanet') {
            $this->app->singleton('blueplanet', function ($app) {
                $blueplanet = new BluePlanetSMS();
                return $blueplanet->setApiUrl(config('sms.providers.blueplanet.api1.api_url'))
                    ->setAccessToken(Settings::get('boom_api_key'))
                    ->setSenderId(config('sms.providers.blueplanet.api1.sender_id'));
            });
        }
        if(config('sms.providers.blueplanet.active') == 'blueplanet2') {
            $this->app->singleton('blueplanet2', function ($app) {
                $blueplanet = new BluePlanetSMS2();
                return $blueplanet->setApiUrl(config('sms.providers.blueplanet.api2.api_url'))
                    ->setUsername(config('sms.providers.blueplanet.api2.username'))
                    ->setAccessToken(config('sms.providers.blueplanet.api2.password'))
                    ->setSenderId(config('sms.providers.blueplanet.api2.sender_id'));
            });
        }
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
