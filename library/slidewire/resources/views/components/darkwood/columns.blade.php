@props(['count' => 2])

<div class="sw-columns" style="--darkwood-columns: {{ (int) $count }}">
    {{ $slot }}
</div>
