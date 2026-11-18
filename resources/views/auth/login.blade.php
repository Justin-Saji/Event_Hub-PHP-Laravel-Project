@extends('layouts.app')

@section('content')
<div class="py-12 md:py-16 bg-slate-50 min-h-[calc(100vh-160px)] flex items-center justify-center">
    <div class="w-full max-w-md mx-auto px-4 sm:px-6">

        <!-- Card Container -->
        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/90 shadow-xs">
            
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Sign In to EventHub</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Access your event registrations and profile</p>
            </div>

            <!-- Server-Side Error Alert -->
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-lg bg-red-50 border border-red-200 text-red-900 text-xs shadow-xs" role="alert">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <p class="font-medium text-red-800">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}" 
                           required 
                           autocomplete="email" 
                           autofocus
                           placeholder="you@example.com" 
                           class="w-full px-3.5 py-2.5 rounded-lg border {{ $errors->has('email') ? 'border-red-300 bg-red-50/20' : 'border-slate-300 bg-white' }} text-slate-900 placeholder-slate-400 text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors shadow-xs">
                </div>

                <!-- Password with Visibility Toggle -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Password <span class="text-red-500">*</span>
                        </label>
                    </div>
                    <div class="relative">
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required 
                               autocomplete="current-password"
                               placeholder="••••••••" 
                               class="w-full pl-3.5 pr-10 py-2.5 rounded-lg border {{ $errors->has('password') ? 'border-red-300 bg-red-50/20' : 'border-slate-300 bg-white' }} text-slate-900 placeholder-slate-400 text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors shadow-xs">
                        
                        <button type="button" 
                                onclick="toggleVisibility('password', this)" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden"
                                aria-label="Toggle password visibility">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 013.25-4.412M6.18 6.18A9.97 9.97 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-2.072 3.125m-3.87-3.87A3 3 0 019.25 8.75m6 6a3 3 0 01-4.243-4.243M3 3l18 18"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded-sm border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                        <span>Sign In</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Footer: Link to Signup -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                        Create an account
                    </a>
                </p>
            </div>

        </div>

    </div>
</div>

<!-- Password Visibility Script -->
<script>
    function toggleVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const openIcon = btn.querySelector('.eye-open');
        const closedIcon = btn.querySelector('.eye-closed');
        
        if (input.type === 'password') {
            input.type = 'text';
            openIcon.classList.add('hidden');
            closedIcon.classList.remove('hidden');
        } else {
            input.type = 'password';
            openIcon.classList.remove('hidden');
            closedIcon.classList.add('hidden');
        }
    }
</script>
@endsection
