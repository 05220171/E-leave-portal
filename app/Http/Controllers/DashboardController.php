<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;         // Import Auth facade
use Illuminate\Support\Facades\Log;          // Import Log facade <<<<<<< ADD THIS
use Illuminate\View\View;                  // Import View for type hinting
use Illuminate\Http\RedirectResponse;      // Import RedirectResponse for type hinting

class DashboardController extends Controller
{
    /**
     * Show the application dashboard view or redirect based on user role.
     * This method is hit AFTER successful login because config('fortify.home') is '/dashboard'.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        // --- DEBUG: Check if method is hit ---
        Log::info('DashboardController@index method CALLED.');

        if (!Auth::check()) {
            Log::warning('DashboardController: User not authenticated, redirecting to login.');
            return redirect('/login');
        }

        $user = Auth::user();

        // --- DEBUG: Log User Info ---
        Log::info('DashboardController: Authenticated User.', [
            'user_id' => $user->id ?? 'N/A',
            'role' => $user->role ?? 'ROLE PROPERTY MISSING/NULL' // Check what the role value actually IS
        ]);

        if (!$user || !property_exists($user, 'role') || $user->role === null) {
             Log::error('DashboardController: User object or role property missing/null. Showing default dashboard.', ['user_id' => $user->id ?? 'N/A']);
             return view('dashboard')->with('warning', 'Account role information missing or invalid.');
        }

        // Trim whitespace and convert to lowercase for reliable matching
        $userRole = strtolower(trim($user->role));

        // --- DEBUG: Log Processed Role ---
        Log::info('DashboardController: Processed role for matching.', ['processed_role' => $userRole]);

        $redirectRouteName = match ($userRole) {
            'hod'        => 'hod.dashboard',
            'dsa'        => 'dsa.dashboard',
            'daa'        => 'daa.dashboard',        // ADDED
            'president'  => 'president.dashboard',
            'sso'        => 'sso.dashboard',
            'admin'      => 'admin.home',
            'superadmin' => 'admin.home',
            'student'    => null, // Explicitly null for students
            default      => null, // Explicitly null for default/unknown
        };

        // --- DEBUG: Log Determined Route Name ---
        Log::info('DashboardController: Determined redirect route name.', ['target_route_name' => $redirectRouteName ?? 'NULL (Should be Student/Default)']);

        // --- Perform Redirect if needed ---
        if ($redirectRouteName !== null) {
            try {
                $targetUrl = route($redirectRouteName); // Generate URL to check if route exists

                Log::info('DashboardController: Attempting redirect.', [
                    'user_id' => $user->id,
                    'role' => $userRole,
                    'target_route' => $redirectRouteName,
                    'target_url' => $targetUrl // Log the actual URL
                ]);
                return redirect()->route($redirectRouteName); // Perform redirect

            } catch (\InvalidArgumentException $e) {
                 Log::error("DashboardController: Target route [{$redirectRouteName}] not defined.", [
                    'user_id' => $user->id,
                    'role' => $userRole,
                    'error' => $e->getMessage()
                 ]);
                 // Fallback to showing the default dashboard with a warning
                 return view('dashboard')->with('warning', "Could not redirect to specific dashboard (Route '{$redirectRouteName}' not found). Check routes/web.php.");
            }
        }

        // --- Show Default Dashboard ---
        // This section is reached only if $redirectRouteName was null (student or unknown role)
        if ($userRole !== 'student') {
             // This indicates an issue if a non-student role didn't match above
             Log::warning('DashboardController: Role was not student but no redirect route matched. Showing default dashboard.', ['user_id' => $user->id, 'role' => $userRole]);
        }

        Log::info('DashboardController: Showing default dashboard view for student/fallback.', ['user_id' => $user->id, 'role' => $userRole]);
        return view('dashboard');
    }
}