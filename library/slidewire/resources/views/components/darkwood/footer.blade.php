@props([
    'date' => '24-04-2026',
    'author' => '@matyo91',
    'label' => null,
    'page' => null,
    'pages' => null,
    'logo' => true,
])

<footer class="sw-footer">
    <span class="sw-footer-label">@if ($page !== null && $pages !== null){{ sprintf('%02d | %02d', $page, $pages) }}@if ($label) · {{ $label }}@endif@else{{ $label ?? $date }}@endif</span>
    <span class="sw-footer-brand">
        <span>{{ $author }}</span>
        @if ($logo)
            <x-darkwood.logo />
        @endif
    </span>
</footer>
