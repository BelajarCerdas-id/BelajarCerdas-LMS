@props([
    'tone' => 'muted', // muted | accent | info | teal | success | warning | danger
    'icon' => null,
    'size' => 'md', // sm (28px) | md (36px) | lg (44px)
])

@php
    $sizeClasses = match($size) {
        'sm' => 'w-7 h-7 text-[13px]',
        'lg' => 'w-11 h-11 text-[20px]',
        default => 'w-9 h-9 text-[16px]',
    };

    $toneStyle = match($tone) {
        'accent' => 'background-color: var(--color-accent-soft); color: var(--color-accent);',
        'info' => 'background-color: var(--color-info-soft); color: var(--color-info);',
        'teal' => 'background-color: var(--color-teal-soft); color: var(--color-teal);',
        'success' => 'background-color: var(--color-success-soft); color: var(--color-success);',
        'warning' => 'background-color: var(--color-warning-soft); color: var(--color-warning);',
        'danger' => 'background-color: var(--color-danger-soft); color: var(--color-danger);',
        default => 'background-color: var(--color-surface-muted); color: var(--color-text-secondary);',
    };
@endphp

<span style="border-radius: var(--radius-tile); {{ $toneStyle }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center shrink-0 $sizeClasses"]) }} aria-hidden="true">
    @if($icon)
        @if(str_contains($icon, ':'))
            <iconify-icon icon="{{ $icon }}"></iconify-icon>
        @else
            <i class="{{ $icon }}"></i>
        @endif
    @else
        {{ $slot }}
    @endif
</span>
