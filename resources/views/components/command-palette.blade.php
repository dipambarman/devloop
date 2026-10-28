<div x-data="{
        isOpen: false,
        query: '',
        results: [],
        selectedIndex: 0,
        loading: false,
        searchTimeout: null,

        init() {
            // Listen for custom trigger event
            window.addEventListener('open-command-palette', () => this.open());
            
            // Global keybind: Cmd+K / Ctrl+K or / (when not typing in an input)
            window.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    this.toggle();
                } else if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                    e.preventDefault();
                    this.open();
                }
            });
        },

        open() {
            this.isOpen = true;
            this.fetchResults();
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        close() {
            this.isOpen = false;
            this.query = '';
            this.selectedIndex = 0;
        },

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        },

        onInput() {
            clearTimeout(this.searchTimeout);
            this.loading = true;
            this.searchTimeout = setTimeout(() => {
                this.fetchResults();
            }, 150);
        },

        async fetchResults() {
            this.loading = true;
            try {
                const url = '{{ route('command-palette.search') }}?q=' + encodeURIComponent(this.query);
                const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    this.results = data.results || [];
                    this.selectedIndex = 0;
                }
            } catch (err) {
                console.error('Command Palette search failed', err);
            } finally {
                this.loading = false;
            }
        },

        navigateSelected() {
            if (this.results.length > 0 && this.results[this.selectedIndex]) {
                const target = this.results[this.selectedIndex];
                if (target.url) {
                    window.location.href = target.url;
                }
            }
        },

        onKeyDown(e) {
            if (!this.isOpen) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (this.results.length > 0) {
                    this.selectedIndex = (this.selectedIndex + 1) % this.results.length;
                    this.scrollToSelected();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (this.results.length > 0) {
                    this.selectedIndex = (this.selectedIndex - 1 + this.results.length) % this.results.length;
                    this.scrollToSelected();
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                this.navigateSelected();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                this.close();
            }
        },

        scrollToSelected() {
            this.$nextTick(() => {
                const el = document.getElementById('palette-item-' + this.selectedIndex);
                if (el) {
                    el.scrollIntoView({ block: 'nearest' });
                }
            });
        }
    }"
    @keydown.window="onKeyDown($event)"
    x-cloak
>
    <!-- Modal Backdrop and Container -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 flex justify-center items-start"
         style="display: none;"
    >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity" @click="close()"></div>

        <!-- Palette Dialog Window -->
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
             class="relative w-full max-w-2xl bg-surface/95 border border-border/80 rounded-2xl shadow-2xl overflow-hidden backdrop-blur-xl ring-1 ring-white/10 z-10"
             @click.outside="close()"
        >
            <!-- Search Header Bar -->
            <div class="flex items-center px-4 py-3.5 border-b border-border/60 gap-3 bg-surface/60">
                <svg x-show="!loading" class="w-5 h-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <!-- Spinner when querying -->
                <svg x-show="loading" class="animate-spin w-5 h-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>

                <input x-ref="searchInput"
                       type="text"
                       x-model="query"
                       @input="onInput()"
                       placeholder="Type a command, project, task, or search notes..."
                       class="w-full bg-transparent border-0 text-primary-text placeholder-secondary-text focus:ring-0 text-sm sm:text-base outline-none p-0 leading-normal"
                       autocomplete="off"
                       spellcheck="false"
                >

                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" @click="close()" class="px-1.5 py-0.5 text-[10px] font-mono text-secondary-text border border-border/60 rounded hover:bg-surface-hover hover:text-primary-text transition-colors">
                        ESC
                    </button>
                </div>
            </div>

            <!-- Results Scroll Box -->
            <div class="max-h-[380px] overflow-y-auto divide-y divide-border/20 py-1">
                <template x-if="results.length === 0 && !loading">
                    <div class="py-12 text-center text-secondary-text">
                        <svg class="w-8 h-8 mx-auto text-secondary-text/50 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm">No results found for "<span class="text-primary-text font-medium" x-text="query"></span>"</p>
                        <p class="text-xs text-secondary-text/80 mt-1">Try searching by project name, task title, or error type</p>
                    </div>
                </template>

                <template x-for="(item, index) in results" :key="item.id || index">
                    <a :href="item.url"
                       :id="'palette-item-' + index"
                       @mouseenter="selectedIndex = index"
                       :class="{
                           'bg-primary/10 border-l-2 border-primary text-primary-text': selectedIndex === index,
                           'border-l-2 border-transparent text-secondary-text hover:text-primary-text': selectedIndex !== index
                       }"
                       class="flex items-center justify-between px-4 py-2.5 transition-colors cursor-pointer group"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Category Icon / Avatar -->
                            <template x-if="item.type === 'project'">
                                <div class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] font-bold text-white shadow shrink-0" :style="'background-color: ' + (item.color || '#6366F1')">
                                    <span x-text="(item.title || 'PR').substring(0, 2).toUpperCase()"></span>
                                </div>
                            </template>

                            <template x-if="item.type === 'task'">
                                <div class="w-6 h-6 rounded-md bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </div>
                            </template>

                            <template x-if="item.type === 'bug'">
                                <div class="w-6 h-6 rounded-md bg-red-500/10 border border-red-500/20 text-red-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                            </template>

                            <template x-if="item.type === 'action'">
                                <div class="w-6 h-6 rounded-md bg-accent/10 border border-accent/20 text-accent flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </template>

                            <template x-if="item.type === 'note'">
                                <div class="w-6 h-6 rounded-md bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                            </template>

                            <template x-if="item.type === 'snippet'">
                                <div class="w-6 h-6 rounded-md bg-teal-500/10 border border-teal-500/20 text-teal-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                </div>
                            </template>

                            <!-- Title & Subtitle -->
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-medium text-primary-text truncate" x-text="item.title"></span>
                                <span class="text-[11px] text-secondary-text truncate" x-text="item.subtitle"></span>
                            </div>
                        </div>

                        <!-- Badge Category Pill / Meta -->
                        <div class="flex items-center gap-2 shrink-0 ml-3">
                            <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded-full bg-surface-hover/80 text-secondary-text border border-border/40" x-text="item.category"></span>
                            <svg class="w-3.5 h-3.5 text-secondary-text/60 group-hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>
                </template>
            </div>

            <!-- Footer Hints -->
            <div class="px-4 py-2 border-t border-border/50 bg-background/50 flex items-center justify-between text-[11px] text-secondary-text">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 rounded bg-surface border border-border text-[10px] font-mono">↑</kbd>
                        <kbd class="px-1.5 py-0.5 rounded bg-surface border border-border text-[10px] font-mono">↓</kbd>
                        navigate
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 rounded bg-surface border border-border text-[10px] font-mono">↵</kbd>
                        open
                    </span>
                </div>
                <div class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 rounded bg-surface border border-border text-[10px] font-mono">esc</kbd>
                    close
                </div>
            </div>
        </div>
    </div>
</div>
