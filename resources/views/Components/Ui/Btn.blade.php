@props([
    'variant' => 'primary', // primary | dark | neutral | outline | plain | ghost | danger | accent-soft
    'shape' => 'rounded',   // rounded | pill
    'icon' => null,
    'label' => null,
    'href' => null,
    'type' => 'button',
])

@php
    $baseClasses = "inline-flex items-center justify-center gap-2 h-[38px] px-4 font-semibold text-[13px] cursor-pointer whitespace-nowrap transition-all duration-150 active:scale-[0.98]";

    $shapeStyle = match($shape) {
        'pill' => 'border-radius: var(--radius-btn-pill);',
        default => 'border-radius: var(--radius-btn);',
    };

    $variantClasses = match($variant) {
        'dark' => 'btn-dpai-dark',
        'neutral' => 'btn-dpai-neutral',
        'outline' => 'btn-dpai-outline',
        'plain' => 'btn-dpai-plain',
        'ghost' => 'btn-dpai-ghost',
        'danger' => 'btn-dpai-danger',
        'accent-soft' => 'bg-dpai-accent-soft text-dpai-accent-text hover:opacity-90',
        default => 'btn-dpai-primary',
    };
@endphp

@if($href)
    <a href="{{ $href }}" style="{{ $shapeStyle }}" {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
        @if($icon)
            @if(str_contains($icon, ':'))
                <iconify-icon icon="{{ $icon }}" aria-hidden="true" class="text-[15px] shrink-0"></iconify-icon>
            @else
                <i class="{{ $icon }} text-[14px]"></i>
            @endif
        @endif
        <span>{{ $label ?? $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" style="{{ $shapeStyle }}" {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
        @if($icon)
            @if(str_contains($icon, ':'))
                <iconify-icon icon="{{ $icon }}" aria-hidden="true" class="text-[15px] shrink-0"></iconify-icon>
            @else
                <i class="{{ $icon }} text-[14px]"></i>
            @endif
        @endif
        <span>{{ $label ?? $slot }}</span>
    </button>
@endif
