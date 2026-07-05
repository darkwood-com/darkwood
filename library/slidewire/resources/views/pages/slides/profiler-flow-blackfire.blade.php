{{-- Profiler un flow Symfony avec Blackfire | /slides/profiler-flow-blackfire --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $flow = '<span class="dw-accent">Flow</span>';
        $bf = '<span class="dw-accent">Blackfire</span>';
    @endphp

    {{-- 1 · TITRE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · Symfony 8 · {!! $flow !!} · {!! $bf !!}</p>
            <h1 class="dw-title">Profiler un flow Symfony avec Blackfire</h1>
            <p class="dw-lead">Rendre les workers observables —<br>pas seulement mesurables.</p>
            <p class="dw-note mt-6"><code>app:flow:profile-demo</code> · 3 démos Symfony</p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Worker</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Étapes nommées</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Flamegraph lisible</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · PROBLÈME WORKER --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Le problème</p>
            <h2 class="dw-heading-slide">Les workers Symfony sont difficiles à profiler.</h2>
            <pre class="mt-4 dw-code" style="font-size:16px;">while (true) {
    $message = $queue->consume();
    if (!$message) { sleep(1); continue; }
    $this->process($message);  // ← boîte noire
}</pre>
            <div class="mt-4 dw-embed" style="height:min(200px,28vh)">
                <x-slidewire::diagram>
flowchart LR
  LOOP[Boucle worker]
  PROC[process opaque]
  BF[Blackfire]
  LOOP --> PROC
  PROC --> BF
  BF --> FLAT[Un gros bloc CPU]
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-question mt-4">Blackfire voit du PHP — pas une architecture.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>01 / problème</span></footer>
    </x-slidewire::slide>

    {{-- 3 · LA BOUCLE N'EST PAS L'ENNEMI --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">La boucle n'est pas l'ennemi</p>
            <h2 class="dw-heading-slide">Symfony worker · driver {!! $flow !!} · YFlow</h2>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:10px;">
                <x-slidewire::fragment :index="0">
                    <div class="dw-card">
                        <h3>Worker</h3>
                        <p><code>while (true)</code></p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-card">
                        <h3>Driver</h3>
                        <p><code>await()</code> loop</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="2">
                    <div class="dw-card">
                        <h3>YFlow</h3>
                        <p><code>$loop($next)</code></p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <div class="mt-4 dw-embed" style="height:min(220px,30vh)">
                <x-slidewire::diagram>
flowchart TB
  W[Worker Symfony]
  AW[flow await]
  D[Driver loop]
  J1[Job extract]
  J2[Job transform]
  J3[Job load]
  W --> AW --> D
  D --> J1 --> J2 --> J3
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="3">
                <p class="dw-takeaway mt-3">Pas la boucle infinie — c'est <code>process()</code> opaque.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>02 / boucles</span></footer>
    </x-slidewire::slide>

    {{-- 4 · BLACKFIRE + SIGUSR2 --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">{!! $bf !!} + signaux</p>
            <h2 class="dw-heading-slide">Profiler un processus déjà lancé.</h2>
            <div class="mt-4 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-card">
                        <h3>Terminal 1</h3>
                        <pre class="dw-code" style="font-size:14px;margin:0;">bin/console app:flow:profile-demo -vv</pre>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-card">
                        <h3>Terminal 2</h3>
                        <pre class="dw-code" style="font-size:14px;margin:0;">kill -USR2 &lt;pid&gt;  # start
kill -USR2 &lt;pid&gt;  # stop → URL</pre>
                    </div>
                </x-slidewire::fragment>
            </div>
            <div class="mt-4 dw-embed" style="height:min(180px,24vh)">
                <x-slidewire::diagram>
flowchart LR
  RUN[Worker tourne]
  S1[SIGUSR2 start]
  WIN[Fenêtre profiling]
  S2[SIGUSR2 stop]
  URL[URL profil]
  RUN --> S1 --> WIN --> S2 --> URL
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-note mt-3">Profile les <strong>itérations</strong> — pas le boot du container.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>03 / blackfire</span></footer>
    </x-slidewire::slide>

    {{-- 5 · RÔLE DE FLOW --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Rôle de {!! $flow !!}</p>
            <h2 class="dw-heading" style="font-size:clamp(1.6rem,3.5vw,2.6rem);">Le profiling ne commence pas dans Blackfire.</h2>
            <x-slidewire::fragment :index="0">
                <h2 class="dw-heading mt-4" style="font-size:clamp(1.4rem,3vw,2.2rem);">Il commence dans la structure du traitement.</h2>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-6">{!! $flow !!} rend la boucle profileable en transformant chaque itération en <span class="dw-accent">étapes d'exécution explicites</span>.</p>
            </x-slidewire::fragment>
            <pre class="mt-6 dw-code" style="font-size:15px;">$flow(new Ip($batch));
$flow->await();  // driver loop + jobs nommés</pre>
        </section>
        <footer class="dw-footer"><span>04 / flow</span></footer>
    </x-slidewire::slide>

    {{-- 6 · DÉMO 1 KIBONO --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Démo 1</p>
            <h2 class="dw-heading-slide">Kibono Flow — pipeline ETL visible</h2>
            <p class="dw-note">php-etl / Kiboko dans {!! $flow !!}</p>
            <div class="mt-4 dw-flow dw-flow--vertical" style="max-width:640px;margin:0 auto;gap:6px;">
                <x-slidewire::fragment :index="0"><div class="dw-pipeline-row is-active"><span class="dw-pipeline-label">extract</span><span>ExtractorInterface → buckets</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-pipeline-row"><span class="dw-pipeline-label">transform</span><span>array_map · str_rot13</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-pipeline-row"><span class="dw-pipeline-label">load</span><span>accumulation + écriture</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-pipeline-row is-active" style="border-color:rgba(184,255,106,.35);"><span class="dw-pipeline-label">walk</span><span>résultats + JSON</span></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="4">
                <p class="dw-note mt-4">1 itération = 1 batch produit · github.com/matyo91/kibono-flow</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>05 / kibono</span></footer>
    </x-slidewire::slide>

    {{-- 7 · DÉMO 2 PHP FLOW --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Démo 2</p>
            <h2 class="dw-heading-slide">PHP Flow — ETL + orchestration</h2>
            <div class="mt-4 dw-split" style="grid-template-columns:1fr 1fr;">
                <x-slidewire::fragment :index="0">
                    <div class="dw-card">
                        <h3>Couche 1 · ETL</h3>
                        <p class="dw-muted" style="font-size:15px;">Inspiré Flow PHP</p>
                        <p style="margin:8px 0 0;font-size:15px;">Extractor → Transformer → Loader</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-card">
                        <h3>Couche 2 · {!! $flow !!}</h3>
                        <p class="dw-muted" style="font-size:15px;">Jobs d'orchestration</p>
                        <p style="margin:8px 0 0;font-size:15px;">prepare → extract → transform → load</p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-6">Hotspot CPU : <code>NormalizeOrderTransformer</code> (SHA-256 ×1000)</p>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="3">
                <p class="dw-note mt-3">github.com/matyo91/php-flow · flow-php.com</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>06 / php-flow</span></footer>
    </x-slidewire::slide>

    {{-- 8 · DÉMO 3 VIBEPHP --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Démo 3</p>
            <h2 class="dw-heading-slide">VibePHP Flow — pipeline applicatif</h2>
            <p class="dw-note">Structure VibePHP · runtime factice reproductible</p>
            <div class="mt-4 dw-flow dw-flow--vertical" style="max-width:680px;margin:0 auto;gap:5px;">
                <x-slidewire::fragment :index="0"><div class="dw-pipeline-row"><span class="dw-pipeline-label">resolve</span><span>path → script vibe/</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-pipeline-row"><span class="dw-pipeline-label">read</span><span>file_get_contents</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-pipeline-row"><span class="dw-pipeline-label">prompt</span><span>json_encode contexte</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-pipeline-row"><span class="dw-pipeline-label">execute</span><span>regex · includes · hash</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-pipeline-row is-active" style="border-color:rgba(184,255,106,.35);"><span class="dw-pipeline-label">metrics</span><span>durée · mémoire · status</span></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="5">
                <p class="dw-note mt-4">1 itération = 1 requête simulée · github.com/matyo91/vibephp-flow</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>07 / vibephp</span></footer>
    </x-slidewire::slide>

    {{-- 9 · CE QUE BLACKFIRE MONTRE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Lecture du profil</p>
            <h2 class="dw-heading-slide">Ce que Blackfire montre — et pas.</h2>
            <div class="mt-6 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-ok">
                        <h3>Oui</h3>
                        <ul class="dw-list-compact" style="margin:0;">
                            <li>CPU par fonction</li>
                            <li>Mémoire · I/O</li>
                            <li>Frames par job / étape</li>
                        </ul>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-warn">
                        <h3>Non</h3>
                        <ul class="dw-list-compact" style="margin:0;">
                            <li>Intent métier</li>
                            <li>Où mettre un cache</li>
                            <li>« Flow est plus rapide »</li>
                        </ul>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-6">{!! $flow !!} ne remplace pas Messenger. Il structure le code <em>dans</em> le worker.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>08 / lecture</span></footer>
    </x-slidewire::slide>

    {{-- 10 · CONCLUSION + RESSOURCES --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Conclusion</p>
            <h2 class="dw-heading">Le profiling commence par la structure.</h2>
            <x-slidewire::fragment :index="0">
                <p class="dw-lead mt-6">Worker · driver · YFlow — trois boucles, une question : que se passe-t-il dans une itération ?</p>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <ul class="dw-list mt-8" style="font-size:17px;">
                    <li><a href="https://github.com/matyo91/kibono-flow" target="_blank" rel="noopener">github.com/matyo91/kibono-flow</a></li>
                    <li><a href="https://github.com/matyo91/php-flow" target="_blank" rel="noopener">github.com/matyo91/php-flow</a></li>
                    <li><a href="https://github.com/matyo91/vibephp-flow" target="_blank" rel="noopener">github.com/matyo91/vibephp-flow</a></li>
                    <li><a href="https://github.com/darkwood-com/flow" target="_blank" rel="noopener">github.com/darkwood-com/flow</a></li>
                    <li><a href="https://flow-php.com" target="_blank" rel="noopener">flow-php.com</a> · <a href="https://github.com/mnapoli/vibephp" target="_blank" rel="noopener">github.com/mnapoli/vibephp</a></li>
                    <li><a href="https://jolicode.com/blog/profiler-un-consumer-avec-blackfire" target="_blank" rel="noopener">jolicode.com/blog/profiler-un-consumer-avec-blackfire</a></li>
                </ul>
            </x-slidewire::fragment>
            <div class="mt-8 dw-flow">
                <div class="dw-node">Boucle</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Étapes</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Profil actionnable</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
