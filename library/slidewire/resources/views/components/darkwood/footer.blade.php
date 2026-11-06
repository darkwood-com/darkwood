@props([
    'date' => '24-04-2026',
    'author' => '@matyo91',
    'label' => null,
    'logo' => true,
])

<footer class="dw-original-footer">
    <span>{{ $label ?? $date }}</span>
    <span class="dw-original-pagination" aria-label="Progression"
        x-text="`${String(index + 1).padStart(2, '0')} | ${String(count).padStart(2, '0')}`"></span>
    <span class="dw-original-footer-brand">
        <span>{{ $author }}</span>
        @if ($logo)
            <x-darkwood.logo />
        @endif
    </span>
</footer>
