<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display the main admin dashboard overview.
     */
    public function dashboard(): View
    {
        $today = Carbon::today();

        // Summary cards calculated dynamically from MySQL
        $totalRegistrations = Registration::count();
        $totalEvents = Event::count();
        $todayRegistrations = Registration::whereDate('created_at', $today)->count();

        // Recent activity
        $recentRegistrations = Registration::with('event')
            ->latest()
            ->take(6)
            ->get();

        // Upcoming events overview
        $upcomingEvents = Event::where('event_date', '>=', $today->toDateString())
            ->orderBy('event_date', 'asc')
            ->withCount('registrations')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRegistrations',
            'totalEvents',
            'todayRegistrations',
            'recentRegistrations',
            'upcomingEvents'
        ));
    }
}
