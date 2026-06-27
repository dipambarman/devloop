<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-primary to-accent border border-transparent rounded-lg font-semibold text-sm text-white shadow-lg shadow-primary/30 hover:shadow-primary/50 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-background active:scale-95 transition-all duration-200']) }}>
    {{ $slot }}
</button>
