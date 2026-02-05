<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Task Tracker') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-white to-indigo-50/30">
        <div class="min-h-screen flex flex-col">
            {{-- Nav --}}
            <nav class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">TaskTracker</span>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-sm shadow-indigo-200">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-sm shadow-indigo-200">
                            Get Started
                        </a>
                    @endauth
                </div>
            </nav>

            {{-- Hero --}}
            <div class="flex-1 flex items-center">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20">
                    <div class="text-center max-w-3xl mx-auto">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-full text-sm font-medium mb-6">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Built with Laravel 12 & Livewire 4
                        </div>

                        <h1 class="text-4xl sm:text-6xl font-extrabold text-gray-900 tracking-tight leading-tight">
                            Track Your Tasks
                            <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Effortlessly</span>
                        </h1>

                        <p class="text-lg text-gray-500 mt-6 max-w-xl mx-auto leading-relaxed">
                            Stay organized with daily task tracking, calendar navigation, progress insights, and detailed analytics. Built for productivity.
                        </p>

                        <div class="flex items-center justify-center gap-4 mt-8">
                            @auth
                                <a href="{{ route('dashboard') }}" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-lg shadow-indigo-200 text-sm">
                                    Go to Dashboard
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-lg shadow-indigo-200 text-sm">
                                    Start Tracking
                                </a>
                                <a href="{{ route('login') }}" class="px-8 py-3.5 bg-white text-gray-700 font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition text-sm">
                                    Sign In
                                </a>
                            @endauth
                        </div>
                    </div>

                    {{-- Feature Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-20">
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2">Calendar Navigation</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">Browse tasks by day with an intuitive calendar bar. Navigate between dates and see completion stats at a glance.</p>
                        </div>
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2">Analytics & Heatmaps</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">Track your productivity with heatmaps, trend charts, priority distribution, and category breakdowns.</p>
                        </div>
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2">Role-Based Access</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">Secure authentication with Breeze and role-based permissions using Spatie. Admin and user roles included.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <footer class="py-6 text-center text-sm text-gray-400">
                <p>TaskTracker &copy; {{ date('Y') }} &middot; Built with Laravel {{ app()->version() }} & Livewire</p>
            </footer>
        </div>
    </body>
</html>
