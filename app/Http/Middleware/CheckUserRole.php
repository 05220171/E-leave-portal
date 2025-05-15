<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  // Accept one or more roles as arguments
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // Redirect to login or return unauthorized if API
             return redirect('login');
             // Or for API: abort(401, 'Unauthenticated.');
        }

        // Get the currently authenticated user
        $user = Auth::user();

        // Check if the user's role matches any of the required roles
        // Using 'role' column as confirmed previously
        foreach ($roles as $role) {
            // Use $user->role here
            if ($user->role === $role) {
                // Role matches, allow the request to proceed
                return $next($request);
            }
        }

        // If no role matched after checking all provided roles
        // Deny access - Forbidden
        abort(403, 'Unauthorized action. Required role not met.');

        // Alternatively, you could redirect them elsewhere:
        // return redirect('/dashboard')->with('error', 'You do not have permission to access this page.');
    }
}