@props([
    'title',
    'subtitle' => null,
    'overline' => null,
    'date' => '24-04-2026',
    'author' => '@matyo91',
])

<x-darkwood.slide variant="cover" {{ $attributes }}>
    @if ($overline)
        <p class="dw-original-overline">{{ $overline }}</p>
    @endif
    <h1 class="dw-original-title">{{ $title }}</h1>
    @if ($subtitle)
        <p class="dw-original-lead">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
    <x-darkwood.footer :date="$date" :author="$author" />
</x-darkwood.slide>
