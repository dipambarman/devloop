@props(['color' => null, 'value' => null, 'dot' => false])

@php
    $label = $slot->isNotEmpty() ? $slot : null;
    $badgeColor = $color;

    if ($value instanceof \App\Enums\TaskStatus) {
        $label = $label ?: $value->label();
        $badgeColor = $badgeColor ?: match($value) {
            \App\Enums\TaskStatus::Todo => 'gray',
            \App\Enums\TaskStatus::InProgress => 'primary',
            \App\Enums\TaskStatus::Review => 'warning',
            \App\Enums\TaskStatus::Done => 'success',
        };
    } elseif ($value instanceof \App\Enums\TaskPriority) {
        $label = $label ?: $value->label();
        $badgeColor = $badgeColor ?: match($value) {
            \App\Enums\TaskPriority::Low => 'gray',
            \App\Enums\TaskPriority::Medium => 'info',
            \App\Enums\TaskPriority::High => 'warning',
            \App\Enums\TaskPriority::Urgent => 'danger',
        };
    } elseif ($value instanceof \App\Enums\ProjectStatus) {
        $label = $label ?: $value->label();
        $badgeColor = $badgeColor ?: match($value) {
            \App\Enums\ProjectStatus::Active => 'success',
            \App\Enums\ProjectStatus::Archived => 'gray',
            \App\Enums\ProjectStatus::OnHold => 'warning',
        };
    } elseif ($value instanceof \App\Enums\ProjectRole) {
        $label = $label ?: $value->label();
        $badgeColor = $badgeColor ?: match($value) {
            \App\Enums\ProjectRole::Owner => 'primary',
            \App\Enums\ProjectRole::Member => 'info',
            \App\Enums\ProjectRole::Viewer => 'gray',
        };
    }

    $badgeColor = $badgeColor ?: 'primary';

    $colors = [
        'primary' => 'bg-primary/10 text-primary border border-primary/20',
        'accent' => 'bg-accent/10 text-accent border border-accent/20',
        'success' => 'bg-teal-500/10 text-teal-500 border border-teal-500/20',
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

    $colorClass = $colors[$badgeColor] ?? $colors['primary'];
    $dotClass = $dotColors[$badgeColor] ?? $dotColors['primary'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $colorClass"]) }}>
    @if($dot)
        <span class="mr-1.5 flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full opacity-75 {{ $dotClass }}"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 {{ $dotClass }}"></span>
        </span>
    @endif
    {{ $label }}
</span>
