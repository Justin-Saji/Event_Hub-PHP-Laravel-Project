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
            <span class="text-xs text-slate-400 font-medium">Event #{{ $event->id }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Event Overview -->
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
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Description</h2>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                            {{ $event->description }}
                        </p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Admission: <strong class="text-slate-800">Free</strong></span>
                        <span>{{ $event->registrations_count }} {{ Str::plural('attendee', $event->registrations_count) }}</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Registration Form -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-xl p-6 sm:p-8 border border-slate-200/90 shadow-xs">
                    
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Reserve Your Seat</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Please provide your details below to confirm attendance.</p>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('registrations.store', $event) }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $event->id }}">

                        <!-- Selected Event -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                                Selected Event
                            </label>
                            <input type="text" 
                                   value="{{ $event->event_name }} ({{ $event->event_date->format('M d, Y') }})" 
                                   readonly 
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 text-sm font-medium cursor-not-allowed">
                        </div>

                        <!-- User Name -->
                        <div>
                            <label for="user_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="user_name" 
                                   id="user_name" 
                                   value="{{ old('user_name') }}" 
                                   required 
                                   placeholder="Jane Doe" 
                                   class="w-full px-3.5 py-2.5 rounded-lg border {{ $errors->has('user_name') ? 'border-red-300 bg-red-50/20' : 'border-slate-300 bg-white' }} text-slate-900 placeholder-slate-400 text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors shadow-xs">
                            @error('user_name')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   placeholder="jane@example.com" 
                                   class="w-full px-3.5 py-2.5 rounded-lg border {{ $errors->has('email') ? 'border-red-300 bg-red-50/20' : 'border-slate-300 bg-white' }} text-slate-900 placeholder-slate-400 text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors shadow-xs">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full py-2.5 px-4 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                <span>Confirm Registration</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
