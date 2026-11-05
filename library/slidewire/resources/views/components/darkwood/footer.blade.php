@props([
    'date' => '24-04-2026',
    'author' => '@matyo91',
    'label' => null,
    'logo' => true,
])

<footer class="dw-original-footer">
    <span>{{ $label ?? $date }}</span>
    <span class="dw-original-footer-brand">
        <span>{{ $author }}</span>
        @if ($logo)
            <img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood">
        @endif
    </span>
</footer>
