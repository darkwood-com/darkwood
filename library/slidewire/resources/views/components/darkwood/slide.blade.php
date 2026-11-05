@props([
    'variant' => 'content',
    'top' => false,
    'transition' => 'none',
])

<x-slidewire::slide
    :transition="$transition"
    {{ $attributes->class([
        'dw-original-slide',
        'dw-original-slide--'.$variant,
    ]) }}
>
    <section @class([
        'dw-original-canvas',
        'dw-original-canvas--top' => $top,
    ])>
        {{ $slot }}
    </section>
</x-slidewire::slide>
