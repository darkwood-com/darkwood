@props(['title', 'subtitle' => null, 'overline' => null, 'variant' => 'content', 'titleAttributes' => null, 'subtitleAttributes' => null, 'overlineAttributes' => null, 'headerAttributes' => null])

@php
    if (trim((string) $title) === '') {
        throw new InvalidArgumentException('A Darkwood slide requires a non-empty title.');
    }
    $titleClass = match ($variant) {
        'cover' => 'dw-original-title',
        'section' => 'dw-original-section-title',
        'conclusion' => 'dw-original-conclusion-title',
        default => 'dw-original-heading',
    };
    $inline = app(\App\Support\DarkwoodInlineContent::class);
@endphp

<header {{ ($headerAttributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['dw-original-header', 'dw-original-header--'.$variant, 'dw-original-content-header' => $variant === 'content']) }}>
    @if (trim((string) $overline) !== '')
        <p {{ ($overlineAttributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['dw-overline', 'dw-original-overline']) }}>{{ $inline->render($overline) }}</p>
    @endif
    <h1 {{ ($titleAttributes ?? new \Illuminate\View\ComponentAttributeBag())->class([$titleClass]) }}>{{ $inline->render($title) }}</h1>
    @if (trim((string) $subtitle) !== '')
        <h2 {{ ($subtitleAttributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['dw-original-subtitle']) }}>{{ $inline->render($subtitle) }}</h2>
    @endif
</header>
