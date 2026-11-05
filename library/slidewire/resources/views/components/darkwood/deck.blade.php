@props([
    'transition' => 'none',
    'transitionSpeed' => 'default',
])

<x-slidewire::deck
    theme="darkwood-original"
    :transition="$transition"
    :transition-speed="$transitionSpeed"
    show-progress="true"
    show-controls="true"
    show-fullscreen-button="true"
    {{ $attributes }}
>
    {{ $slot }}
</x-slidewire::deck>
