<div {{ $attributes->merge(['class' => 'glass rounded-xl shadow-sm border border-border overflow-hidden relative']) }}>
    @if(isset($glow))
        <!-- Optional subtle glow behind the card -->
        <div class="absolute -inset-1 bg-gradient-to-r from-primary to-accent rounded-xl blur opacity-[0.03] pointer-events-none"></div>
    @endif
    
    <div class="relative h-full flex flex-col">
        @if(isset($header))
            <div class="px-6 py-4 border-b border-border bg-surface/50 backdrop-blur-sm">
                {{ $header }}
            </div>
        @endif

        <div class="p-6 flex-1">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="px-6 py-4 border-t border-border bg-surface/50 backdrop-blur-sm mt-auto">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
