@extends('layouts.app')

@section('content')
<div class="py-10 md:py-14 bg-slate-50 min-h-[calc(100vh-160px)]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Back Navigation -->
        <nav class="mb-6 flex items-center justify-between">
            <a href="{{ route('home') }}#events" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Events List</span>
            </a>
            <span class="text-xs text-slate-400 font-medium">
                Event Registration
            </span>
        </nav>

        <!-- Main Form Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left Panel: Event Summary -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white rounded-xl p-6 border border-slate-200/90 shadow-xs">
                    
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $event->event_date->format('l, F j, Y') }}
                        </span>
                        <span class="text-xs font-medium text-slate-500">
                            {{ $event->event_date->diffForHumans() }}
                        </span>
                    </div>

                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-snug">
                        {{ $event->event_name }}
                    </h1>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">About This Event</h2>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                            {{ $event->description }}
                        </p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Registration Type: <strong class="text-slate-800">Free</strong></span>
                        <span>{{ $event->registrations_count }} {{ Str::plural('attendee', $event->registrations_count) }} registered</span>
                    </div>

                </div>
            </div>

            <!-- Right Panel: Registration Form -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-xl p-6 sm:p-8 border border-slate-200/90 shadow-xs">

                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Reserve Your Seat</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Please provide your details below to confirm attendance.</p>
                    </div>

                    @auth
                        <div class="mb-5 p-3 rounded-lg bg-blue-50/60 border border-blue-100 flex items-center justify-between text-xs text-blue-900">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span>Signed in as <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }})</span>
                            </div>
                            <span class="text-[11px] text-blue-600 font-medium hidden sm:inline">Pass linked to account</span>
                        </div>
                    @else
                        <div class="mb-5 p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center justify-between">
                            <span>Registering as guest. Already have an account?</span>
                            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-800 transition-colors">Sign in &rarr;</a>
                        </div>
                    @endauth

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-5 p-3.5 rounded-lg bg-red-50 border border-red-200 text-red-900 text-xs shadow-xs" role="alert">
                            <p class="font-bold text-red-900 mb-1">Please correct the following errors:</p>
                            <ul class="list-disc list-inside space-y-0.5 text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="registration-form" action="{{ route('registrations.store', $event) }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Selected Event Field -->
                        <div>
                            <label for="event_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                                Selected Event <span class="text-red-500">*</span>
                            </label>
                            
                            <div class="relative">
                                <select name="event_id" 
                                        id="event_id" 
                                        required 
                                        class="w-full pl-3.5 pr-10 py-2.5 rounded-lg border {{ $errors->has('event_id') ? 'border-red-300 bg-red-50/20' : 'border-slate-300 bg-slate-50/50' }} text-slate-800 text-sm focus:outline-hidden focus:border-blue-600 focus:bg-white focus:ring-1 focus:ring-blue-600 transition-colors appearance-none">
                                    @foreach($allUpcomingEvents as $upcoming)
                                        <option value="{{ $upcoming->id }}" 
                                                data-url="{{ route('registrations.create', $upcoming) }}"
                                                {{ (string)old('event_id', $event->id) === (string)$upcoming->id ? 'selected' : '' }}>
                                            {{ $upcoming->event_name }} ({{ $upcoming->event_date->format('M d, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('event_id')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Full Name Field -->
                        <div>
                            <label for="user_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="user_name" 
                                   id="user_name" 
                                   value="{{ old('user_name', Auth::check() ? Auth::user()->name : '') }}" 
                                   required 
                                   placeholder="Jane Doe" 
                                   autocomplete="name"
                                   class="w-full px-3.5 py-2.5 rounded-lg border {{ $errors->has('user_name') ? 'border-red-300 bg-red-50/20' : 'border-slate-300 bg-white' }} text-slate-900 placeholder-slate-400 text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors shadow-xs">
                            @error('user_name')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email Address Field -->
                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email', Auth::check() ? Auth::user()->email : '') }}" 
                                   required 
                                   placeholder="jane@example.com" 
                                   autocomplete="email"
                                   class="w-full px-3.5 py-2.5 rounded-lg border {{ $errors->has('email') ? 'border-red-300 bg-red-50/20' : 'border-slate-300 bg-white' }} text-slate-900 placeholder-slate-400 text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors shadow-xs">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" 
                                    id="submit-btn" 
                                    class="w-full py-2.5 px-4 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                <span id="submit-btn-text">Confirm Registration</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                        <span class="text-[11px] text-slate-400">By registering, you confirm attendance for this event session.</span>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('registration-form');
        const submitBtn = document.getElementById('submit-btn');
        const submitBtnText = document.getElementById('submit-btn-text');
        const eventSelect = document.getElementById('event_id');

        if (eventSelect) {
            eventSelect.addEventListener('change', (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const targetUrl = selectedOption.getAttribute('data-url');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });
        }

        if (form && submitBtn) {
            form.addEventListener('submit', () => {
                if (!form.checkValidity || form.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    if (submitBtnText) {
                        submitBtnText.textContent = 'Processing...';
                    }
                }
            });

            window.addEventListener('pageshow', () => {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                if (submitBtnText) {
                    submitBtnText.textContent = 'Confirm Registration';
                }
            });
        }
    });
</script>
@endsection
