@props(['background' => null])
@aware(['variant' => 'content'])

@php
    $surface = $background ?? match ($variant) {
        'cover', 'section' => 'blue',
        'cover-black' => 'black',
        default => 'white',
    };
    $file = match ($surface) {
        'blue' => 'dw512x512-uni-blue.png',
        'white' => 'dw512x512-uni-dark.png',
        'black' => 'dw512x512-uni-light.png',
        default => throw new InvalidArgumentException('Unknown Darkwood logo background: '.$surface),
    };
@endphp

<img src="{{ asset('darkwood/logos/'.$file) }}" alt="Darkwood" width="512" height="512" data-background="{{ $surface }}" {{ $attributes->class(['sw-logo']) }}>
