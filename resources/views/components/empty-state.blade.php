@props([
    'icon' => null,
    'title' => 'No items found',
    'description' => null,
    'action' => null
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-8 text-center rounded-2xl border border-dashed border-border bg-surface/30']) }}>
    @if($icon)
        <div class="w-12 h-12 rounded-full bg-surface-hover flex items-center justify-center text-secondary-text mb-4">
            {{ $icon }}
        </div>
    @else
        <div class="w-12 h-12 rounded-full bg-surface-hover flex items-center justify-center text-secondary-text mb-4">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </div>
    @endif

    <h3 class="text-sm font-semibold text-primary-text">{{ $title }}</h3>
    
    @if($description)
        <p class="mt-1 text-sm text-secondary-text max-w-sm">{{ $description }}</p>
    @endif

    @if($action)
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
