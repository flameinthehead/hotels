<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('GuzzleHttp\Client', function () {
            return new \GuzzleHttp\Client(['verify' => false]);
        });

        $this->app->bind(Serializer::class, function ($app) {
            $encoders = [];
            $normalizers = [new GetSetMethodNormalizer()];
            return new Serializer($normalizers, $encoders);
        });
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
