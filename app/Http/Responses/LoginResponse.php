<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): Response
    {
        $user = Auth::user();

        // Default redirect URL.
        // It's good practice to use a named route for the default if possible.
        // Ensure config('fortify.home') points to a valid path or use a fallback like route('dashboard').
        $redirectUrl = config('fortify.home', route('dashboard'));

        if ($user) { // Check if a user is authenticated
            // --- ROLE CHECK LOGIC (Using 'role' column) ---
            if ($user->role === 'admin') { // User with 'admin' role
                // Redirect to the route named 'superadmin.dashboard'.
                // The route NAME can remain 'superadmin.dashboard' even if the ROLE is 'admin'.
                // This name is used internally to generate the URL.
                $redirectUrl = route('superadmin.dashboard');
            } elseif ($user->role === 'hod') {
                $redirectUrl = route('hod.dashboard');
            } elseif ($user->role === 'dsa') {
                $redirectUrl = route('dsa.dashboard');
            } elseif ($user->role === 'sso') {
                $redirectUrl = route('sso.dashboard');
            } elseif ($user->role === 'student') {
                 $redirectUrl = route('dashboard'); // Students go to the general dashboard
            }
            // No 'else' needed here if the default $redirectUrl set above is sufficient
            // for any other roles or users without a specific role handled.
        }

        return $request->wantsJson()
                   ? new JsonResponse('', 204)
                   // Using intended() is good practice. If the user was trying to access
                   // a specific page before login, they'll be sent there. Otherwise, to $redirectUrl.
                   : redirect()->intended($redirectUrl);
    }
}