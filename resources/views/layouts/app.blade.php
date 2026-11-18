<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'EventHub — Discover & Register for Premier Events' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-white text-slate-800 font-sans antialiased selection:bg-blue-600 selection:text-white flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Brand Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group focus:outline-hidden">
                    <div class="w-9 h-9 rounded-lg bg-blue-600 group-hover:bg-blue-700 transition-colors flex items-center justify-center text-white shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-slate-900 group-hover:text-blue-600 transition-colors">
                        Event<span class="text-blue-600">Hub</span>
                    </span>
                </a>

                <!-- Desktop Navigation Links & Auth Actions (Top Right Corner) -->
                <div class="hidden md:flex items-center gap-1 sm:gap-2">
                    <nav class="flex items-center gap-1 sm:gap-2">
                        <a href="{{ route('home') }}" 
                           class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 transition-colors {{ request()->routeIs('home') ? 'text-slate-900 font-semibold' : '' }}">
                            Home
                        </a>
                        <a href="{{ route('home') }}#events" 
                           class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 transition-colors">
                            Events
                        </a>
                        @auth
                            @if(Auth::user()->is_admin)
                                <!-- Administrator: Admin Dashboard Link -->
                                <a href="{{ route('admin.dashboard') }}" 
                                   class="px-3.5 py-2 rounded-lg text-sm font-semibold text-slate-900 hover:text-blue-600 hover:bg-slate-100/70 transition-colors flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span>Admin Dashboard</span>
                                </a>
                            @else
                                <!-- Normal User: Welcome Badge & My Account -->
                                <span class="px-3 py-1.5 text-xs sm:text-sm text-slate-600 font-medium flex items-center gap-1.5 bg-slate-100/80 rounded-lg">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Welcome, <strong class="text-slate-900 font-semibold">{{ Auth::user()->name }}</strong>
                                </span>
                                <a href="{{ route('profile') }}" 
                                   class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 transition-colors {{ request()->routeIs('profile') ? 'text-blue-600 font-semibold bg-blue-50/50' : '' }}">
                                    My Account
                                </a>
                            @endif
                        @endauth
                    </nav>

                    <!-- Subtle Divider -->
                    <div class="h-5 w-px bg-slate-200 mx-1"></div>

                    <!-- Auth Action Buttons (Right) -->
                    <div class="flex items-center gap-2">
                        @guest
                            <!-- Login & Sign Up for visitors -->
                            <a href="{{ route('login') }}" 
                               class="px-3.5 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-slate-100 transition-colors">
                                Login
                            </a>
                            <a href="{{ route('register') }}" 
                               class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                                Sign Up
                            </a>
                        @else
                            <!-- Logout Form for Authenticated Users (Admin & Normal User) -->
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs sm:text-sm font-semibold text-slate-600 hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span>Logout</span>
                                </button>
                            </form>
                        @endguest
                    </div>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex items-center md:hidden">
                    <button id="mobile-menu-btn" type="button" 
                            class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-hidden"
                            aria-expanded="false" aria-label="Toggle navigation">
                        <svg id="hamburger-icon" class="w-6 h-6 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div id="mobile-menu" class="hidden md:hidden border-t border-slate-100 py-3 space-y-1 bg-white">
                @auth
                    @if(Auth::user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" 
                           class="mobile-nav-link block px-3 py-2 rounded-lg text-sm font-semibold text-slate-900 hover:text-blue-600 hover:bg-slate-50">
                            Admin Dashboard
                        </a>
                        <div class="pt-2 px-3 border-t border-slate-100 mt-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 text-center cursor-pointer">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="px-3 py-2 text-xs text-slate-500 font-medium">
                            Welcome, <strong class="text-slate-900 font-bold">{{ Auth::user()->name }}</strong>
                        </div>
                        <a href="{{ route('home') }}" 
                           class="mobile-nav-link block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-slate-50">
                            Home
                        </a>
                        <a href="{{ route('home') }}#events" 
                           class="mobile-nav-link block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-slate-50">
                            Events
                        </a>
                        <a href="{{ route('profile') }}" 
                           class="mobile-nav-link block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-slate-50">
                            My Account
                        </a>
                        <div class="pt-2 px-3 border-t border-slate-100 mt-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 text-center cursor-pointer">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @endif
                @else
                    <a href="{{ route('home') }}" 
                       class="mobile-nav-link block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-slate-50">
                        Home
                    </a>
                    <a href="{{ route('home') }}#events" 
                       class="mobile-nav-link block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-slate-50">
                        Events
                    </a>
                    <div class="pt-3 px-3 space-y-2 border-t border-slate-100 mt-2">
                        <a href="{{ route('login') }}" 
                           class="mobile-nav-link flex items-center justify-center w-full px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 text-center">
                            Login
                        </a>
                        <a href="{{ route('register') }}" 
                           class="mobile-nav-link flex items-center justify-center w-full px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 text-center">
                            Sign Up
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Global Notification Banner Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4 empty:mt-0">
        @if(session('success'))
            <div id="flash-success" class="flex items-center justify-between p-3.5 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm shadow-xs" role="alert">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="document.getElementById('flash-success').remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div id="flash-error" class="flex items-center justify-between p-3.5 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-900 text-sm shadow-xs" role="alert">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="document.getElementById('flash-error').remove()" class="text-red-500 hover:text-red-700 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <!-- Main Content Slot -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Clean, Minimal SaaS Footer -->
    <footer class="bg-white border-t border-slate-200 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                <!-- Brand & Tagline -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <span class="text-base font-bold text-slate-900 tracking-tight">EventHub</span>
                    <span class="text-xs text-slate-400 border-l border-slate-200 pl-3">Event Registration Platform</span>
                </div>

                <!-- Footer Nav Links -->
                <nav class="flex items-center gap-6 text-xs sm:text-sm font-medium text-slate-600">
                    <a href="{{ route('home') }}" class="hover:text-slate-900 transition-colors">Home</a>
                    <a href="{{ route('home') }}#events" class="hover:text-slate-900 transition-colors">Events</a>
                    <a href="{{ route('home') }}#about" class="hover:text-slate-900 transition-colors">About</a>
                </nav>

                <!-- Copyright -->
                <div class="text-xs text-slate-400">
                    &copy; {{ date('Y') }} EventHub. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <!-- Vanilla JavaScript for interactivity -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const hamburgerIcon = document.getElementById('hamburger-icon');
            const closeIcon = document.getElementById('close-icon');

            if (menuBtn && mobileMenu) {
                menuBtn.addEventListener('click', () => {
                    const isExpanded = menuBtn.getAttribute('aria-expanded') === 'true';
                    menuBtn.setAttribute('aria-expanded', !isExpanded);
                    mobileMenu.classList.toggle('hidden');
                    hamburgerIcon.classList.toggle('hidden');
                    closeIcon.classList.toggle('hidden');
                });

                const mobileLinks = mobileMenu.querySelectorAll('.mobile-nav-link');
                mobileLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                        hamburgerIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                        menuBtn.setAttribute('aria-expanded', 'false');
                    });
                });
            }

            const flashSuccess = document.getElementById('flash-success');
            if (flashSuccess) {
                setTimeout(() => {
                    flashSuccess.style.opacity = '0';
                    setTimeout(() => flashSuccess.remove(), 250);
                }, 5000);
            }
        });
    </script>
</body>
</html>
