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
    <body class="font-sans text-primary-text antialiased bg-background selection:bg-primary selection:text-white">
        
        <!-- Cosmic animated background -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-[-1]">
            <div class="absolute -top-1/2 -left-1/2 w-[200%] h-[200%] bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-primary/10 via-background to-background animate-[spin_60s_linear_infinite] opacity-50"></div>
            <div class="absolute -bottom-1/2 -right-1/2 w-[200%] h-[200%] bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-accent/10 via-background to-background animate-[spin_40s_linear_infinite_reverse] opacity-50"></div>
        </div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="z-10">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-accent flex items-center justify-center shadow-lg group-hover:shadow-primary/50 transition-all duration-300 transform group-hover:scale-105">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-white group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r group-hover:from-primary group-hover:to-accent transition-all duration-300">
                        DevLoop
                    </span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-8 px-8 py-8 glass shadow-2xl overflow-hidden sm:rounded-2xl z-10 border border-border relative">
                <!-- Subtle glow effect behind card -->
                <div class="absolute -inset-1 bg-gradient-to-r from-primary to-accent rounded-2xl blur opacity-10 pointer-events-none"></div>
                <div class="relative">
                    {{ $slot }}
                </div>
            </div>
            
            <!-- Footer text -->
            <div class="mt-8 text-sm text-secondary-text z-10">
                Build Together. Grow Together.
            </div>
        </div>
    </body>
</html>
