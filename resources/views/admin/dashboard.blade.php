@extends('layouts.admin')

@section('page_title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">

    <!-- Header & Welcome -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Overview</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Real-time statistics and recent event attendee activity</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.registrations.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span>Manage Registrations</span>
            </a>
        </div>
    </div>

    <!-- Summary Cards (Calculated Dynamically from MySQL) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        
        <!-- 1. Total Registrations -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Registrations</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-black text-slate-900 mt-3 tracking-tight">{{ number_format($totalRegistrations) }}</p>
            <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                <span>Confirmed participants</span>
                <a href="{{ route('admin.registrations.index') }}" class="font-semibold text-blue-600 hover:text-blue-800">View all &rarr;</a>
            </div>
        </div>

        <!-- 2. Total Events -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Events</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-black text-slate-900 mt-3 tracking-tight">{{ number_format($totalEvents) }}</p>
            <div class="mt-2 text-xs text-slate-400">
                <span>Scheduled in database</span>
            </div>
        </div>

        <!-- 3. Today's Registrations -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Today's Registrations</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-black text-slate-900 mt-3 tracking-tight">{{ number_format($todayRegistrations) }}</p>
            <div class="mt-2 text-xs text-slate-400">
                <span>Registered today ({{ date('M j, Y') }})</span>
            </div>
        </div>

    </div>

    <!-- Two-Column Section: Recent Registrations & Upcoming Events -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Recent Registrations Table -->
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Recent Registrations</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Latest attendees signed up via the website</p>
                </div>
                <a href="{{ route('admin.registrations.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                    View Table &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-[10px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-5">ID</th>
                            <th class="py-3 px-5">Participant</th>
                            <th class="py-3 px-5">Event</th>
                            <th class="py-3 px-5 text-right">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentRegistrations as $reg)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-5 font-mono text-slate-500">#{{ $reg->id }}</td>
                                <td class="py-3 px-5">
                                    <div class="font-bold text-slate-900">{{ $reg->user_name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $reg->email }}</div>
                                </td>
                                <td class="py-3 px-5 font-medium text-slate-800">
                                    {{ $reg->event->event_name ?? '—' }}
                                </td>
                                <td class="py-3 px-5 text-right text-slate-400">
                                    {{ $reg->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                                    No registrations recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Upcoming Events List -->
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Upcoming Events</h3>
                <p class="text-xs text-slate-500 mt-0.5">Events open for registration</p>
            </div>

            <div class="p-4 space-y-3">
                @forelse($upcomingEvents as $evt)
                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-between gap-3">
                        <div class="space-y-0.5 overflow-hidden">
                            <h4 class="text-xs font-bold text-slate-900 truncate">{{ $evt->event_name }}</h4>
                            <p class="text-[11px] text-slate-500">
                                {{ $evt->event_date->format('M d, Y') }} ({{ $evt->event_date->diffForHumans() }})
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-white text-slate-700 border border-slate-200 shrink-0">
                            {{ $evt->registrations_count }} registered
                        </span>
                    </div>
                @empty
                    <p class="text-center py-6 text-xs text-slate-400">No upcoming events scheduled.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
