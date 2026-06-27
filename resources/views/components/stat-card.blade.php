@props(['title', 'value', 'icon', 'trend' => null, 'trendUp' => true])

<x-card class="hover:glass-hover transition-all duration-300">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-secondary-text mb-1">{{ $title }}</p>
            <h3 class="text-2xl font-bold text-primary-text">{{ $value }}</h3>
            
            @if($trend)
                <div class="mt-2 flex items-center text-xs">
                    @if($trendUp)
                        <svg class="w-4 h-4 text-teal-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span class="text-teal-500 font-medium">{{ $trend }}</span>
                    @else
                        <svg class="w-4 h-4 text-red-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                        <span class="text-red-500 font-medium">{{ $trend }}</span>
                    @endif
                    <span class="text-tertiary-text ml-1">vs last month</span>
                </div>
            @endif
        </div>
        
        @if(isset($icon))
            <div class="w-12 h-12 rounded-xl bg-surface-hover border border-border flex items-center justify-center text-primary shadow-inner">
                {{ $icon }}
            </div>
        @endif
    </div>
</x-card>
