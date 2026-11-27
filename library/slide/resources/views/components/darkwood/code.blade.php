@props(['source' => null, 'language' => 'text', 'title' => null, 'preAttributes' => null])
<div {{ $attributes->class(['sw-code-window']) }} data-code-surface>
    @if ($title)
        <div class="sw-code-toolbar">{{ $title }}</div>
    @endif
    @if ($source !== null)
        @php
            $highlighted = app(\WendellAdriel\SlideWire\Support\CodeHighlighter::class)->highlight($source, $language, presentationTheme: 'darkwood')->toHtml();
            if ($preAttributes) {
                $highlighted = preg_replace('/<pre\b/', '<pre '.$preAttributes->toHtml(), $highlighted, 1);
            }
        @endphp
        {!! $highlighted !!}
    @else
        {{ $slot }}
    @endif
</div>
