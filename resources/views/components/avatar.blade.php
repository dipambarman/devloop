@props([
    'name' => 'User',
    'src' => null,
    'size' => 'md',
    'status' => null // 'online', 'offline', 'busy', 'away'
])

@php
    $sizeClasses = [
        'xs' => 'w-6 h-6 text-[10px]',
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-12 h-12 text-base',
        'xl' => 'w-16 h-16 text-lg',
    ][$size] ?? 'w-10 h-10 text-sm';

    $statusClasses = [
        'online' => 'bg-teal-500',
        'offline' => 'bg-secondary-text',
        'busy' => 'bg-red-500',
        'away' => 'bg-yellow-500',
    ][$status] ?? null;

    $safeName = trim($name ?: 'User');
    $initials = collect(explode(' ', $safeName))->filter()->map(fn($word) => substr($word, 0, 1))->take(2)->join('');
    if (empty($initials)) $initials = 'U';
@endphp

<div class="relative inline-block">
    <div {{ $attributes->merge(['class' => "rounded-full flex items-center justify-center font-medium bg-gradient-to-br from-primary to-accent text-white overflow-hidden shrink-0 border border-border shadow-sm $sizeClasses"]) }}>
        @if($src)
            <img src="{{ $src }}" alt="{{ $name }}" class="w-full h-full object-cover" />
        @else
            <span>{{ strtoupper($initials) }}</span>
        @endif
    </div>
    
    @if($statusClasses)
        <span class="absolute bottom-0 right-0 block w-2.5 h-2.5 rounded-full ring-2 ring-background {{ $statusClasses }}"></span>
    @endif
</div>
