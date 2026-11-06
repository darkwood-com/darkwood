<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slidewire — Présentations</title>
    @vite('resources/css/app.css')
</head>
<body class="sw-catalogue">
<main>
    <h1>Slidewire</h1>
    <h2>Présentations</h2>
    <ul>
        @foreach ($presentations as $presentation)
            <li data-presentation-id="{{ $presentation['id'] }}"><a href="{{ route('slidewire.'.$presentation['source_slug']) }}"><span>{{ $presentation['title'] }}</span><x-darkwood.arrow /></a></li>
        @endforeach
    </ul>
</main>
</body>
</html>
