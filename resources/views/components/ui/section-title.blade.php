@props([
    'title' => '',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-0.5']) }}>
    <h2 class="m-0 text-[15px] font-bold text-[#1B1913] tracking-tight">
        {{ $title ?? $slot }}
    </h2>
    @if($subtitle)
        <p class="m-0 text-[12px] text-[#8E8B84]">
            {{ $subtitle }}
        </p>
    @endif
</div>
