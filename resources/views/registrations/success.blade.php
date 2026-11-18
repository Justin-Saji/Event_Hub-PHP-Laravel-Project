@extends('layouts.app')

@section('content')
<div class="py-12 md:py-20 bg-slate-50 min-h-[calc(100vh-160px)] flex items-center justify-center">
    <div class="w-full max-w-xl mx-auto px-4 sm:px-6">

        <div class="bg-white rounded-2xl p-8 sm:p-10 border border-slate-200/90 shadow-xs text-center">

            <!-- Clean Success Icon -->
            <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-5">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 mb-3">
                Registration Confirmed
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                You're in, {{ $registration->user_name }}!
            </h1>

            <p class="text-slate-600 mt-2 text-sm max-w-md mx-auto leading-relaxed">
                Your registration has been securely recorded. Below are your event confirmation details.
            </p>

            <!-- Digital Pass Card -->
            <div class="mt-6 p-6 rounded-xl bg-slate-50 border border-slate-200/80 text-left space-y-4">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <span>Attendee Pass</span>
                    <span class="font-mono text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                        #REG-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <!-- Event Title -->
                <div>
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Event</span>
                    <p class="text-base font-bold text-slate-900 mt-0.5">{{ $registration->event->event_name }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Date</span>
                        <p class="text-xs sm:text-sm font-semibold text-slate-800 mt-0.5">
                            {{ $registration->event->event_date->format('l, F j, Y') }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Email</span>
                        <p class="text-xs sm:text-sm font-semibold text-slate-800 mt-0.5 font-mono">
                            {{ $registration->email }}
                        </p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200 text-xs text-slate-400 flex items-center justify-between">
                    <span>Registered: {{ $registration->created_at->format('M d, Y h:i A') }}</span>
                    <span class="text-emerald-700 font-semibold">Active & Confirmed</span>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('home') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Return to Homepage</span>
                </a>

                <a href="{{ route('home') }}#events" 
                   class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-lg text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition-colors shadow-xs">
                    Explore Other Events
                </a>
            </div>

        </div>

    </div>
</div>
@endsection
