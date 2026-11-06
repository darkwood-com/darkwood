@props([
    'date' => '24-04-2026',
    'author' => '@matyo91',
    'label' => null,
    'logo' => true,
])

<footer class="sw-footer">
    <span class="sw-footer-label">{{ $label ?? $date }}</span>
    <span class="sw-footer-brand">
        <span>{{ $author }}</span>
        @if ($logo)
            <x-darkwood.logo />
        @endif
    </span>
</footer>
