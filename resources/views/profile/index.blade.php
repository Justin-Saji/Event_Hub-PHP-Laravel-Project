@extends('layouts.app')

@section('content')
<div class="py-10 md:py-16 bg-slate-50 min-h-[calc(100vh-160px)]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Top Account Summary Card -->
        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/90 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-blue-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-md shadow-blue-600/20 shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $user->name }}</h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $user->is_admin ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700' }}">
                            {{ $user->is_admin ? 'Administrator' : 'Member' }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-mono">{{ $user->email }}</p>
                    <p class="text-xs text-slate-400 mt-1">
                        Member since {{ $user->created_at ? $user->created_at->format('M d, Y') : 'Recently' }}
                    </p>
                </div>
            </div>

            <!-- Quick Action -->
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}#events" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Browse New Events</span>
                </a>
            </div>
        </div>

        <!-- Registered Events Section -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">My Event Registrations</h2>
                    <p class="text-xs text-slate-500 mt-0.5">All conferences and workshops you have registered for</p>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-white px-3 py-1 rounded-lg border border-slate-200 shadow-2xs">
                    {{ $registrations->count() }} {{ Str::plural('Pass', $registrations->count()) }}
                </span>
            </div>

            @if($registrations->isNotEmpty())
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-50/90 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="py-3.5 px-5">Pass ID</th>
                                    <th class="py-3.5 px-5">Event</th>
                                    <th class="py-3.5 px-5">Event Date</th>
                                    <th class="py-3.5 px-5">Registered On</th>
                                    <th class="py-3.5 px-5 text-right">Confirmation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($registrations as $reg)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3.5 px-5 font-mono text-blue-700 font-semibold">
                                            #REG-{{ str_pad($reg->id, 5, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="py-3.5 px-5">
                                            <p class="font-bold text-slate-900">{{ $reg->event?->event_name ?? 'Event #' . $reg->event_id }}</p>
                                        </td>
                                        <td class="py-3.5 px-5 text-slate-600">
                                            @if($reg->event)
                                                <span>{{ $reg->event->event_date->format('M d, Y') }}</span>
                                                <span class="text-[11px] text-slate-400 block">{{ $reg->event->event_date->diffForHumans() }}</span>
                                            @else
                                                <span class="text-slate-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-5 text-slate-500 text-xs">
                                            {{ $reg->created_at->format('M d, Y — h:i A') }}
                                        </td>
                                        <td class="py-3.5 px-5 text-right">
                                            <a href="{{ route('registrations.success', $reg) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors">
                                                <span>View Pass</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl p-12 text-center border border-slate-200/90 shadow-xs">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">No Event Registrations Yet</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        You have not registered for any upcoming events. Explore our schedule to reserve your seat.
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('home') }}#events" 
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                            <span>Browse Upcoming Events</span>
                        </a>
                    </div>
                </div>
            @endif

        </div>

    </div>
</div>
@endsection

