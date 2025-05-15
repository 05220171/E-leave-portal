<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// --- ADD THESE LINES ---
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse; // Assuming your custom response is at this path
// --- END OF ADDED LINES ---

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This is where we bind our custom LoginResponse implementation
     * to the LoginResponseContract provided by Fortify.
     */
    public function register(): void
    {
        // --- ADD THIS BINDING ---
        $this->app->singleton(
            LoginResponseContract::class,
            LoginResponse::class
        );
        // --- END OF BINDING ---

        // You can add other service bindings here if needed
    }

    /**
     * Bootstrap any application services.
     *
     * This method is called after all other service providers have
     * been registered, meaning you can type hint dependencies
     * in this method's parameters.
     */
    public function boot(): void
    {
        // You can add boot logic here, like registering Policies, Observers, etc.
    }
}