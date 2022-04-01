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
