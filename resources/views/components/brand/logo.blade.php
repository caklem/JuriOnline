@props([
    'variant' => 'light',
    'size' => 'md',
    'tile' => false,
    'descriptor' => false,
    'href' => null,
])

@php
    $classes = 'jurion-logo jurion-logo--' . $size . ' jurion-logo--' . $variant
        . ($tile ? ' jurion-logo--tile' : '');
@endphp

@if ($href)
<a href="{{ $href }}" {{ $attributes->class($classes) }}>
@else
<span {{ $attributes->class($classes) }}>
@endif
    <img
        src="{{ asset('images/brand/jurion-mark.png') }}"
        alt=""
        width="512"
        height="512"
        class="jurion-logo__mark"
        decoding="async"
    >
    <span class="jurion-logo__text">
        <span class="jurion-wordmark">JURI<span class="jurion-dot">.</span>ON</span>
        @if ($descriptor)
            <span class="jurion-descriptor">Juri Online</span>
        @endif
    </span>
@if ($href)
</a>
@else
</span>
@endif
