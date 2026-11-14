@props([
    'tone' => 'accent', // blue | accent | teal | yellow | green | red | info
    'label' => null,
    'icon' => null,
])

@php
    $toneStyle = match($tone) {
        'teal' => 'background-color: var(--color-teal-soft); color: var(--color-teal);',
        'yellow' => 'background-color: var(--color-warning-soft); color: var(--color-warning);',
        'green' => 'background-color: var(--color-success-soft); color: var(--color-success);',
        'red' => 'background-color: var(--color-danger-soft); color: var(--color-danger);',
        'info' => 'background-color: var(--color-info-soft); color: var(--color-info);',
        'muted' => 'background-color: var(--color-surface-muted); color: var(--color-text-secondary);',
        default => 'background-color: var(--color-accent-soft); color: var(--color-accent-text);',
    };
@endphp

<span style="border-radius: var(--radius-badge); {{ $toneStyle }}" {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold tracking-tight whitespace-nowrap"]) }}>
    @if($icon)
        @if(str_contains($icon, ':'))
            <iconify-icon icon="{{ $icon }}" aria-hidden="true" class="text-[12px] shrink-0"></iconify-icon>
        @else
            <i class="{{ $icon }} text-[11px]"></i>
        @endif
    @endif
    <span>{{ $label ?? $slot }}</span>
</span>
