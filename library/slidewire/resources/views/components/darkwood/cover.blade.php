@props([
    'title',
    'subtitle' => null,
    'overline' => null,
    'date' => '24-04-2026',
    'author' => '@matyo91',
    'background' => 'blue',
])

<x-darkwood.slide :variant="$background === 'black' ? 'cover-black' : 'cover'" {{ $attributes }}>
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
