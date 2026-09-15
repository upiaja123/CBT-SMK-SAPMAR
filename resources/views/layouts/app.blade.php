<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-gray-50 font-sans antialiased">

    <div class="flex h-full min-h-screen" x-data="{ sidebarOpen: false }">

        {{-- ── Sidebar Overlay (mobile) ──────────────────────────────────── --}}
        <div x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/60 z-20 lg:hidden"
            @click="sidebarOpen = false"
            style="display: none;">
        </div>

        {{-- ── Sidebar ───────────────────────────────────────────────────── --}}
        <aside id="sidebar"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed top-0 left-0 z-30 h-full w-64 bg-gradient-to-b from-sapta-900 to-sapta-800 shadow-2xl transform transition-transform duration-200 ease-in-out lg:relative lg:translate-x-0 flex flex-col flex-shrink-0">

            {{-- Logo --}}
            <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
                <div class="flex-shrink-0 flex items-center justify-center">
                    <img src="{{ asset('images/logo-sapmar.png') }}" alt="Logo Sapta Marga" class="w-14 h-14 object-contain drop-shadow-md">
                </div>
                <div>
                    <span class="text-white font-bold text-lg leading-tight">SAPTA CBT</span>
                    <p class="text-sapta-300 text-xs">SMK Sapta Marga</p>
                </div>
            </div>

            {{-- User info --}}
            <div class="px-4 py-3 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                        class="w-9 h-9 rounded-full object-cover flex-shrink-0 ring-2 ring-white/20">
                    <div class="min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-sapta-accent/20 text-sapta-accent">
                            {{ auth()->user()->primary_role_label }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-1">
                @include('layouts.partials.sidebar-nav')
            </nav>

            {{-- Logout --}}
            <div class="px-3 py-3 border-t border-white/10">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sapta-200 hover:bg-white/10 hover:text-white transition text-sm group">
                        <svg class="w-5 h-5 flex-shrink-0 group-hover:text-red-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        {{-- ── Main Content ──────────────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Top Bar --}}
            <header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-3 flex items-center gap-4 flex-shrink-0 shadow-sm">
                {{-- Mobile menu toggle --}}
                <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition"
                    aria-label="Toggle sidebar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Page title & Header Slot --}}
                <div class="flex-1 min-w-0">
                    @isset($header)
                        {!! $header !!}
                    @endisset
                    @isset($title)
                        <h1 class="text-lg font-semibold text-gray-800 truncate">{{ $title }}</h1>
                    @endisset
                    @isset($breadcrumbs)
                        <nav class="flex items-center gap-1 text-xs text-gray-400 mt-0.5">
                            @foreach ($breadcrumbs as $label => $url)
                                @if (!$loop->last)
                                    <a href="{{ $url }}" class="hover:text-sapta-600 transition">{{ $label }}</a>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                @else
                                    <span class="text-gray-600 font-medium">{{ $label }}</span>
                                @endif
                            @endforeach
                        </nav>
                    @endisset
                </div>

                {{-- Actions slot --}}
                @isset($headerActions)
                    <div class="flex items-center gap-2">
                        {{ $headerActions }}
                    </div>
                @endisset
            </header>

            {{-- Flash Messages --}}
            @if (session('success') || session('error') || session('warning'))
                <div class="px-4 lg:px-6 pt-4">
                    @if (session('success'))
                        <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="flex items-center gap-3 p-4 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-xl text-sm">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            {{ session('warning') }}
                        </div>
                    @endif
                </div>
            @endif

            @if ($errors->any())
                <div class="px-4 lg:px-6 pt-4">
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada isian form:</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <p class="mt-2 text-xs font-semibold text-red-600">Silakan buka kembali form untuk memperbaiki data.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto px-4 lg:px-6 py-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
