@props([
    'title',
    'subtitle' => null,
    'lead' => null,
    'overline' => null,
    'label' => null,
    'top' => true,
])

<x-darkwood.slide variant="content" :top="$top" {{ $attributes }}>
    <x-darkwood.heading :title="$title" :subtitle="$subtitle" :overline="$overline" />
    <div class="dw-original-body">
        <div class="dw-original-body-inner">
            @if ($lead)
                <p class="dw-original-lead">{{ $lead }}</p>
            @endif
            @if (trim((string) $slot) !== '')
                <div class="dw-original-content">{{ $slot }}</div>
            @endif
        </div>
    </div>
    <x-darkwood.footer :label="$label" />
</x-darkwood.slide>
