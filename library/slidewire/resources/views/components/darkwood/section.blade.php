@props([
    'title',
    'subtitle' => null,
    'overline' => null,
    'label' => null,
])

<x-darkwood.slide variant="section" {{ $attributes }}>
    @if ($overline)
        <p class="dw-original-overline">{{ $overline }}</p>
    @endif
    <h2 class="dw-original-section-title">{{ $title }}</h2>
    @if ($subtitle)
        <p class="dw-original-lead">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
    <x-darkwood.footer :label="$label" />
</x-darkwood.slide>
