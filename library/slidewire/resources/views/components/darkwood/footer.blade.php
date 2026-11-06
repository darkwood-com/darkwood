@props([
    'date' => '24-04-2026',
    'author' => '@matyo91',
    'label' => null,
    'logo' => true,
])

<footer class="dw-original-footer">
    <span class="dw-original-footer-label">{{ $label === null ? $date : preg_replace('/^(\d+)\s*\/\s*(.+)$/u', '$1 | $2', $label) }}</span>
    <span class="dw-original-footer-brand">
        <span>{{ $author }}</span>
        @if ($logo)
            <x-darkwood.logo />
        @endif
    </span>
</footer>
