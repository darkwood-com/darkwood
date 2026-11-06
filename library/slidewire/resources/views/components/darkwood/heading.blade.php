@props(['title', 'subtitle' => null, 'overline' => null, 'variant' => 'content'])

@php
    if (trim((string) $title) === '') {
        throw new InvalidArgumentException('A Darkwood slide requires a non-empty title.');
    }
    $titleClass = match ($variant) {
        'cover' => 'dw-original-title',
        'section' => 'dw-original-section-title',
        'conclusion' => 'dw-original-conclusion-title',
        default => 'dw-original-heading',
    };
@endphp

<header @class(['dw-original-header', 'dw-original-header--'.$variant, 'dw-original-content-header' => $variant === 'content'])>
    @if (trim((string) $overline) !== '')
        <p class="dw-overline dw-original-overline">{{ $overline }}</p>
    @endif
    <h1 class="{{ $titleClass }}">{{ $title }}</h1>
    @if (trim((string) $subtitle) !== '')
        <h2 class="dw-original-subtitle">{{ $subtitle }}</h2>
    @endif
</header>
