@props([
    'padding' => 'md', // sm | md | none
    'tag' => 'section',
])

@php
    $padClasses = match($padding) {
        'sm' => 'p-3',
        'none' => 'p-0',
        default => 'p-4 md:p-5',
    };
@endphp

<{{ $tag }} style="border-radius: var(--radius-card);" {{ $attributes->merge(['class' => "card-dpai $padClasses"]) }}>
    {{ $slot }}
</{{ $tag }}>
