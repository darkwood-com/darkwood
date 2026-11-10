@props(['source' => null, 'title' => null])
<x-darkwood.code language="text" :source="$source ?? html_entity_decode(trim((string) $slot), ENT_QUOTES | ENT_HTML5)" :title="$title" {{ $attributes->class(['sw-terminal']) }} />
