@props([
    'variant' => 'content',
    'top' => false,
    'transition' => 'none',
    'canvasAttributes' => null,
])

<x-slidewire::slide
    :transition="$transition"
    {{ $attributes->class([
        'sw-slide',
        'sw-slide--'.$variant,
    ]) }}
>
    <section {{ ($canvasAttributes ?? new \Illuminate\View\ComponentAttributeBag())->class([
        'sw-canvas',
        'sw-canvas--top' => $top,
    ]) }}>
        {{ $slot }}
    </section>
</x-slidewire::slide>
