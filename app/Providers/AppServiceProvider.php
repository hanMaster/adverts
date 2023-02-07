<?php

namespace App\Providers;

use App\Services\PaymentService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(PaymentService::class, function(){
            return new PaymentService($this->app->make(DatabaseManager::class));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
