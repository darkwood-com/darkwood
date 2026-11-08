@props([
    'title',
    'subtitle' => null,
    'lead' => null,
    'overline' => null,
    'label' => null,
    'page' => null,
    'pages' => null,
    'top' => true,
])

<x-darkwood.slide variant="content" :top="$top" {{ $attributes }}>
    <x-darkwood.heading :title="$title" :subtitle="$subtitle" :overline="$overline" />
    <div class="sw-body">
        <div class="sw-body-inner">
            @if ($lead)
                <p class="sw-lead">{{ $lead }}</p>
            @endif
            @if (trim((string) $slot) !== '')
                <div class="sw-content">{{ $slot }}</div>
            @endif
        </div>
    </div>
    <x-darkwood.footer :label="$label" :page="$page" :pages="$pages" />
</x-darkwood.slide>
