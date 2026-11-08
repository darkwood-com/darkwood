<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $selected['title'] }} — Slidewire</title>
    @vite(['resources/css/app.css', 'resources/js/catalogue.js'])
</head>
<body class="sw-library theme-light">
<a class="library-skip" href="#presentation">Aller à la présentation</a>
<header class="library-header">
    <a class="library-brand" href="{{ route('home') }}" aria-label="Slidewire — accueil"><span class="library-logo"><img src="/darkwood/logos/dw512x512-uni-dark.png" width="32" height="32" alt="Darkwood"></span><span>Slidewire</span></a>
    <span class="library-section-label">Présentations</span>
    <div class="library-actions">
        <button id="theme-switcher" type="button" aria-label="Changer de thème" title="Changer de thème"><span id="theme-icon" aria-hidden="true"></span></button>
        <button id="library-menu" type="button" aria-expanded="false" aria-controls="library-sidebar" aria-label="Ouvrir les présentations"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path class="menu-open" d="M3 6h18M3 12h18M3 18h18"/><path class="menu-close" d="m6 6 12 12M18 6 6 18"/></svg></button>
    </div>
</header>
<div class="library-layout">
    <aside id="library-sidebar" class="library-sidebar" aria-label="Bibliothèque de présentations">
        <h2>Présentations <span>{{ count($presentations) }}</span></h2>
        <nav aria-label="Choisir une présentation"><ul>
        @foreach ($presentations as $presentation)
            <li data-presentation-id="{{ $presentation['id'] }}">
                <a href="{{ route('home', ['presentation' => $presentation['source_slug']]) }}" data-deck-url="{{ route('slidewire.'.$presentation['source_slug']) }}" data-title="{{ $presentation['title'] }}" @if($selected['id'] === $presentation['id']) aria-current="page" @endif><span>{{ $presentation['title'] }}</span><small>{{ $presentation['source_slug'] }}</small></a>
            </li>
        @endforeach
        </ul></nav>
    </aside>
    <main id="presentation" class="library-main" tabindex="-1">
        <div class="library-heading"><div><p>Bibliothèque Darkwood</p><h1 id="deck-title">{{ $selected['title'] }}</h1></div><a id="deck-direct" href="{{ route('slidewire.'.$selected['source_slug']) }}" target="_blank" rel="noopener">Ouvrir la présentation <span aria-hidden="true">↗</span></a></div>
        <div class="library-player"><iframe id="deck-player" src="{{ route('slidewire.'.$selected['source_slug']) }}" title="{{ $selected['title'] }} — présentation Slidewire" allow="fullscreen" allowfullscreen></iframe></div>
        <p class="library-hint">Utilisez les commandes de la présentation ou cliquez dans le lecteur pour naviguer au clavier.</p>
        <p id="library-announcement" class="library-sr" aria-live="polite"></p>
    </main>
</div>
</body>
</html>
