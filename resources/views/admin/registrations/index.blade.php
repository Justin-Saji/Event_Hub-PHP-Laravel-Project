@extends('layouts.admin')

@section('page_title', 'All Registrations')

@section('content')
<div class="space-y-5">

    <!-- Header & Total Count -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Participant Registrations</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">View and manage all attendees registered across events</p>
        </div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-600 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-xs shrink-0">
            <span>Total Attendees:</span>
            <span class="text-slate-900 font-bold font-mono">{{ $registrations->total() }}</span>
        </div>
    </div>

    <!-- Filter & Search Toolbar Card -->
    <div class="p-4 bg-white rounded-xl border border-slate-200/90 shadow-xs">
        <form method="GET" action="{{ route('admin.registrations.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <!-- Search -->
            <div class="sm:col-span-6 relative">
                <input type="text" 
                       name="search" 
                       value="{{ request('search', $search) }}" 
                       placeholder="Search by participant name, email, or ID..." 
                       class="w-full pl-9 pr-3.5 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 placeholder-slate-400 text-xs sm:text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors shadow-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Filter by Event -->
            <div class="sm:col-span-4 relative">
                <select name="event_id" 
                        onchange="this.form.submit()"
                        class="w-full pl-3 pr-8 py-2 rounded-lg border border-slate-300 bg-white text-slate-800 text-xs sm:text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors appearance-none cursor-pointer shadow-xs">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ (string)request('event_id', $eventId) === (string)$event->id ? 'selected' : '' }}>
                            {{ $event->event_name }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" 
                        class="w-full py-2 px-3 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>Filter</span>
                </button>

                @if(request()->filled('search') || request()->filled('event_id'))
                    <a href="{{ route('admin.registrations.index') }}" 
                       class="py-2 px-2.5 rounded-lg text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors"
                       title="Clear filters">
                        Clear
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Registrations Table Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50/90 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-5">Registration ID</th>
                        <th class="py-3.5 px-5">Name</th>
                        <th class="py-3.5 px-5">Email</th>
                        <th class="py-3.5 px-5">Event Name</th>
                        <th class="py-3.5 px-5">Date Registered</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($registrations as $reg)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Registration ID -->
                            <td class="py-3.5 px-5 font-mono text-slate-500 font-medium">
                                #{{ $reg->id }}
                            </td>

                            <!-- Name -->
                            <td class="py-3.5 px-5 font-bold text-slate-900">
                                {{ $reg->user_name }}
                            </td>

                            <!-- Email -->
                            <td class="py-3.5 px-5 text-slate-600 font-mono text-xs">
                                {{ $reg->email }}
                            </td>

                            <!-- Event Name -->
                            <td class="py-3.5 px-5 font-medium text-slate-900">
                                {{ $reg->event->event_name ?? '—' }}
                            </td>

                            <!-- Date Registered -->
                            <td class="py-3.5 px-5 text-xs text-slate-500">
                                <span>{{ $reg->created_at->format('M d, Y') }}</span>
                                <span class="text-slate-400 block text-[11px]">{{ $reg->created_at->format('h:i A') }}</span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.registrations.show', $reg) }}" 
                                       class="px-2.5 py-1.5 rounded-md text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-xs">
                                        View
                                    </a>

                                    <form action="{{ route('admin.registrations.destroy', $reg) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Delete registration #{{ $reg->id }} for {{ addslashes($reg->user_name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-2.5 py-1.5 rounded-md text-xs font-semibold text-red-600 bg-white border border-red-200 hover:bg-red-50 transition-colors shadow-xs cursor-pointer">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-10 h-10 mx-auto rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800">No Registrations Found</p>
                                    <p class="text-xs text-slate-400">
                                        @if(request()->filled('search') || request()->filled('event_id'))
                                            No attendees match your search query or event filter.
                                        @else
                                            No registrations currently recorded in the database.
                                        @endif
                                    </p>
                                    @if(request()->filled('search') || request()->filled('event_id'))
                                        <div class="pt-1">
                                            <a href="{{ route('admin.registrations.index') }}" 
                                               class="inline-block text-xs font-semibold text-blue-600 hover:text-blue-800">
                                                Clear filters &rarr;
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($registrations->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/70">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
