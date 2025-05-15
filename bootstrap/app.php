<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware; // Ensure this is imported

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // Added api routes file just in case
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) { // <-- Inside this function

        // Register your route middleware alias here
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class, // <-- THIS IS THE LINE YOU NEED
            // Add any other custom aliases here in the future, like:
            // 'isAdmin' => \App\Http\Middleware\CheckIfAdmin::class,
        ]);

        // Other middleware registrations might go here (e.g., CSRF exceptions)
        // $middleware->validateCsrfTokens(except: [ ... ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Exception handling configuration
    })->create();