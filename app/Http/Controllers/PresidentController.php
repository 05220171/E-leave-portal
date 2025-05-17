<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresidentController extends Controller
{
    public function dashboard()
    {
        // Logic for President dashboard (e.g., fetch pending leaves for President)
        // For now, just a simple view
        $user = Auth::user();
        return view('president.dashboard', compact('user'));
    }

    // Add other methods for President actions later
}