<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Changelog — DevLoop | Version History & Updates</title>
        <meta name="description" content="Explore what's new in DevLoop. Track releases, features, security improvements, and architecture updates.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #0A0A0F;
                color: #F8FAFC;
                overflow-x: hidden;
            }
            .code-font {
                font-family: 'JetBrains Mono', monospace;
            }
            .glass {
                background: rgba(19, 19, 26, 0.7);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.08);
            }
            .text-gradient {
                background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 50%, #EC4899 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
        </style>
    </head>
    <body class="min-h-screen flex flex-col bg-[#0A0A0F] text-slate-100 antialiased selection:bg-indigo-500 selection:text-white">

        <!-- NAVBAR -->
        <nav class="sticky top-0 z-50 glass border-b border-white/5 py-4 px-4 sm:px-8">
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-bold tracking-tight text-white">Dev<span class="text-indigo-400">Loop</span></span>
                        <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Changelog</span>
                    </div>
                </a>

                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="text-sm text-slate-400 hover:text-white transition-colors">
                        ← Back to Home
                    </a>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition-all shadow-md shadow-indigo-600/20">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition-all shadow-md shadow-indigo-600/20">
                                Get Started
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </nav>

        <!-- HEADER -->
        <header class="py-16 sm:py-20 px-4 sm:px-8 text-center relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                <div class="w-[500px] h-[300px] bg-indigo-600/10 rounded-full blur-3xl"></div>
            </div>
            <div class="max-w-3xl mx-auto relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-indigo-500/20 bg-indigo-500/10 text-indigo-400 text-xs font-medium mb-4">
                    <span>✨ Continuous Iteration</span>
                </div>
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-4">
                    DevLoop <span class="text-gradient">Changelog</span>
                </h1>
                <p class="text-base sm:text-lg text-slate-400 max-w-xl mx-auto">
                    A transparent timeline of how DevLoop is built, improved, and scaled.
                </p>
            </div>
        </header>

        <!-- TIMELINE ENTRIES -->
        <main class="flex-grow max-w-4xl mx-auto px-4 sm:px-6 pb-24 w-full">
            <div class="relative border-l border-white/10 ml-4 sm:ml-8 space-y-12">

                <!-- v1.4.0 -->
                <div class="relative pl-8 sm:pl-10 group">
                    <!-- Dot marker -->
                    <div class="absolute -left-2.5 top-1.5 w-5 h-5 rounded-full bg-indigo-500 border-4 border-[#0A0A0F] shadow-lg shadow-indigo-500/50"></div>

                    <div class="glass rounded-2xl p-6 sm:p-8 border border-indigo-500/30 bg-indigo-950/10">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-sm font-bold code-font">
                                    v1.4.0
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-semibold">
                                    Latest Release
                                </span>
                            </div>
                            <time class="text-xs text-slate-400 font-medium">October 2026</time>
                        </div>

                        <h2 class="text-xl sm:text-2xl font-bold text-white mb-3">
                            Production Architecture, First-Run Onboarding & Marketing Site
                        </h2>
                        <p class="text-slate-300 text-sm mb-6 leading-relaxed">
                            Prepared DevLoop for public cloud deployment on Oracle Cloud Always Free tier and transformed the first-run experience for new users with an automated interactive sandbox.
                        </p>

                        <div class="space-y-4 text-sm text-slate-300">
                            <div>
                                <h3 class="text-xs uppercase tracking-wider font-semibold text-indigo-400 mb-2">🚀 Production & Infrastructure</h3>
                                <ul class="list-disc list-inside space-y-1.5 text-slate-400 ml-1">
                                    <li>Multi-stage production <span class="text-slate-200">Dockerfile</span> with Node 20 asset bundling and Composer autoloader optimization</li>
                                    <li>5-service <span class="text-slate-200">Docker Compose</span> stack: PHP 8.3-FPM, Nginx, MySQL 8.4, Redis 7, and Queue worker</li>
                                    <li>Automated GitHub Actions CI (<span class="text-slate-200">test.yml</span>) and continuous deployment (<span class="text-slate-200">deploy.yml</span>)</li>
                                    <li>Automated cloud setup (<span class="text-slate-200">scripts/setup-oracle.sh</span>) and database backup script with 14-day rotation</li>
                                </ul>
                            </div>

                            <div>
                                <h3 class="text-xs uppercase tracking-wider font-semibold text-purple-400 mb-2">✨ User Experience & Growth</h3>
                                <ul class="list-disc list-inside space-y-1.5 text-slate-400 ml-1">
                                    <li><span class="text-slate-200">Automated Onboarding Engine:</span> New registrations automatically receive an interactive starter project with Kanban cards, markdown note guide, and code snippets</li>
                                    <li>Premium public landing page (<span class="text-slate-200">/</span>) with cosmic dark theme, dashboard mockups, and scroll-reveal animations</li>
                                    <li>SEO meta tags, Twitter Cards, OpenGraph tags, and optimized <span class="text-slate-200">robots.txt</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- v1.3.0 -->
                <div class="relative pl-8 sm:pl-10 group">
                    <div class="absolute -left-2 top-2 w-4 h-4 rounded-full bg-purple-500 border-4 border-[#0A0A0F]"></div>

                    <div class="glass rounded-2xl p-6 sm:p-8">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <span class="px-3 py-1 rounded-lg bg-purple-500/10 text-purple-300 border border-purple-500/20 text-sm font-bold code-font">
                                v1.3.0
                            </span>
                            <time class="text-xs text-slate-400 font-medium">August 2026</time>
                        </div>

                        <h2 class="text-xl font-bold text-white mb-2">Security Hardening & Rate Limiting</h2>
                        <p class="text-slate-400 text-sm mb-4 leading-relaxed">
                            Implemented enterprise-grade security middleware, tiered rate limits, and password protection application-wide.
                        </p>

                        <ul class="list-disc list-inside space-y-1.5 text-sm text-slate-400 ml-1">
                            <li>5-tiered rate limiting architecture (global, write, search, upload, critical auth)</li>
                            <li>Security headers middleware (Strict-Transport-Security, CSP, XSS protection, anti-clickjacking)</li>
                            <li>Strong password policy enforcement with compromised password checking</li>
                            <li>Session invalidation on password update and password reset</li>
                            <li>Authorization policies guarding Notes, Snippets, Projects, and Tasks</li>
                        </ul>
                    </div>
                </div>

                <!-- v1.2.0 -->
                <div class="relative pl-8 sm:pl-10 group">
                    <div class="absolute -left-2 top-2 w-4 h-4 rounded-full bg-cyan-500 border-4 border-[#0A0A0F]"></div>

                    <div class="glass rounded-2xl p-6 sm:p-8">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <span class="px-3 py-1 rounded-lg bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 text-sm font-bold code-font">
                                v1.2.0
                            </span>
                            <time class="text-xs text-slate-400 font-medium">July 2026</time>
                        </div>

                        <h2 class="text-xl font-bold text-white mb-2">Team Collaboration & Kanban Board</h2>
                        <p class="text-slate-400 text-sm mb-4 leading-relaxed">
                            Turned DevLoop into a collaborative platform with interactive boards and discussion threads.
                        </p>

                        <ul class="list-disc list-inside space-y-1.5 text-sm text-slate-400 ml-1">
                            <li>Drag-and-drop Kanban board powered by SortableJS with instant order saving</li>
                            <li>Project member invitations by email with Member and Viewer permission roles</li>
                            <li>Threaded project discussions with pinning and author permissions</li>
                            <li>Event-driven polymorphic activity log feed</li>
                            <li>In-app notification system with mark-as-read</li>
                        </ul>
                    </div>
                </div>

                <!-- v1.1.0 -->
                <div class="relative pl-8 sm:pl-10 group">
                    <div class="absolute -left-2 top-2 w-4 h-4 rounded-full bg-emerald-500 border-4 border-[#0A0A0F]"></div>

                    <div class="glass rounded-2xl p-6 sm:p-8">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <span class="px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 text-sm font-bold code-font">
                                v1.1.0
                            </span>
                            <time class="text-xs text-slate-400 font-medium">June 2026</time>
                        </div>

                        <h2 class="text-xl font-bold text-white mb-2">Personal Developer Workspace</h2>
                        <p class="text-slate-400 text-sm mb-4 leading-relaxed">
                            Personal productivity tooling for engineers to organize snippets, notes, and technical profiles.
                        </p>

                        <ul class="list-disc list-inside space-y-1.5 text-sm text-slate-400 ml-1">
                            <li>Markdown notes with pin-to-top and project linking</li>
                            <li>Syntax-highlighted code snippet storage across multiple programming languages</li>
                            <li>Developer profile pages with skills badges, bio, and social portfolio links</li>
                            <li>Dashboard analytics with interactive Chart.js visualizations</li>
                        </ul>
                    </div>
                </div>

                <!-- v1.0.0 -->
                <div class="relative pl-8 sm:pl-10 group">
                    <div class="absolute -left-2 top-2 w-4 h-4 rounded-full bg-slate-600 border-4 border-[#0A0A0F]"></div>

                    <div class="glass rounded-2xl p-6 sm:p-8">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <span class="px-3 py-1 rounded-lg bg-slate-700/50 text-slate-300 border border-slate-600 text-sm font-bold code-font">
                                v1.0.0
                            </span>
                            <time class="text-xs text-slate-400 font-medium">May 2026</time>
                        </div>

                        <h2 class="text-xl font-bold text-white mb-2">Initial Release & Core Foundation</h2>
                        <p class="text-slate-400 text-sm mb-4 leading-relaxed">
                            The initial foundation of DevLoop built on Laravel 12 and PHP 8.3.
                        </p>

                        <ul class="list-disc list-inside space-y-1.5 text-sm text-slate-400 ml-1">
                            <li>Laravel 12 project setup with PHP 8.3</li>
                            <li>Breeze authentication with secure password verification</li>
                            <li>Cosmic dark theme design system built with custom Tailwind tokens</li>
                            <li>Project and Task CRUD with priority workflows and soft deletes</li>
                        </ul>
                    </div>
                </div>

            </div>
        </main>

        <!-- FOOTER -->
        <footer class="border-t border-white/5 py-8 text-center text-sm text-slate-400">
            <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span>© {{ date('Y') }} DevLoop. Built by <a href="https://github.com/dipambarman" class="text-slate-200 hover:text-indigo-400 transition-colors">Dipam Barman</a>.</span>
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                    <a href="https://github.com/dipambarman/devloop" target="_blank" rel="noopener" class="hover:text-white transition-colors">GitHub</a>
                </div>
            </div>
        </footer>

    </body>
</html>
