<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard view or redirect based on user role.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $userRole = strtolower(trim($user->role));

        // 1. Handle Redirects for non-student roles
        $redirectRouteName = match ($userRole) {
            'hod'        => 'hod.dashboard',
            'dsa'        => 'dsa.dashboard',
            'daa'        => 'daa.dashboard',
            'president'  => 'president.dashboard',
            'sso'        => 'sso.dashboard',
            'admin', 'superadmin' => 'superadmin.dashboard',
            default      => null,
        };

        if ($redirectRouteName) {
            return redirect()->route($redirectRouteName);
        }

        // 2. Handle the Student Dashboard View
        if ($userRole === 'student') {
            $userName = $user->name;
            
            // MODIFICATION: Point to the correct view file 'dashboard'
            return view('dashboard', compact('userName'));
        }

        // 3. Fallback for any other case
        Log::warning('DashboardController: Role was not student and no redirect matched. Showing generic dashboard.', [
            'user_id' => $user->id, 
            'role' => $userRole
        ]);
        
        return view('dashboard');
    }
}