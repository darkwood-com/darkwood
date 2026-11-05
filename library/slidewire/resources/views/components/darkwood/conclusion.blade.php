@props([
    'title' => 'Thanks!',
    'subtitle' => null,
    'name' => null,
    'contact' => '@matyo91',
    'label' => '24-04-2026',
])

<x-darkwood.slide variant="conclusion" {{ $attributes }}>
    <div class="dw-original-thanks">
        <div class="dw-original-thanks-mark">
            <img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood">
        </div>
        <div class="dw-original-thanks-copy">
            <h2>{{ $title }}</h2>
            @if ($subtitle)
                <p>{{ $subtitle }}</p>
            @endif
            @if ($name)
                <p>Hello!<br>I am <span class="dw-original-accent">{{ $name }}</span></p>
            @endif
            @if ($contact)
                <p><span class="dw-original-accent">{{ $contact }}</span></p>
            @endif
            {{ $slot }}
        </div>
    </div>
    <x-darkwood.footer :label="$label" />
</x-darkwood.slide>
