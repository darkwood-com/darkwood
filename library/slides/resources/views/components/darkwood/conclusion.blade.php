@props([
    'title' => 'Thanks!',
    'subtitle' => null,
    'name' => null,
    'authorId' => null,
    'contact' => '@matyo91',
    'label' => '24-04-2026',
])

@php($resolvedAuthorId = $authorId ?? ltrim((string) $contact, '@'))

<x-darkwood.slide variant="conclusion" {{ $attributes }}>
    <div class="sw-thanks">
        <div @class(['sw-thanks-mark', 'sw-thanks-mark--author' => isset(config('authors')[$resolvedAuthorId]['portrait'])])>
            <x-darkwood.author-portrait :author-id="$resolvedAuthorId" background="blue" />
        </div>
        <div class="sw-thanks-copy">
            <x-darkwood.heading :title="$title" :subtitle="$subtitle" variant="conclusion" />
            @if ($name)
                <p>Hello!<br>I am <span class="sw-accent">{{ $name }}</span></p>
            @endif
            @if ($contact)
                <p><span class="sw-accent">{{ $contact }}</span></p>
            @endif
            {{ $slot }}
        </div>
    </div>
    <x-darkwood.footer :label="$label" />
</x-darkwood.slide>
