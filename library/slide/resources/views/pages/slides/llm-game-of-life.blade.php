<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="cover" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--cover',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-title dw-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="cover" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
LLM Game of Life
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Construire un LLM
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Inspiré des concepts réels des LLM, ce projet propose une version visuelle et simplifiée basée sur un Game of Life.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <blockquote class="twitter-tweet"><p lang="en" dir="ltr">This 2-hour Stanford lecture breaks down how models like ChatGPT and Claude are actually built, clearer than what many people in top AI roles ever get exposed to.<br><br>Save this and set aside two hours today. It might end up being the most valuable thing you learn all week. <a href="https://t.co/5u97uZCWxd">pic.twitter.com/5u97uZCWxd</a></p>— Allen Braden (@allen_explains) <a href="https://twitter.com/allen_explains/status/2044757995549319172?ref_src=twsrc%5Etfw">April 16, 2026</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | introduction" />
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
LLM en 30 secondes
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Un LLM prédit la suite.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Probabilité</h3><p>Il choisit ce qui semble le plus probable.</p></div>
                <div class="dw-card"><h3>Séquence</h3><p>Il avance étape par étape.</p></div>
                <div class="dw-card"><h3>Tokens</h3><p>Les mots sont découpés en petits morceaux.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | llm" />
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
Récap
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Les grandes idées à retenir.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-flow">
                <div class="dw-node">Tokens</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Probabilités</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Génération</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Training</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | concepts" />
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
Training
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Un modèle apprend avec plusieurs signaux.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Pretraining</h3><p>Explorer beaucoup de données.</p></div>
                <div class="dw-card"><h3>Fine-tuning</h3><p>Se rapprocher d'une cible.</p></div>
                <div class="dw-card"><h3>RLHF</h3><p>Préférer une réponse à une autre.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | training" />
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
Transition
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Et si on rendait tout ça visible ?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-flow">
                <div class="dw-node">Mots</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Pixels</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Animation</div>
            </div>
            <p class="dw-lead">Au lieu de générer du texte, on génère une grille qui bouge.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | transition" />
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
Projet
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Un LLM… mais avec des pixels.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-grid dw-grid-4">
                <div class="dw-chip">Game of Life</div>
                <div class="dw-chip">Programmes lambda</div>
                <div class="dw-chip">Algorithme génétique</div>
                <div class="dw-chip">Frames streamées</div>
            </div>
            <div class="mt-12 dw-pixel-grid" aria-hidden="true">
                                    <span class=""></span>
                                    <span class="is-on"></span>
                                    <span class=""></span>
                                    <span class=""></span>
                                    <span class="is-on"></span>
                                    <span class="is-on"></span>
                                    <span class=""></span>
                                    <span class="is-on"></span>
                                    <span class=""></span>
                                    <span class="is-on"></span>
                                    <span class="is-on"></span>
                                    <span class=""></span>
                                    <span class=""></span>
                                    <span class=""></span>
                                    <span class="is-on"></span>
                                    <span class=""></span>
                            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | overview" />
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
Mapping
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Le vocabulaire LLM devient visuel.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-map">
                <div><strong>Token</strong><span>Cellule</span></div>
                <div><strong>Génération</strong><span>Frame</span></div>
                <div><strong>Modèle</strong><span>Programme</span></div>
                <div><strong>Training</strong><span>Évolution</span></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | mapping" />
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
Unsupervised
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Sans cible: on cherche du mouvement intéressant.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Exploration</h3><p>Des programmes sont testés au hasard.</p></div>
                <div class="dw-card"><h3>Score</h3><p>Mouvement, entropie, durée de vie.</p></div>
                <div class="dw-card"><h3>Évolution</h3><p>Les meilleurs survivent et mutent.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | unsupervised" />
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
Supervised
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Avec une cible: reproduire un motif.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-flow">
                <div class="dw-node">Glider</div>
                <div class="dw-node">Block</div>
                <div class="dw-node">Beacon</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Programme gagnant</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | supervised" />
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
Préférences
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Version simplifiée de RLHF: comparer deux sorties.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-flow">
                <div class="dw-node">Animation A</div>
                <div class="dw-arrow">vs</div>
                <div class="dw-node">Animation B</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">On garde la meilleure</div>
            </div>
            <p class="dw-lead">Ce n'est pas un vrai RLHF. C'est l'idée, rendue visible.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | rlhf" />
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
Streaming
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Chaque chunk devient une frame.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-flow">
                <div class="dw-node">NDJSON</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">SSE</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Canvas</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Animation</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | streaming" />
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
Live
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Demo
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Live demonstration of training and generation
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-embed dw-embed-demo">
                <iframe src="http://127.0.0.1:8000/runs" title="LLM Game of Life run viewer"></iframe>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | demo" />
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
Ce projet n'est pas un LLM… mais un outil pour les comprendre.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Les tokens deviennent visibles. Le training devient observable. La génération devient une animation.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | conclusion" />
</x-darkwood.slide>
</x-darkwood.deck>
