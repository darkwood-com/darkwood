@props([
    'title',
    'subtitle' => null,
    'overline' => null,
    'date' => '24-04-2026',
    'author' => '@matyo91',
    'background' => 'blue',
])

<x-darkwood.slide :variant="$background === 'black' ? 'cover-black' : 'cover'" {{ $attributes }}>
    <x-darkwood.heading :title="$title" :subtitle="$subtitle" :overline="$overline" variant="cover" />
    {{ $slot }}
    <x-darkwood.footer :date="$date" :author="$author" />
</x-darkwood.slide>
