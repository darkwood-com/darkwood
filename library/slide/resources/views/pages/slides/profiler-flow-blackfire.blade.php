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
Darkwood · Symfony 8 · <span class="dw-accent">Flow</span> · <span class="dw-accent">Blackfire</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Profiler un flow Symfony avec Blackfire
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Rendre les workers observables —<br>pas seulement mesurables.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <p class="dw-note mt-6"><code>app:flow:profile-demo</code> · 3 démos Symfony</p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Worker</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Étapes nommées</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Flamegraph lisible</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
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
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Le problème
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Les workers Symfony sont difficiles à profiler.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_1_0'
while (true) {
    $message = $queue->consume();
    if (!$message) { sleep(1); continue; }
    $this->process($message);  // ← boîte noire
}
SW_CODE_1_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  LOOP[Boucle worker]
  PROC[process opaque]
  BF[Blackfire]
  LOOP --&gt; PROC
  PROC --&gt; BF
  BF --&gt; FLAT[Un gros bloc CPU]</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-question mt-4">Blackfire voit du PHP — pas une architecture.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | problème" />
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
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
La boucle n'est pas l'ennemi
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Symfony worker · driver <span class="dw-accent">Flow</span> · YFlow
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-grid dw-grid-3" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Worker</h3>
                        <p><code>while (true)</code></p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Driver</h3>
                        <p><code>await()</code> loop</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>YFlow</h3>
                        <p><code>$loop($next)</code></p>
                    </div>
</div>
            </div>
            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  W[Worker Symfony]
  AW[flow await]
  D[Driver loop]
  J1[Job extract]
  J2[Job transform]
  J3[Job load]
  W --&gt; AW --&gt; D
  D --&gt; J1 --&gt; J2 --&gt; J3</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p class="dw-takeaway mt-3">Pas la boucle infinie — c'est <code>process()</code> opaque.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | boucles" />
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
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
<span class="dw-accent">Blackfire</span> + signaux
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Profiler un processus déjà lancé.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Terminal 1</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_3_0'
bin/console app:flow:profile-demo -vv
SW_CODE_3_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Terminal 2</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_3_1'
kill -USR2 <pid>  # start
kill -USR2 <pid>  # stop → URL
SW_CODE_3_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    </div>
</div>
            </div>
            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  RUN[Worker tourne]
  S1[SIGUSR2 start]
  WIN[Fenêtre profiling]
  S2[SIGUSR2 stop]
  URL[URL profil]
  RUN --&gt; S1 --&gt; WIN --&gt; S2 --&gt; URL</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-note mt-3">Profile les <strong>itérations</strong> — pas le boot du container.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | blackfire" />
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
Rôle de <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Le profiling ne commence pas dans Blackfire.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <h3 class="dw-heading mt-4">Il commence dans la structure du traitement.</h3>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6"><span class="dw-accent">Flow</span> rend la boucle profileable en transformant chaque itération en <span class="dw-accent">étapes d'exécution explicites</span>.</p>
</div>

@endverbatim
@php
$snippet = <<<'SW_CODE_4_0'
$flow(new Ip($batch));
$flow->await();  // driver loop + jobs nommés
SW_CODE_4_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | flow" />
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
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Démo 1
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Kibono Flow — pipeline ETL visible
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <p class="dw-note">php-etl / Kiboko dans <span class="dw-accent">Flow</span></p>
            <div class="mt-4 dw-flow dw-flow--vertical" style="max-width:640px;margin:0 auto;gap:6px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-pipeline-row is-active"><span class="dw-pipeline-label">extract</span><span>ExtractorInterface → buckets</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">transform</span><span>array_map · str_rot13</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">load</span><span>accumulation + écriture</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-pipeline-row is-active"><span class="dw-pipeline-label">walk</span><span>résultats + JSON</span></div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <p class="dw-note mt-4">1 itération = 1 batch produit · github.com/matyo91/kibono-flow</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | kibono" />
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
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Démo 2
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
PHP Flow — ETL + orchestration
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-split" style="grid-template-columns:1fr 1fr">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Couche 1 · ETL</h3>
                        <p class="dw-muted">Inspiré Flow PHP</p>
                        <p style="margin:8px 0 0">Extractor → Transformer → Loader</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Couche 2 · <span class="dw-accent">Flow</span></h3>
                        <p class="dw-muted">Jobs d'orchestration</p>
                        <p style="margin:8px 0 0">prepare → extract → transform → load</p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Hotspot CPU : <code>NormalizeOrderTransformer</code> (SHA-256 ×1000)</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p class="dw-note mt-3">github.com/matyo91/php-flow · flow-php.com</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | php-flow" />
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
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Démo 3
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
VibePHP Flow — pipeline applicatif
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <p class="dw-note">Structure VibePHP · runtime factice reproductible</p>
            <div class="mt-4 dw-flow dw-flow--vertical" style="max-width:680px;margin:0 auto;gap:5px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">resolve</span><span>path → script vibe/</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">read</span><span>file_get_contents</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">prompt</span><span>json_encode contexte</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">execute</span><span>regex · includes · hash</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-pipeline-row is-active"><span class="dw-pipeline-label">metrics</span><span>durée · mémoire · status</span></div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <p class="dw-note mt-4">1 itération = 1 requête simulée · github.com/matyo91/vibephp-flow</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | vibephp" />
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
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Lecture du profil
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Ce que Blackfire montre — et pas.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Oui</h3>
                        <ul class="dw-list-compact" style="margin:0">
                            <li>CPU par fonction</li>
                            <li>Mémoire · I/O</li>
                            <li>Frames par job / étape</li>
                        </ul>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>Non</h3>
                        <ul class="dw-list-compact" style="margin:0">
                            <li>Intent métier</li>
                            <li>Où mettre un cache</li>
                            <li>« Flow est plus rapide »</li>
                        </ul>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6"><span class="dw-accent">Flow</span> ne remplace pas Messenger. Il structure le code <em>dans</em> le worker.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | lecture" />
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
Conclusion
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Le profiling commence par la structure.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-lead mt-6">Worker · driver · YFlow — trois boucles, une question : que se passe-t-il dans une itération ?</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <ul class="dw-list mt-8">
                    <li><a href="https://github.com/matyo91/kibono-flow" target="_blank" rel="noopener">github.com/matyo91/kibono-flow</a></li>
                    <li><a href="https://github.com/matyo91/php-flow" target="_blank" rel="noopener">github.com/matyo91/php-flow</a></li>
                    <li><a href="https://github.com/matyo91/vibephp-flow" target="_blank" rel="noopener">github.com/matyo91/vibephp-flow</a></li>
                    <li><a href="https://github.com/darkwood-com/flow" target="_blank" rel="noopener">github.com/darkwood-com/flow</a></li>
                    <li><a href="https://flow-php.com" target="_blank" rel="noopener">flow-php.com</a> · <a href="https://github.com/mnapoli/vibephp" target="_blank" rel="noopener">github.com/mnapoli/vibephp</a></li>
                    <li><a href="https://jolicode.com/blog/profiler-un-consumer-avec-blackfire" target="_blank" rel="noopener">jolicode.com/blog/profiler-un-consumer-avec-blackfire</a></li>
                </ul>
</div>
            <div class="mt-8 dw-flow">
                <div class="dw-node">Boucle</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Étapes</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Profil actionnable</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>
</x-darkwood.deck>
