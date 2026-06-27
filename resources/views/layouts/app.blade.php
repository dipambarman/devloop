<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DevLoop') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-primary-text bg-background selection:bg-primary selection:text-white overflow-hidden">
        
        <!-- Cosmic animated background -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-[-1]">
            <div class="absolute -top-1/2 -left-1/2 w-[200%] h-[200%] bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-primary/10 via-background to-background animate-[spin_60s_linear_infinite] opacity-30"></div>
            <div class="absolute -bottom-1/2 -right-1/2 w-[200%] h-[200%] bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-accent/10 via-background to-background animate-[spin_40s_linear_infinite_reverse] opacity-30"></div>
        </div>

        <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
            <!-- Mobile Sidebar Backdrop -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-background/80 backdrop-blur-sm z-40 lg:hidden"
                 @click="sidebarOpen = false"
                 style="display: none;"></div>

            <!-- Sidebar -->
            <x-sidebar />

            <!-- Main Content Wrapper -->
            <div class="flex-1 flex flex-col overflow-hidden relative z-10 w-full">
                <!-- Topbar -->
                <x-topbar />

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 scroll-smooth">
                    @isset($header)
                        <header class="mb-6">
                            {{ $header }}
                        </header>
                    @endisset

                    <div class="mx-auto max-w-7xl">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
        
        {{ $scripts ?? '' }}
    </body>
</html>
