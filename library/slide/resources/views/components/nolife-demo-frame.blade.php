@props([
    'url',
    'title' => 'NoLife Models',
])

<div {{ $attributes->class(['dw-embed', 'dw-embed-demo', 'dw-embed--live']) }}>
    <iframe
        src="{{ $url }}"
        title="{{ $title }}"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        allow="clipboard-read; clipboard-write"
    ></iframe>
</div>
{{--<p class="dw-note dw-embed-hint">
    These URLs use NoLife <strong>/embed/…</strong> (no Stimulus / Turbo / LiveComponent) so they load in iframes.
    If the frame is still empty, open
    <span class="font-mono text-[var(--dw-lime)]">{{ $url }}</span> in a separate window.
</p>--}}
