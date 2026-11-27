<?php

use App\Support\PresentationCatalog;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function (PresentationCatalog $catalog, Request $request) {
    $presentations = $catalog->entries();
    $slug = $request->query('presentation');
    abort_if($slug !== null && (! is_string($slug) || ! in_array($slug, array_column($presentations, 'source_slug'), true)), 404);
    $selected = collect($presentations)->firstWhere('source_slug', $slug) ?? $presentations[0];

    return view('catalogue', compact('presentations', 'selected'));
})->name('home');

foreach (app(PresentationCatalog::class)->entries() as $presentation) {
    Route::slidewire('/slides/'.$presentation['source_slug'], $presentation['source_slug']);
}
