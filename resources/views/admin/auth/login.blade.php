<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Sign In — EventHub</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md">
        
        <!-- Header Brand -->
        <div class="text-center mb-8">
            <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center mx-auto mb-3 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Event<span class="text-blue-600">Hub</span> Admin</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Sign in with administrator credentials</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/90 shadow-sm">
            
            <!-- Generic Invalid Credentials Error Alert -->
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-lg bg-red-50 border border-red-200 text-red-900 text-xs shadow-xs" role="alert">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="font-semibold text-red-800">
                            @foreach ($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </p>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4">
                @csrf

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
                           autocomplete="email" 
                           autofocus
                           placeholder="admin@example.com" 
                           class="w-full px-3.5 py-2.5 rounded-lg border {{ $errors->has('email') ? 'border-red-300 bg-red-50/20' : 'border-slate-300 bg-white' }} text-slate-900 placeholder-slate-400 text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors shadow-xs">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required 
                           autocomplete="current-password"
                           placeholder="••••••••" 
                           class="w-full px-3.5 py-2.5 rounded-lg border {{ $errors->has('password') ? 'border-red-300 bg-red-50/20' : 'border-slate-300 bg-white' }} text-slate-900 placeholder-slate-400 text-sm focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors shadow-xs">
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

        </div>

        <!-- Back to Public Site -->
        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Return to Public Website</span>
            </a>
        </div>

    </div>

</body>
</html>
