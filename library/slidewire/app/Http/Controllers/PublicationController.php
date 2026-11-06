<?php

namespace App\Http\Controllers;

use App\Models\Presentation;
use App\Models\PublicationAttempt;
use App\Support\PublicationAssociations;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    private function guard(Request $request): void
    {
        // Operator tooling is local-only until a real authenticated admin exists.
        abort_unless(app()->environment(['local', 'testing']) && in_array($request->ip(), ['127.0.0.1', '::1'], true), 404);
    }

    public function index(Request $request)
    {
        $this->guard($request);

        return view('publications.index', ['presentations' => Presentation::with('publications')->orderBy('title')->get(), 'attempts' => PublicationAttempt::where('active_slot', 1)->get()]);
    }

    public function associate(Request $request, PublicationAssociations $associations)
    {
        $this->guard($request);
        $values = $request->validate(['id' => 'required|uuid|exists:presentations,id', 'provider' => 'required|in:speakerdeck,slideshare', 'remote_id' => 'required|string|max:255', 'url' => 'required|url:https|max:2048', 'confirmed' => 'accepted']);
        try {
            $associations->associate(Presentation::findOrFail($values['id']), $values['provider'], $values['remote_id'], $values['url'], true);
        } catch (\InvalidArgumentException $error) {
            return back()->withErrors(['association' => $error->getMessage()]);
        }

        return redirect('/publications')->with('status', 'Association enregistrée. Le PDF distant reste à vérifier.');
    }
}
