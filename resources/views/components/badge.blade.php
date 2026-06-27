@props(['color' => 'primary', 'dot' => false])

@php
    $colors = [
        'primary' => 'bg-primary/10 text-primary border border-primary/20',
        'accent' => 'bg-accent/10 text-accent border border-accent/20',
        'success' => 'bg-teal-500/10 text-teal-500 border border-teal-500/20', // Using teal as success in our dark theme
        'warning' => 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20',
        'danger' => 'bg-red-500/10 text-red-500 border border-red-500/20',
        'info' => 'bg-blue-500/10 text-blue-500 border border-blue-500/20',
        'gray' => 'bg-surface-hover text-secondary-text border border-border',
    ];

    $dotColors = [
        'primary' => 'bg-primary',
        'accent' => 'bg-accent',
        'success' => 'bg-teal-500',
        'warning' => 'bg-yellow-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-blue-500',
        'gray' => 'bg-secondary-text',
    ];

    $colorClass = $colors[$color] ?? $colors['primary'];
    $dotClass = $dotColors[$color] ?? $dotColors['primary'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $colorClass"]) }}>
    @if($dot)
        <span class="mr-1.5 flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full opacity-75 {{ $dotClass }}"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 {{ $dotClass }}"></span>
        </span>
    @endif
    {{ $slot }}
</span>
