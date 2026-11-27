@props([
    'transition' => 'none',
    'transitionSpeed' => 'default',
    'showControls' => true,
    'showProgress' => true,
    'showFullscreenButton' => true,
])

<x-slidewire::deck
    theme="darkwood"
    :transition="$transition"
    :transition-speed="$transitionSpeed"
    :show-progress="$showProgress"
    :show-controls="$showControls"
    :show-fullscreen-button="$showFullscreenButton"
    {{ $attributes }}
>
    {{ $slot }}
</x-slidewire::deck>
