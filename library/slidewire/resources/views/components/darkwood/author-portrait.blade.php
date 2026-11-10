@props(['authorId' => null, 'background' => null])
@php($author = config('authors')[$authorId ?? ''] ?? null)
@if ($author && isset($author['portrait']))
    <img src="{{ asset($author['portrait']) }}" alt="Portrait de {{ $author['name'] }}" width="640" height="640" data-author-id="{{ $authorId }}" {{ $attributes->class(['sw-author-portrait']) }}>
@else
    <x-darkwood.logo :background="$background" data-author-fallback="logo" :data-author-id="$authorId" {{ $attributes }} />
@endif
