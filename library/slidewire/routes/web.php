<?php

use App\Http\Controllers\PublicationController;
use App\Support\PresentationCatalog;
use Illuminate\Support\Facades\Route;

Route::get('/publications', [PublicationController::class, 'index']);
Route::post('/publications/associate', [PublicationController::class, 'associate']);

Route::get('/', function (PresentationCatalog $catalog) {
    return view('catalogue', ['presentations' => $catalog->entries()]);
})->name('home');

foreach (app(PresentationCatalog::class)->entries() as $presentation) {
    Route::slidewire('/slides/'.$presentation['source_slug'], $presentation['source_slug']);
}
