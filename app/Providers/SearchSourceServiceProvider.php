<?php

namespace App\Providers;

use App\UseCase\Yandex\Search;
use Illuminate\Support\ServiceProvider;

class SearchSourceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('yandex', function ($app) {
            return $app->make(Search::class);
        });

        $this->app->bind('ostrovok', function ($app) {
            return $app->make(\App\UseCase\Ostrovok\Search::class);
        });

        $this->app->bind('sutochno', function ($app) {
            return $app->make(\App\UseCase\Sutochno\Search::class);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
