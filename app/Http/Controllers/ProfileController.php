<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's account and registered events.
     */
    public function show(Request $request): View
    {
        $user = Auth::user();

        // Retrieve registrations associated with the user's ID or their email address
        $registrations = Registration::with('event')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $user->email);
            })
            ->latest()
            ->get();

        return view('profile.index', compact('user', 'registrations'));
    }
}

