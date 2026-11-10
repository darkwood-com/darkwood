<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="section" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--section',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-section-title dw-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="section" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Video Darkwood / IA x Game Dev
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
J'ai force 4 IA a recreer Mario de zero
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Mais au lieu de juste comparer les modeles, j'ai voulu comprendre comment orchestrer des agents IA pour produire un jeu.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-12 dw-flow">
                <div class="dw-node">Prompt</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Agents</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Tests</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Jeu jouable</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | hook" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Le probleme du vibe coding classique
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Prompt unique → IA → code fragile
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-grid dw-grid-2">
                <div class="dw-card"><h3>Collision bugs</h3><p>Le perso traverse le sol ou reste bloque.</p></div>
                <div class="dw-card"><h3>Niveaux impossibles</h3><p>Sauts injustes, progression cassee.</p></div>
                <div class="dw-card"><h3>Logique incoherente</h3><p>Regles qui se contredisent d'un fichier a l'autre.</p></div>
                <div class="dw-card"><h3>Dette technique immediate</h3><p>Chaque patch casse autre chose.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | probleme" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Stack IA
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Symfony AI: agents, tools, RAG, multi-modal pour PHP.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-grid dw-grid-2">
                <div class="dw-card"><h3>Agent</h3><p>Tool calling, subagents, orchestration.</p></div>
                <div class="dw-card"><h3>Platform</h3><p>Abstraction multi-modeles et streaming.</p></div>
                <div class="dw-card"><h3>Store</h3><p>RAG, indexation et recherche semantique.</p></div>
                <div class="dw-card"><h3>AI Bundle</h3><p>Configuration Symfony via YAML.</p></div>
            </div>
            <p class="dw-lead mt-8">Site: <a href="https://ai.symfony.com/" target="_blank" rel="noopener">ai.symfony.com</a></p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | symfony-ai" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Moteur de jeu
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Godot: moteur open-source parfait pour prototyper vite.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>2D solide</h3><p>Nodes et scenes pour platformers.</p></div>
                <div class="dw-card"><h3>Scriptable</h3><p>GDScript rapide a iterer.</p></div>
                <div class="dw-card"><h3>Open source</h3><p>Pipeline dev sans lock-in.</p></div>
            </div>
            <p class="dw-lead mt-8">Site: <a href="https://godotengine.org/" target="_blank" rel="noopener">godotengine.org</a></p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | godot" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Approche orchestration
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Un agent chef route vers des agents specialises.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-flow" style="display:flex;flex-direction:column;gap:16px;align-items:flex-start">
                <div class="dw-node">User Prompt</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Orchestrator Agent</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-grid dw-grid-2" style="width:100%">
                    <div class="dw-card"><h3>Layout Agent</h3></div>
                    <div class="dw-card"><h3>Gameplay Agent</h3></div>
                    <div class="dw-card"><h3>Godot Export Agent</h3></div>
                    <div class="dw-card"><h3>QA Agent</h3></div>
                </div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Generated Game / Level JSON</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | architecture" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Le script Symfony AI
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Pattern: Agent + Handoff + MultiAgent + MessageBag
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



@endverbatim
@php
$snippet = <<<'SW_CODE_5_0'
$messages = MessageBag::of(Message::ofUser($prompt));

$orchestrator = new Agent(...);
$layout = new Agent(...);
$gameplay = new Agent(...);
$export = new Agent(...);
$qa = new Agent(...);

$flow = MultiAgent::of($orchestrator)
    ->handoff($layout)
    ->handoff($gameplay)
    ->handoff($export)
    ->handoff($qa);

$result = $flow->run($messages);
SW_CODE_5_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="dw-lead mt-8">Idee cle: un agent chef route vers des agents specialises avec un contexte partage.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | symfony-ai" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Les agents
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
4 roles complementaires
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-grid dw-grid-2">
                <div class="dw-card"><h3>Layout Agent</h3><p>Construit le niveau.</p></div>
                <div class="dw-card"><h3>Gameplay Agent</h3><p>Ajoute les mecaniques.</p></div>
                <div class="dw-card"><h3>Godot Export Agent</h3><p>Genere les fichiers exploitables.</p></div>
                <div class="dw-card"><h3>QA Agent</h3><p>Verifie la jouabilite.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | agents" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Les 4 IA testees
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Meme prompt initial. Resultats tres differents.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-grid dw-grid-4" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px">
                <div class="dw-card"><h3>ChatGPT</h3></div>
                <div class="dw-card"><h3>Claude</h3></div>
                <div class="dw-card"><h3>Gemini</h3></div>
                <div class="dw-card"><h3>Grok</h3></div>
            </div>
            <p class="dw-lead mt-10">Objectif: comparer creativite, stabilite, cout et jouabilite.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | benchmark" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Generation automatique
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
La generation se fait en une seule commande Symfony.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



@endverbatim
@php
$snippet = <<<'SW_CODE_8_0'
php bin/console app:mario:orchestrate
SW_CODE_8_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-10 dw-flow">
                <div class="dw-node">Prompt</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Multi-agent</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Export jeu</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | commande" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Demo ChatGPT
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
ChatGPT - bonne base, iteration rapide
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8" style="height:66vh;border:var(--dw-border-width) solid var(--dw-blue);border-radius:16px;overflow:hidden">
                <iframe src="http://localhost:8062/Super%20Mario%20Bros.html" title="Demo ChatGPT Mario" style="width:100%;height:100%;border:0" loading="eager"></iframe>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | chatgpt" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Demo Claude
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Claude - meilleur raisonnement, plus cher
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8" style="height:66vh;border:var(--dw-border-width) solid var(--dw-blue);border-radius:16px;overflow:hidden">
                <iframe src="http://localhost:8061/Super%20mario%20bros.html" title="Demo Claude Mario" style="width:100%;height:100%;border:0" loading="eager"></iframe>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | claude" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Demo Gemini
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Gemini - tres bon rendu visuel et creativite
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8" style="height:66vh;border:var(--dw-border-width) solid var(--dw-blue);border-radius:16px;overflow:hidden">
                <iframe src="http://localhost:8063/Solar%20Engine.html" title="Demo Gemini Mario" style="width:100%;height:100%;border:0" loading="eager"></iframe>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | gemini" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Demo Grok
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Grok - interessant, mais instable
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8" style="height:66vh;border:var(--dw-border-width) solid var(--dw-blue);border-radius:16px;overflow:hidden">
                <iframe src="http://localhost:8060/Platformer.html" title="Demo Grok Mario" style="width:100%;height:100%;border:0" loading="eager"></iframe>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | grok" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Ce que l'experience montre
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Le vrai sujet n'est pas "quelle IA code le mieux".
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-flow" style="display:flex;flex-direction:column;gap:14px;align-items:flex-start">
                <div class="dw-node">Cadrer</div>
                <div class="dw-node">Orchestrer</div>
                <div class="dw-node">Tester</div>
                <div class="dw-node">Corriger</div>
            </div>
            <p class="dw-lead mt-10">L'efficacite vient du systeme d'agents, pas d'un prompt unique.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | enseignements" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Conclusion
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Le futur du vibe coding, c'est l'orchestration
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Un seul agent peut produire un prototype. Plusieurs agents bien cadres peuvent produire un systeme.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-12 dw-card">
                <h3>CTA</h3>
                <p>Abonne-toi si tu veux voir la suite avec Godot, Symfony AI et orchestration d'agents.</p>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="15 | conclusion" />
</x-darkwood.slide>
</x-darkwood.deck>
