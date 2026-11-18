<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Show the dedicated registration form for a specific event.
     */
    public function create(Event $event): View|RedirectResponse
    {
        $today = Carbon::today()->toDateString();
        $eventDate = Carbon::parse($event->event_date)->toDateString();

        // Reject if event date has already passed
        if ($eventDate < $today) {
            return redirect()->route('home')->with('error', 'Registration is closed for "' . $event->event_name . '" because this event date has already passed.');
        }

        $allUpcomingEvents = Event::where('event_date', '>=', $today)
            ->orderBy('event_date', 'asc')
            ->get();

        $event->loadCount('registrations');

        return view('registrations.create', compact('event', 'allUpcomingEvents'));
    }

    /**
     * Store a newly created registration in storage.
     */
    public function store(Request $request, ?Event $event = null): RedirectResponse
    {
        // Automatically populate event_id from route parameter if not supplied in form body
        if (!$request->filled('event_id') && $event) {
            $request->merge(['event_id' => $event->id]);
        }

        // Server-side Laravel validation
        $validated = $request->validate([
            'user_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ], [
            'user_name.required' => 'Full Name is required.',
            'user_name.min' => 'Full Name must be at least 2 characters long.',
            'email.required' => 'Email Address is required.',
            'email.email' => 'Please enter a valid email address.',
            'event_id.required' => 'Please select an event to register for.',
            'event_id.exists' => 'The selected event is invalid or does not exist.',
        ]);

        $targetEvent = Event::findOrFail($validated['event_id']);

        // Reject registration for events whose dates have already passed
        $today = Carbon::today()->toDateString();
        $eventDate = Carbon::parse($targetEvent->event_date)->toDateString();
        if ($eventDate < $today) {
            return back()->withInput()->withErrors([
                'event_id' => 'Registration is closed for "' . $targetEvent->event_name . '" because this event date has already passed.',
            ]);
        }

        // Prevent accidental duplicate registrations (same email for the same event)
        $isDuplicate = Registration::where('event_id', $targetEvent->id)
            ->where('email', $validated['email'])
            ->exists();

        if ($isDuplicate) {
            return back()->withInput()->withErrors([
                'email' => 'The email address "' . $validated['email'] . '" is already registered for ' . $targetEvent->event_name . '.',
            ]);
        }

        // Save registration into the registrations table (associate with logged-in user if authenticated)
        $registration = Registration::create([
            'user_id' => Auth::id(),
            'user_name' => $validated['user_name'],
            'email' => $validated['email'],
            'event_id' => $targetEvent->id,
        ]);

        // Redirect to dedicated success page
        return redirect()->route('registrations.success', $registration);
    }

    /**
     * Display the dedicated registration success page.
     */
    public function success(Registration $registration): View
    {
        $registration->load('event');

        return view('registrations.success', compact('registration'));
    }
}
