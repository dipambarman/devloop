@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full bg-surface border border-border text-primary-text text-sm rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-primary block p-2.5 outline-none transition-all duration-200 shadow-sm placeholder-secondary-text disabled:opacity-50 disabled:cursor-not-allowed']) }}>
