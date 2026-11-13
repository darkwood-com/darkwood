<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Publications Slidewire</title>
@vite('resources/css/app.css')
<style>body{font-family:Verdana,sans-serif;max-width:1100px;margin:40px auto;padding:0 24px;color:#000;background:#fff}h1{font-size:32px;font-weight:bold}h2{font-size:24px;font-weight:bold}a{color:var(--dw-blue);text-decoration:underline}section{border-top:var(--dw-border-width) solid #000;padding:20px 0}label{display:block;margin:16px 0}input,select,button{font:inherit;padding:8px;border:var(--dw-border-width) solid #000;background:#fff;color:#000}button{background:var(--dw-blue);color:#fff}code{overflow-wrap:anywhere}li{margin:12px 0}</style></head>
<body><h1>Publications Slidewire</h1><p>Suivi local des destinations de publication. Slidewire conserve les sources canoniques.</p>
<p>Speaker Deck : publication et remplacement assistés dans le navigateur. SlideShare : uploads temporairement indisponibles (audit du 6 novembre 2026). Aucun upload automatique.</p>
@if(session('status'))<p role="status">{{ session('status') }}</p>@endif
@if($errors->any())<ul role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
@forelse($presentations as $deck)
<section><h2>{{ $deck->title }}</h2><p>{{ $deck->slide_count }} slides · <code>{{ $deck->id }}</code> · <a href="{{ url('/slides/'.$deck->source_slug) }}">Lire</a></p>
<ul>@foreach(['speakerdeck' => 'Speaker Deck', 'slideshare' => 'SlideShare'] as $provider => $label)
@php($publication = $deck->publications->firstWhere('provider', $provider))
<li><strong>{{ $label }}</strong> : {{ match($publication?->status) { 'published' => 'Publiée (upload attesté)', 'update_available' => 'Mise à jour disponible', 'manual_action_required', 'manual_verification_required' => 'Action ou vérification manuelle requise', 'error' => 'Erreur', default => 'Non publiée' } }}
@if($publication?->remote_url)<a href="{{ $publication->remote_url }}" target="_blank" rel="noopener noreferrer">Publication publique</a>@endif
@if($publication?->last_error)<p>{{ $publication->last_error }}</p>@endif</li>
@endforeach</ul>
<details><summary>Associer une publication existante</summary><p>Confirmer la correspondance éditoriale et relever l’identifiant réel. Le titre ou le slug seuls ne suffisent pas.</p>
<form method="post" action="{{ url('/publications/associate') }}">@csrf<input type="hidden" name="id" value="{{ $deck->id }}">
<label>Plateforme <select name="provider"><option value="speakerdeck">Speaker Deck</option><option value="slideshare">SlideShare</option></select></label>
<label>Identifiant distant <input required name="remote_id"></label><label>URL publique <input required name="url" type="url"></label>
<label><input type="checkbox" name="confirmed" value="1" required> Je confirme que cette publication correspond à cette présentation Slidewire.</label><button>Enregistrer l’association</button></form></details>
<details><summary>Préparer le PDF</summary><p>Depuis l’environnement Nix du projet : <code>php artisan publications:export {{ $deck->id }}</code>. Vérifier le PDF avant de réserver une tentative avec <code>publications:prepare</code>. La réservation ne publie rien.</p></details>
</section>
@empty<p>Exécuter les migrations puis <code>php artisan publications:catalog</code> pour initialiser l’inventaire.</p>@endforelse
@if($attempts->isNotEmpty())<h2>Tentatives à réconcilier</h2><p>Ne pas recommencer un upload sans vérifier son résultat sur la plateforme.</p><ul>@foreach($attempts as $attempt)<li><code>{{ $attempt->id }}</code> — {{ $attempt->status }} — SHA-256 <code>{{ $attempt->source_hash }}</code></li>@endforeach</ul>@endif
</body></html>
