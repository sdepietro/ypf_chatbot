<?php

namespace App\Providers;

use App\Contracts\AIProviderInterface;
use App\Services\AI\AIProviderFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind AIProviderInterface to the factory-created provider
        $this->app->bind(AIProviderInterface::class, function ($app) {
            return AIProviderFactory::create();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
