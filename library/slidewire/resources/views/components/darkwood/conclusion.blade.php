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
            <x-darkwood.logo background="blue" />
        </div>
        <div class="dw-original-thanks-copy">
            <x-darkwood.heading :title="$title" :subtitle="$subtitle" variant="conclusion" />
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
