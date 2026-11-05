@props(['count' => 2])

<div class="dw-original-columns" style="--darkwood-columns: {{ (int) $count }}">
    {{ $slot }}
</div>
