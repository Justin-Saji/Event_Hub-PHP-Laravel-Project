<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin Dashboard — EventHub' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 flex overflow-hidden">

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/60 z-40 hidden md:hidden transition-opacity"></div>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" 
           class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-200 flex flex-col justify-between transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shrink-0 border-r border-slate-800 shadow-xl md:shadow-none">
        
        <div>
            <!-- Sidebar Header / Logo -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800 bg-slate-950/40">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group focus:outline-hidden">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-black tracking-tight text-white group-hover:text-blue-400 transition-colors">
                            Event<span class="text-blue-500">Hub</span>
                        </span>
                        <span class="block text-[9px] uppercase tracking-widest font-bold text-slate-400 -mt-1">Admin Panel</span>
                    </div>
                </a>

                <!-- Close Sidebar on Mobile -->
                <button id="close-sidebar-btn" type="button" class="md:hidden text-slate-400 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5">
                <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Main Menu
                </div>

                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- All Registrations -->
                <a href="{{ route('admin.registrations.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.registrations.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span>All Registrations</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer / Logout -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/30 space-y-3">
            <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-slate-800/60 border border-slate-700/60">
                <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name ?? 'Administrator' }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
            </div>

            <!-- Logout Button Form -->
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-red-400 hover:text-red-300 hover:bg-red-500/10 border border-red-500/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>

    </aside>

    <!-- Main Content Area Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Top Navigation Bar -->
        <header class="h-20 bg-white border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between shrink-0 shadow-xs">
            <div class="flex items-center gap-3">
                <!-- Mobile Sidebar Toggle -->
                <button id="open-sidebar-btn" type="button" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                        @yield('page_title', 'Dashboard')
                    </h1>
                    <p class="text-xs text-slate-500 hidden sm:block">EventHub Administration & Attendee Records</p>
                </div>
            </div>

        </header>

        <!-- Scrollable Page Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            
            <!-- Global Flash Messages in Admin Panel -->
            @if(session('success'))
                <div id="flash-admin-success" class="mb-6 flex items-start justify-between p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 shadow-sm" role="alert">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-emerald-900">{{ session('success') }}</p>
                    </div>
                    <button type="button" onclick="document.getElementById('flash-admin-success').remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div id="flash-admin-error" class="mb-6 flex items-start justify-between p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 shadow-sm" role="alert">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-red-900">{{ session('error') }}</p>
                    </div>
                    <button type="button" onclick="document.getElementById('flash-admin-error').remove()" class="text-red-500 hover:text-red-700 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Content Slot -->
            @yield('content')

        </main>
    </div>

    <!-- Mobile Drawer Interactivity Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const openBtn = document.getElementById('open-sidebar-btn');
            const closeBtn = document.getElementById('close-sidebar-btn');

            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            }

            if (openBtn) openBtn.addEventListener('click', toggleSidebar);
            if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
            if (backdrop) backdrop.addEventListener('click', toggleSidebar);

            // Auto dismiss success toast after 5s
            const successToast = document.getElementById('flash-admin-success');
            if (successToast) {
                setTimeout(() => {
                    successToast.style.opacity = '0';
                    setTimeout(() => successToast.remove(), 300);
                }, 5000);
            }
        });
    </script>
</body>
</html>

