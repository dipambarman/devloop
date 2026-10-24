<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>DevLoop — Home to Developers. Build Together. Grow Together.</title>

        <!-- SEO Meta Tags -->
        <meta name="description" content="DevLoop is a modern developer collaboration platform for project management, task tracking, Kanban boards, and team communication. Free and open-source.">
        <meta name="keywords" content="developer tools, project management, kanban board, task tracking, team collaboration, open source, laravel">
        <meta name="author" content="Dipam Barman">

        <!-- Open Graph / Social Media -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ config('app.url') }}">
        <meta property="og:title" content="DevLoop — Home to Developers">
        <meta property="og:description" content="A modern developer collaboration platform for project management, task tracking, Kanban boards, and team communication.">
        <meta property="og:image" content="{{ asset('favicon.ico') }}">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="DevLoop — Home to Developers">
        <meta name="twitter:description" content="A modern developer collaboration platform. Build Together. Grow Together.">

        <!-- Canonical URL -->
        <link rel="canonical" href="{{ config('app.url') }}">

        <!-- Allow indexing on the landing page -->
        <meta name="robots" content="index, follow">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Landing page specific animations */
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            @keyframes float-delayed {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-15px); }
            }
            @keyframes pulse-glow {
                0%, 100% { opacity: 0.4; transform: scale(1); }
                50% { opacity: 0.8; transform: scale(1.05); }
            }
            @keyframes slide-up {
                from { opacity: 0; transform: translateY(40px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes slide-in-left {
                from { opacity: 0; transform: translateX(-40px); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes slide-in-right {
                from { opacity: 0; transform: translateX(40px); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes count-up {
                from { opacity: 0; transform: scale(0.5); }
                to { opacity: 1; transform: scale(1); }
            }
            @keyframes gradient-shift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            @keyframes typewriter {
                from { width: 0; }
                to { width: 100%; }
            }
            @keyframes blink-caret {
                from, to { border-color: transparent; }
                50% { border-color: #6366F1; }
            }
            .animate-float { animation: float 6s ease-in-out infinite; }
            .animate-float-delayed { animation: float-delayed 5s ease-in-out infinite 1s; }
            .animate-pulse-glow { animation: pulse-glow 4s ease-in-out infinite; }
            .animate-slide-up { animation: slide-up 0.8s ease-out forwards; }
            .animate-slide-up-delay-1 { animation: slide-up 0.8s ease-out 0.15s forwards; opacity: 0; }
            .animate-slide-up-delay-2 { animation: slide-up 0.8s ease-out 0.3s forwards; opacity: 0; }
            .animate-slide-up-delay-3 { animation: slide-up 0.8s ease-out 0.45s forwards; opacity: 0; }
            .animate-slide-up-delay-4 { animation: slide-up 0.8s ease-out 0.6s forwards; opacity: 0; }
            .animate-gradient-shift {
                background-size: 200% 200%;
                animation: gradient-shift 4s ease infinite;
            }

            .feature-card {
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .feature-card:hover {
                transform: translateY(-8px);
                border-color: rgba(99, 102, 241, 0.4);
                box-shadow: 0 20px 40px rgba(99, 102, 241, 0.1), 0 0 80px rgba(139, 92, 246, 0.05);
            }
            .feature-card:hover .feature-icon {
                transform: scale(1.1) rotate(-5deg);
            }

            .cta-button {
                position: relative;
                overflow: hidden;
            }
            .cta-button::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
                transition: left 0.6s ease;
            }
            .cta-button:hover::before {
                left: 100%;
            }

            /* Scroll-triggered animations via Intersection Observer */
            .reveal {
                opacity: 0;
                transform: translateY(30px);
                transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .reveal.visible {
                opacity: 1;
                transform: translateY(0);
            }

            /* Grid pattern background */
            .grid-pattern {
                background-image:
                    linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
                background-size: 60px 60px;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-primary-text bg-background selection:bg-primary selection:text-white overflow-x-hidden">

        <!-- Cosmic animated background -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-[-1]">
            <div class="absolute -top-1/2 -left-1/2 w-[200%] h-[200%] bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-primary/10 via-background to-background animate-[spin_60s_linear_infinite] opacity-30"></div>
            <div class="absolute -bottom-1/2 -right-1/2 w-[200%] h-[200%] bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-accent/10 via-background to-background animate-[spin_40s_linear_infinite_reverse] opacity-30"></div>
        </div>

        <!-- Grid pattern overlay -->
        <div class="fixed inset-0 grid-pattern pointer-events-none z-[-1]"></div>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- NAVIGATION -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <nav class="fixed top-0 left-0 right-0 z-50 border-b border-border/50 backdrop-blur-xl bg-background/70" id="landing-nav">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <a href="/" class="flex items-center gap-3 group" id="landing-logo">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-accent flex items-center justify-center shadow-lg group-hover:shadow-primary/50 transition-all duration-300 transform group-hover:scale-105">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-white">DevLoop</span>
                    </a>

                    <!-- Nav Links -->
                    <div class="hidden md:flex items-center gap-8">
                        <a href="#features" class="text-sm text-secondary-text hover:text-primary-text transition-colors">Features</a>
                        <a href="#tech-stack" class="text-sm text-secondary-text hover:text-primary-text transition-colors">Tech Stack</a>
                        <a href="{{ route('changelog') }}" class="text-sm text-secondary-text hover:text-primary-text transition-colors">Changelog</a>
                        <a href="#open-source" class="text-sm text-secondary-text hover:text-primary-text transition-colors">Open Source</a>
                    </div>

                    <!-- Auth Buttons -->
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" id="nav-dashboard-btn"
                               class="px-5 py-2 text-sm font-medium rounded-lg bg-gradient-to-r from-primary to-accent text-white hover:opacity-90 transition-opacity shadow-lg shadow-primary/25">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" id="nav-login-btn"
                               class="px-4 py-2 text-sm font-medium text-secondary-text hover:text-primary-text transition-colors">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" id="nav-register-btn"
                                   class="cta-button px-5 py-2 text-sm font-medium rounded-lg bg-gradient-to-r from-primary to-accent text-white hover:opacity-90 transition-opacity shadow-lg shadow-primary/25">
                                    Get Started Free
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- HERO SECTION -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <section class="relative min-h-screen flex items-center justify-center pt-16" id="hero">
            <!-- Decorative floating elements -->
            <div class="absolute top-32 left-10 w-72 h-72 bg-primary/10 rounded-full blur-3xl animate-pulse-glow pointer-events-none"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-accent/10 rounded-full blur-3xl animate-pulse-glow pointer-events-none" style="animation-delay: 2s;"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                <!-- Badge -->
                <div class="animate-slide-up inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-border bg-surface/50 backdrop-blur-sm mb-8">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-medium text-secondary-text">Free & Open Source — Built with Laravel 12</span>
                </div>

                <!-- Headline -->
                <h1 class="animate-slide-up-delay-1 text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight leading-tight mb-6">
                    <span class="text-primary-text">Home to</span>
                    <br>
                    <span class="bg-gradient-to-r from-primary via-accent to-teal bg-clip-text text-transparent animate-gradient-shift">
                        Developers
                    </span>
                </h1>

                <!-- Subtitle -->
                <p class="animate-slide-up-delay-2 text-lg sm:text-xl text-secondary-text max-w-2xl mx-auto mb-10 leading-relaxed">
                    A modern collaboration platform for project management, task tracking, Kanban boards, and team communication.
                    <span class="text-primary-text font-medium">Build Together. Grow Together.</span>
                </p>

                <!-- CTA Buttons -->
                <div class="animate-slide-up-delay-3 flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                    @auth
                        <a href="{{ url('/dashboard') }}" id="hero-dashboard-btn"
                           class="cta-button group px-8 py-3.5 text-base font-semibold rounded-xl bg-gradient-to-r from-primary to-accent text-white shadow-2xl shadow-primary/30 hover:shadow-primary/50 transition-all duration-300 hover:scale-105">
                            Go to Dashboard
                            <svg class="inline-block w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" id="hero-signup-btn"
                           class="cta-button group px-8 py-3.5 text-base font-semibold rounded-xl bg-gradient-to-r from-primary to-accent text-white shadow-2xl shadow-primary/30 hover:shadow-primary/50 transition-all duration-300 hover:scale-105">
                            Start Building — It's Free
                            <svg class="inline-block w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    @endauth
                    <a href="https://github.com/dipambarman/devloop" target="_blank" rel="noopener" id="hero-github-btn"
                       class="group flex items-center gap-2 px-8 py-3.5 text-base font-semibold rounded-xl border border-border hover:border-border-hover text-primary-text bg-surface/50 hover:bg-surface transition-all duration-300">
                        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        Star on GitHub
                    </a>
                </div>

                <!-- Hero Visual — Code Editor Mock -->
                <div class="animate-slide-up-delay-4 relative max-w-4xl mx-auto">
                    <div class="absolute -inset-4 bg-gradient-to-r from-primary/20 via-accent/20 to-teal/20 rounded-2xl blur-2xl opacity-50 animate-pulse-glow"></div>
                    <div class="relative glass rounded-2xl border border-border overflow-hidden shadow-2xl">
                        <!-- Window chrome -->
                        <div class="flex items-center gap-2 px-4 py-3 bg-surface/80 border-b border-border">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                            </div>
                            <div class="flex-1 text-center">
                                <span class="text-xs text-tertiary-text">devloop.duckdns.org/dashboard</span>
                            </div>
                        </div>
                        <!-- Dashboard Preview -->
                        <div class="p-6 sm:p-8 bg-background/90">
                            <!-- Stats Row -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                                <div class="glass rounded-xl p-4 border border-border">
                                    <div class="text-xs text-secondary-text mb-1">Projects</div>
                                    <div class="text-2xl font-bold text-primary-text">12</div>
                                </div>
                                <div class="glass rounded-xl p-4 border border-border">
                                    <div class="text-xs text-secondary-text mb-1">Active Tasks</div>
                                    <div class="text-2xl font-bold text-primary">47</div>
                                </div>
                                <div class="glass rounded-xl p-4 border border-border">
                                    <div class="text-xs text-secondary-text mb-1">Completion</div>
                                    <div class="text-2xl font-bold text-emerald-400">89%</div>
                                </div>
                                <div class="glass rounded-xl p-4 border border-border">
                                    <div class="text-xs text-secondary-text mb-1">Team</div>
                                    <div class="text-2xl font-bold text-accent">8</div>
                                </div>
                            </div>
                            <!-- Kanban Preview -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="space-y-2">
                                    <div class="text-xs font-medium text-secondary-text uppercase tracking-wider px-1">Todo</div>
                                    <div class="glass rounded-lg p-3 border border-border">
                                        <div class="text-xs font-medium mb-1">Setup CI/CD</div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>
                                            <span class="text-[10px] text-tertiary-text">Medium</span>
                                        </div>
                                    </div>
                                    <div class="glass rounded-lg p-3 border border-border">
                                        <div class="text-xs font-medium mb-1">API Docs</div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                            <span class="text-[10px] text-tertiary-text">Low</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="text-xs font-medium text-secondary-text uppercase tracking-wider px-1">In Progress</div>
                                    <div class="glass rounded-lg p-3 border border-primary/40 bg-primary/5">
                                        <div class="text-xs font-medium mb-1">Auth System</div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                            <span class="text-[10px] text-tertiary-text">Urgent</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="text-xs font-medium text-secondary-text uppercase tracking-wider px-1">Review</div>
                                    <div class="glass rounded-lg p-3 border border-border">
                                        <div class="text-xs font-medium mb-1">Dark Theme</div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                                            <span class="text-[10px] text-tertiary-text">High</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="text-xs font-medium text-secondary-text uppercase tracking-wider px-1">Done</div>
                                    <div class="glass rounded-lg p-3 border border-emerald-500/30 bg-emerald-500/5">
                                        <div class="text-xs font-medium mb-1">User Profiles</div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            <span class="text-[10px] text-tertiary-text">Complete</span>
                                        </div>
                                    </div>
                                    <div class="glass rounded-lg p-3 border border-emerald-500/30 bg-emerald-500/5">
                                        <div class="text-xs font-medium mb-1">Kanban Board</div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            <span class="text-[10px] text-tertiary-text">Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- FEATURES SECTION -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <section class="relative py-32" id="features">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-20 reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-border bg-surface/50 mb-6">
                        <span class="text-xs font-medium text-secondary-text">Everything you need</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight mb-4">
                        <span class="text-primary-text">Built for developers,</span><br>
                        <span class="bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">by developers</span>
                    </h2>
                    <p class="text-lg text-secondary-text max-w-2xl mx-auto">
                        DevLoop combines the best of GitHub, Linear, Jira, and Notion into one beautiful, open-source platform.
                    </p>
                </div>

                <!-- Feature Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Feature 1: Kanban -->
                    <div class="feature-card glass rounded-2xl p-8 border border-border reveal" id="feature-kanban">
                        <div class="feature-icon w-14 h-14 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center mb-6 transition-transform duration-300">
                            <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-primary-text mb-2">Kanban Board</h3>
                        <p class="text-sm text-secondary-text leading-relaxed">Drag-and-drop task management with visual columns. Move tasks through your workflow effortlessly.</p>
                    </div>

                    <!-- Feature 2: Project Management -->
                    <div class="feature-card glass rounded-2xl p-8 border border-border reveal" id="feature-projects">
                        <div class="feature-icon w-14 h-14 rounded-xl bg-gradient-to-br from-accent/20 to-accent/5 flex items-center justify-center mb-6 transition-transform duration-300">
                            <svg class="w-7 h-7 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-primary-text mb-2">Project Management</h3>
                        <p class="text-sm text-secondary-text leading-relaxed">Create projects, track progress, assign team members, and view activity timelines in one place.</p>
                    </div>

                    <!-- Feature 3: Task Tracking -->
                    <div class="feature-card glass rounded-2xl p-8 border border-border reveal" id="feature-tasks">
                        <div class="feature-icon w-14 h-14 rounded-xl bg-gradient-to-br from-teal/20 to-teal/5 flex items-center justify-center mb-6 transition-transform duration-300">
                            <svg class="w-7 h-7 text-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-primary-text mb-2">Task Tracking</h3>
                        <p class="text-sm text-secondary-text leading-relaxed">Full task lifecycle with priorities, due dates, assignees, status workflow, tags, and comments.</p>
                    </div>

                    <!-- Feature 4: Discussions -->
                    <div class="feature-card glass rounded-2xl p-8 border border-border reveal" id="feature-discussions">
                        <div class="feature-icon w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 flex items-center justify-center mb-6 transition-transform duration-300">
                            <svg class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-primary-text mb-2">Team Discussions</h3>
                        <p class="text-sm text-secondary-text leading-relaxed">Threaded project discussions with replies, pinning, and notification support for seamless collaboration.</p>
                    </div>

                    <!-- Feature 5: Developer Workspace -->
                    <div class="feature-card glass rounded-2xl p-8 border border-border reveal" id="feature-workspace">
                        <div class="feature-icon w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500/20 to-amber-500/5 flex items-center justify-center mb-6 transition-transform duration-300">
                            <svg class="w-7 h-7 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-primary-text mb-2">Developer Workspace</h3>
                        <p class="text-sm text-secondary-text leading-relaxed">Personal notes with Markdown, code snippets with syntax highlighting, and customizable developer profiles.</p>
                    </div>

                    <!-- Feature 6: Security -->
                    <div class="feature-card glass rounded-2xl p-8 border border-border reveal" id="feature-security">
                        <div class="feature-icon w-14 h-14 rounded-xl bg-gradient-to-br from-rose-500/20 to-rose-500/5 flex items-center justify-center mb-6 transition-transform duration-300">
                            <svg class="w-7 h-7 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-primary-text mb-2">Production Security</h3>
                        <p class="text-sm text-secondary-text leading-relaxed">Rate limiting, security headers, strong passwords, HSTS, policy-based auth, and encrypted sessions.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- TECH STACK SECTION -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <section class="relative py-32 border-t border-border/30" id="tech-stack">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-border bg-surface/50 mb-6">
                        <span class="text-xs font-medium text-secondary-text">Modern stack</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">
                        <span class="text-primary-text">Built with </span>
                        <span class="bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">cutting-edge tech</span>
                    </h2>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 reveal">
                    @php
                    $techStack = [
                        ['name' => 'Laravel 12', 'color' => 'text-red-400', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                        ['name' => 'PHP 8.3', 'color' => 'text-indigo-400', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                        ['name' => 'Tailwind CSS', 'color' => 'text-cyan-400', 'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
                        ['name' => 'Alpine.js', 'color' => 'text-sky-400', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ['name' => 'MySQL 8.4', 'color' => 'text-blue-400', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4'],
                        ['name' => 'Redis', 'color' => 'text-red-500', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
                    ];
                    @endphp

                    @foreach ($techStack as $tech)
                        <div class="glass rounded-xl p-4 border border-border text-center hover:border-border-hover hover:-translate-y-1 transition-all duration-300">
                            <svg class="w-8 h-8 {{ $tech['color'] }} mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $tech['icon'] }}" />
                            </svg>
                            <div class="text-sm font-medium text-primary-text">{{ $tech['name'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- OPEN SOURCE SECTION -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <section class="relative py-32 border-t border-border/30" id="open-source">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-border bg-surface/50 mb-6">
                        <svg class="w-4 h-4 text-secondary-text" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        <span class="text-xs font-medium text-secondary-text">MIT Licensed</span>
                    </div>

                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight mb-6">
                        <span class="text-primary-text">100% Open Source.</span><br>
                        <span class="bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">Forever Free.</span>
                    </h2>

                    <p class="text-lg text-secondary-text max-w-2xl mx-auto mb-10 leading-relaxed">
                        DevLoop is free to use, free to deploy, and free to modify. Browse the source, submit pull requests, and build on top of it.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="https://github.com/dipambarman/devloop" target="_blank" rel="noopener" id="oss-github-btn"
                           class="cta-button group flex items-center gap-3 px-8 py-3.5 text-base font-semibold rounded-xl border border-border hover:border-primary/50 text-primary-text bg-surface/50 hover:bg-surface transition-all duration-300 hover:shadow-lg hover:shadow-primary/10">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            View on GitHub
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                        @guest
                            <a href="{{ route('register') }}" id="oss-signup-btn"
                               class="cta-button px-8 py-3.5 text-base font-semibold rounded-xl bg-gradient-to-r from-primary to-accent text-white shadow-2xl shadow-primary/30 hover:shadow-primary/50 transition-all duration-300 hover:scale-105">
                                Get Started Free
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- FOOTER -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <footer class="border-t border-border/30 py-12" id="landing-footer">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <!-- Logo & tagline -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <span class="text-sm text-secondary-text">
                            Built with ❤️ by <a href="https://github.com/dipambarman" target="_blank" rel="noopener" class="text-primary-text hover:text-primary transition-colors font-medium">Dipam Barman</a>
                        </span>
                    </div>

                    <!-- Links -->
                    <div class="flex items-center gap-6 text-sm text-secondary-text">
                        <a href="https://github.com/dipambarman/devloop" target="_blank" rel="noopener" class="hover:text-primary-text transition-colors">GitHub</a>
                        <a href="#features" class="hover:text-primary-text transition-colors">Features</a>
                        <a href="{{ route('changelog') }}" class="hover:text-primary-text transition-colors">Changelog</a>
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="hover:text-primary-text transition-colors">Log in</a>
                        @endif
                    </div>

                    <!-- Copyright -->
                    <div class="text-sm text-tertiary-text">
                        © {{ date('Y') }} DevLoop. MIT License.
                    </div>
                </div>
            </div>
        </footer>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- SCROLL REVEAL SCRIPT -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Intersection Observer for scroll-triggered animations
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry, index) => {
                        if (entry.isIntersecting) {
                            // Add staggered delay based on element index among siblings
                            const siblings = entry.target.parentElement.querySelectorAll('.reveal');
                            const siblingIndex = Array.from(siblings).indexOf(entry.target);
                            entry.target.style.transitionDelay = `${siblingIndex * 0.1}s`;
                            entry.target.classList.add('visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });

                document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

                // Smooth scroll for anchor links
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                });

                // Navbar background on scroll
                const nav = document.getElementById('landing-nav');
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 50) {
                        nav.classList.add('shadow-lg', 'shadow-background/50');
                    } else {
                        nav.classList.remove('shadow-lg', 'shadow-background/50');
                    }
                });
            });
        </script>
    </body>
</html>
