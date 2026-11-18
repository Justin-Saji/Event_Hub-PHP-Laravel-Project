@extends('layouts.app')

@section('content')
    <!-- Hero Section -->
    <section class="bg-white border-b border-slate-200/80 pt-16 pb-20 md:pt-24 md:pb-28">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            
            <!-- Main Heading -->
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-black text-slate-900 tracking-tight leading-tight md:leading-tight">
                Discover Events That <span class="text-blue-600">Inspire You</span>
            </h1>

            <!-- Subtitle -->
            <p class="mt-5 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                Connect with industry pioneers, master new skills, and reserve your place at premier summits and interactive workshops.
            </p>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                <a href="#events" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                    <span>Explore Events</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </a>
                <a href="#about" 
                   class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-lg text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition-colors shadow-xs">
                    About EventHub
                </a>
            </div>

            <!-- Stats Bar -->
            <div class="mt-14 pt-8 border-t border-slate-100 max-w-3xl mx-auto grid grid-cols-3 gap-4 sm:gap-8">
                <div class="text-center">
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $upcomingEvents->count() }}</p>
                    <p class="text-xs font-medium text-slate-500 mt-0.5">Upcoming Events</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl sm:text-3xl font-extrabold text-blue-600">100%</p>
                    <p class="text-xs font-medium text-slate-500 mt-0.5">Free Registration</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900">Instant</p>
                    <p class="text-xs font-medium text-slate-500 mt-0.5">Confirmation</p>
                </div>
            </div>

        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section id="events" class="py-16 md:py-24 bg-slate-50/70 border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header & Search Toolbar -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Upcoming Events
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Choose an event below and reserve your attendance in seconds.
                    </p>
                </div>

                <!-- Instant Search Box -->
                <div class="w-full md:w-72">
                    <div class="relative">
                        <input type="text" 
                               id="event-search" 
                               placeholder="Search events..." 
                               class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 placeholder-slate-400 text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Events Grid -->
            @if($upcomingEvents->isNotEmpty())
                <div id="events-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($upcomingEvents as $event)
                        <article class="event-card group bg-white rounded-xl border border-slate-200/90 shadow-xs hover:border-slate-300 hover:shadow-sm transition-all duration-150 flex flex-col justify-between overflow-hidden"
                                 data-name="{{ strtolower($event->event_name) }}" 
                                 data-description="{{ strtolower($event->description) }}">
                            
                            <div class="p-6">
                                <!-- Date & Relative Status -->
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $event->event_date->format('M d, Y') }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-medium">
                                        {{ $event->event_date->diffForHumans() }}
                                    </span>
                                </div>

                                <!-- Event Title -->
                                <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors leading-snug">
                                    {{ $event->event_name }}
                                </h3>

                                <!-- Event Description -->
                                <p class="mt-2 text-sm text-slate-600 leading-relaxed line-clamp-3">
                                    {{ $event->description }}
                                </p>
                            </div>

                            <!-- Card Footer -->
                            <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-4 mt-auto">
                                <span class="text-xs text-slate-500 font-medium">
                                    {{ $event->registrations_count }} {{ Str::plural('attendee', $event->registrations_count) }}
                                </span>

                                <a href="{{ route('registrations.create', $event) }}" 
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-xs">
                                    <span>Register Now</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>

                        </article>
                    @endforeach
                </div>

                <!-- Empty Search Match State -->
                <div id="no-search-results" class="hidden text-center py-12 bg-white rounded-xl border border-slate-200 mt-4">
                    <p class="text-sm font-semibold text-slate-800">No events matched your search.</p>
                    <p class="text-xs text-slate-500 mt-1">Try another keyword or clear the search input.</p>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16 px-4 bg-white rounded-xl border border-slate-200">
                    <div class="w-12 h-12 mx-auto rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">No Upcoming Events Available</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        New conferences and workshops are being scheduled. Please check back soon.
                    </p>
                </div>
            @endif

        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 md:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    About EventHub
                </h2>
                <p class="mt-2 text-sm text-slate-600">
                    An intuitive event portal connecting professionals with high-impact learning opportunities.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-xl border border-slate-200/90 bg-white shadow-xs">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-1.5">Curated Selection</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Every event listed on EventHub is vetted for actionable content, experienced speaker panels, and real-world relevance.
                    </p>
                </div>

                <div class="p-6 rounded-xl border border-slate-200/90 bg-white shadow-xs">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-1.5">Frictionless Registration</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Quick one-click reservation without lengthy forms or verification delays. Confirm your attendance instantly.
                    </p>
                </div>

                <div class="p-6 rounded-xl border border-slate-200/90 bg-white shadow-xs">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-1.5">Professional Network</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Connect with peers across software development, system engineering, and modern technology management.
                    </p>
                </div>
            </div>

            <!-- Minimal CTA Banner -->
            <div class="mt-12 p-8 rounded-2xl bg-slate-900 text-white flex flex-col sm:flex-row items-center justify-between gap-6">
                <div>
                    <h3 class="text-xl font-bold">Ready to participate in an upcoming event?</h3>
                    <p class="text-slate-300 text-xs sm:text-sm mt-0.5">Explore our scheduled sessions and reserve your attendance today.</p>
                </div>
                <a href="#events" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-xs sm:text-sm font-semibold bg-white text-slate-900 hover:bg-slate-100 transition-colors shrink-0">
                    <span>Browse Schedule</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                </a>
            </div>

        </div>
    </section>

    <!-- Client-side Search Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('event-search');
            const eventCards = document.querySelectorAll('.event-card');
            const noResults = document.getElementById('no-search-results');

            if (searchInput && eventCards.length > 0) {
                searchInput.addEventListener('input', (e) => {
                    const query = e.target.value.toLowerCase().trim();
                    let visibleCount = 0;

                    eventCards.forEach(card => {
                        const name = card.getAttribute('data-name') || '';
                        const desc = card.getAttribute('data-description') || '';

                        if (name.includes(query) || desc.includes(query)) {
                            card.classList.remove('hidden');
                            visibleCount++;
                        } else {
                            card.classList.add('hidden');
                        }
                    });

                    if (noResults) {
                        if (visibleCount === 0) {
                            noResults.classList.remove('hidden');
                        } else {
                            noResults.classList.add('hidden');
                        }
                    }
                });
            }
        });
    </script>
@endsection
