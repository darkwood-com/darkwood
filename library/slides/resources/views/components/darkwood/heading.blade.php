@props(['title', 'subtitle' => null, 'overline' => null, 'variant' => 'content', 'titleAttributes' => null, 'subtitleAttributes' => null, 'overlineAttributes' => null, 'headerAttributes' => null])

@php
    if (trim((string) $title) === '') {
        throw new InvalidArgumentException('A Darkwood slide requires a non-empty title.');
    }
    $titleClass = match ($variant) {
        'cover' => 'sw-title',
        'section' => 'sw-section-title',
        'conclusion' => 'sw-conclusion-title',
        default => 'sw-heading',
    };
    $inline = app(\App\Support\DarkwoodInlineContent::class);
@endphp

<header {{ ($headerAttributes ?? new \Illuminate\View\ComponentAttributeBag())->merge($attributes->getAttributes())->class(['sw-header', 'sw-header--'.$variant, 'sw-content-header' => $variant === 'content']) }}>
    @if (trim((string) $overline) !== '')
        <p {{ ($overlineAttributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['dw-overline', 'sw-overline']) }}>{{ $inline->render($overline) }}</p>
    @endif
    <h1 {{ ($titleAttributes ?? new \Illuminate\View\ComponentAttributeBag())->class([$titleClass]) }}>{{ $inline->render($title) }}</h1>
    @if (trim((string) $subtitle) !== '')
        <h2 {{ ($subtitleAttributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['sw-subtitle']) }}>{{ $inline->render($subtitle) }}</h2>
    @endif
</header>
