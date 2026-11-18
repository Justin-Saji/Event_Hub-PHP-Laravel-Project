<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display the homepage with upcoming events.
     */
    public function index(): View
    {
        $today = Carbon::today()->toDateString();

        // Fetch upcoming events only, ordered by date ascending
        $upcomingEvents = Event::where('event_date', '>=', $today)
            ->orderBy('event_date', 'asc')
            ->withCount('registrations')
            ->get();

        return view('home', compact('upcomingEvents'));
    }

    /**
     * Display the specified event with details and registration form.
     */
    public function show(Event $event): View
    {
        $event->loadCount('registrations');

        return view('events.show', compact('event'));
    }
}

