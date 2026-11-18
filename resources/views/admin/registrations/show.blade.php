@extends('layouts.admin')

@section('page_title', 'Registration Details #' . $registration->id)

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    <!-- Top Navigation Row -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.registrations.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Back to All Registrations</span>
        </a>

        <!-- Delete Action -->
        <form action="{{ route('admin.registrations.destroy', $registration) }}" 
              method="POST" 
              onsubmit="return confirm('Delete registration #{{ $registration->id }} for {{ addslashes($registration->user_name) }}?');">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-white border border-red-200 hover:bg-red-50 transition-colors shadow-xs cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                <span>Delete</span>
            </button>
        </form>
    </div>

    <!-- Registration Detail Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-xs font-mono font-bold text-blue-600">#{{ $registration->id }}</span>
                <h2 class="text-xl font-bold text-slate-900 mt-0.5">
                    {{ $registration->user_name }}
                </h2>
                <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $registration->email }}</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                Active Attendee
            </span>
        </div>

        <!-- Details Grid -->
        <div class="p-6 space-y-6">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Registration ID</span>
                    <p class="text-base font-bold text-slate-900 font-mono mt-0.5">#{{ $registration->id }}</p>
                </div>

                <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Participant Name</span>
                    <p class="text-base font-bold text-slate-900 mt-0.5">{{ $registration->user_name }}</p>
                </div>

                <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Email Address</span>
                    <p class="text-base font-semibold text-slate-900 font-mono mt-0.5">{{ $registration->email }}</p>
                </div>

                <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Registration Date</span>
                    <p class="text-base font-semibold text-slate-900 mt-0.5">
                        {{ $registration->created_at->format('M d, Y — h:i A') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $registration->created_at->diffForHumans() }}</p>
                </div>

            </div>

            <!-- Associated Event -->
            <div class="pt-5 border-t border-slate-100">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-3">Event Information</span>

                @if($registration->event)
                    <div class="p-5 rounded-lg bg-slate-50 border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-slate-900">{{ $registration->event->event_name }}</h3>
                            <span class="text-xs text-slate-500 font-medium">Event #{{ $registration->event->id }}</span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ $registration->event->event_date->format('l, F j, Y') }} ({{ $registration->event->event_date->diffForHumans() }})</span>
                        </p>
                        <p class="text-xs text-slate-600 leading-relaxed pt-2 border-t border-slate-200/60 whitespace-pre-line">
                            {{ $registration->event->description }}
                        </p>
                    </div>
                @else
                    <p class="text-xs text-slate-400">Associated event not found.</p>
                @endif
            </div>

        </div>

        <!-- Footer -->
        <div class="p-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <a href="{{ route('admin.registrations.index') }}" class="font-medium text-slate-700 hover:text-slate-900">
                &larr; Back to table
            </a>
            <span class="font-mono text-slate-400">Created: {{ $registration->created_at->toFormattedDateString() }}</span>
        </div>

    </div>

</div>
@endsection
