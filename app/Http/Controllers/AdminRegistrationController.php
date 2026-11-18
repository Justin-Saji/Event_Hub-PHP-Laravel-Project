<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRegistrationController extends Controller
{
    /**
     * Display a listing of registrations with search, filter, and pagination.
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $eventId = $request->input('event_id');

        $query = Registration::with('event')->latest();

        // Search by attendee name or email
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        // Filter by selected event
        if (!empty($eventId)) {
            $query->where('event_id', $eventId);
        }

        $registrations = $query->paginate(10)->withQueryString();
        $events = Event::orderBy('event_name')->get();

        return view('admin.registrations.index', compact('registrations', 'events', 'search', 'eventId'));
    }

    /**
     * Display the specified registration details.
     */
    public function show(Registration $registration): View
    {
        $registration->load('event');

        return view('admin.registrations.show', compact('registration'));
    }

    /**
     * Remove the specified registration from MySQL storage.
     */
    public function destroy(Registration $registration): RedirectResponse
    {
        $name = $registration->user_name;
        $id = $registration->id;
        $eventName = $registration->event?->event_name ?? 'the event';

        // Delete from MySQL
        $registration->delete();

        return redirect()->route('admin.registrations.index')
            ->with('success', "Registration #{$id} for {$name} ({$eventName}) was deleted successfully.");
    }
}

