@props(['value'])

<label {{ $attributes->merge(['class' => 'block mb-2 text-sm font-medium text-secondary-text']) }}>
    {{ $value ?? $slot }}
</label>
