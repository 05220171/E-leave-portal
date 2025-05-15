<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use App\Models\User; // Only needed if you use the User model directly

class HomeController extends Controller
{
    /**
     * Handle the redirection based on user type after login.
     * This method should be called by the route defined as the redirect path
     * after login (e.g., '/home').
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function handleRedirect() // Renamed from 'redirect' for clarity
    {
        if (Auth::check()) // Use Auth::check() for boolean check
        {
            $user = Auth::user();

            switch ($user->usertype) {
                case 'student':
                    // **FIXED**: Redirect to the named route 'dashboard'
                    // which should point to resources/views/dashboard.blade.php
                    return redirect()->route('dashboard');
                    // Alternative if you don't have a named route:
                    // return redirect('/dashboard');
                    // Alternative if '/home' IS the student dashboard URL and you want to show it directly:
                    // return view('dashboard'); // Or 'student.dashboard' if that's the view name

                case 'hod':
                    // This was already good (assuming 'hod.dashboard' route exists)
                    return redirect()->route('hod.dashboard');

                case 'dsa':
                    // Redirecting is generally better post-login than returning view directly
                    // Assuming 'dsa.dashboard' route exists:
                    return redirect()->route('dsa.dashboard');
                    // Or return redirect('/dsa/dashboard');
                    // Or return view('dsa.dashboard'); // If route points here anyway

                case 'sso':
                     // Assuming 'sso.dashboard' route exists:
                    return redirect()->route('sso.dashboard');
                    // Or return redirect('/sso/dashboard');
                    // Or return view('sso.dashboard'); // If route points here anyway

                case 'superadmin':
                     // Assuming 'admin.home' route exists:
                    return redirect()->route('admin.home');
                     // Or return redirect('/admin/home');
                    // Or return view('admin.home'); // If route points here anyway

                default:
                    // Handle unknown user types or users with no type set
                    Auth::logout(); // Log them out for safety
                    return redirect('/login')->with('error', 'Your account type is not configured.');
            }
        } else {
            // If somehow a non-logged-in user hits this route, send them to login
            return redirect('/login');
        }
    }

    /**
     * Example: Display the public welcome page (e.g., for route '/')
     * You might already have this logic in your web.php routes file.
     */
    public function index()
    {
        // If logged in, redirect immediately based on type
        if(Auth::check()){
            return $this->handleRedirect();
        }
        // Otherwise, show the public welcome page
        return view('student.home'); // Assumes you have resources/views/welcome.blade.php
    }
}