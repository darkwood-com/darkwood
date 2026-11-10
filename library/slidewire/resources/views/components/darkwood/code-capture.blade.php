@props(['source', 'language' => 'text', 'title' => null])
<x-darkwood.code :source="$source" :language="$language" :title="$title" {{ $attributes }} />
