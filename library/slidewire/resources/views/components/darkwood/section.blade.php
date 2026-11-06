@props([
    'title',
    'subtitle' => null,
    'overline' => null,
    'label' => null,
])

<x-darkwood.slide variant="section" {{ $attributes }}>
    <x-darkwood.heading :title="$title" :subtitle="$subtitle" :overline="$overline" variant="section" />
    {{ $slot }}
    <x-darkwood.footer :label="$label" />
</x-darkwood.slide>
