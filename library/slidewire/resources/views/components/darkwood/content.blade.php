@props([
    'title',
    'lead' => null,
    'overline' => null,
    'label' => null,
    'top' => true,
])

<x-darkwood.slide variant="content" :top="$top" {{ $attributes }}>
    <header class="dw-original-content-header">
        @if ($overline)
            <p class="dw-original-overline">{{ $overline }}</p>
        @endif
        <h2 class="dw-original-heading">{{ $title }}</h2>
    </header>
    @if ($lead)
        <p class="dw-original-lead">{{ $lead }}</p>
    @endif
    @if (trim((string) $slot) !== '')
        <div class="dw-original-content">{{ $slot }}</div>
    @endif
    <x-darkwood.footer :label="$label" />
</x-darkwood.slide>
