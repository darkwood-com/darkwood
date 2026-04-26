<x-slidewire::deck theme="black" transition="fade" show-progress="true" show-controls="true" show-fullscreen-button="true">
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">LLM Game of Life</p>
            <h1 class="dw-title">Construire un LLM</h1>
            <p class="dw-lead">Inspiré des concepts réels des LLM, ce projet propose une version visuelle et simplifiée basée sur un Game of Life.</p>
            <blockquote class="twitter-tweet"><p lang="en" dir="ltr">This 2-hour Stanford lecture breaks down how models like ChatGPT and Claude are actually built, clearer than what many people in top AI roles ever get exposed to.<br><br>Save this and set aside two hours today. It might end up being the most valuable thing you learn all week. <a href="https://t.co/5u97uZCWxd">pic.twitter.com/5u97uZCWxd</a></p>&mdash; Allen Braden (@allen_explains) <a href="https://twitter.com/allen_explains/status/2044757995549319172?ref_src=twsrc%5Etfw">April 16, 2026</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
            {{--<div class="mt-10 dw-embed dw-embed-video">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/9vM4p9NN0Ts?si=w-PJDw5UkV8QDdmb" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>--}}
        </section>
        <footer class="dw-footer"><span>01 / introduction</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">LLM en 30 secondes</p>
            <h2 class="dw-heading">Un LLM prédit la suite.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Probabilité</h3><p>Il choisit ce qui semble le plus probable.</p></div>
                <div class="dw-card"><h3>Séquence</h3><p>Il avance étape par étape.</p></div>
                <div class="dw-card"><h3>Tokens</h3><p>Les mots sont découpés en petits morceaux.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>02 / llm</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Récap</p>
            <h2 class="dw-heading">Les grandes idées à retenir.</h2>
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
        <footer class="dw-footer"><span>03 / concepts</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Training</p>
            <h2 class="dw-heading">Un modèle apprend avec plusieurs signaux.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Pretraining</h3><p>Explorer beaucoup de données.</p></div>
                <div class="dw-card"><h3>Fine-tuning</h3><p>Se rapprocher d'une cible.</p></div>
                <div class="dw-card"><h3>RLHF</h3><p>Préférer une réponse à une autre.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>04 / training</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Transition</p>
            <h2 class="dw-heading">Et si on rendait tout ça visible ?</h2>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Mots</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Pixels</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Animation</div>
            </div>
            <p class="dw-lead">Au lieu de générer du texte, on génère une grille qui bouge.</p>
        </section>
        <footer class="dw-footer"><span>05 / transition</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Projet</p>
            <h2 class="dw-heading">Un LLM… mais avec des pixels.</h2>
            <div class="mt-12 dw-grid dw-grid-4">
                <div class="dw-chip">Game of Life</div>
                <div class="dw-chip">Programmes lambda</div>
                <div class="dw-chip">Algorithme génétique</div>
                <div class="dw-chip">Frames streamées</div>
            </div>
            <div class="mt-12 dw-pixel-grid" aria-hidden="true">
                @foreach ([0,1,0,0,1,1,0,1,0,1,1,0,0,0,1,0] as $cell)
                    <span class="{{ $cell ? 'is-on' : '' }}"></span>
                @endforeach
            </div>
        </section>
        <footer class="dw-footer"><span>06 / overview</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Mapping</p>
            <h2 class="dw-heading">Le vocabulaire LLM devient visuel.</h2>
            <div class="mt-10 dw-map">
                <div><strong>Token</strong><span>Cellule</span></div>
                <div><strong>Génération</strong><span>Frame</span></div>
                <div><strong>Modèle</strong><span>Programme</span></div>
                <div><strong>Training</strong><span>Évolution</span></div>
            </div>
        </section>
        <footer class="dw-footer"><span>07 / mapping</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Unsupervised</p>
            <h2 class="dw-heading">Sans cible: on cherche du mouvement intéressant.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Exploration</h3><p>Des programmes sont testés au hasard.</p></div>
                <div class="dw-card"><h3>Score</h3><p>Mouvement, entropie, durée de vie.</p></div>
                <div class="dw-card"><h3>Évolution</h3><p>Les meilleurs survivent et mutent.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>08 / unsupervised</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Supervised</p>
            <h2 class="dw-heading">Avec une cible: reproduire un motif.</h2>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Glider</div>
                <div class="dw-node">Block</div>
                <div class="dw-node">Beacon</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Programme gagnant</div>
            </div>
        </section>
        <footer class="dw-footer"><span>09 / supervised</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Préférences</p>
            <h2 class="dw-heading">Version simplifiée de RLHF: comparer deux sorties.</h2>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Animation A</div>
                <div class="dw-arrow">vs</div>
                <div class="dw-node">Animation B</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">On garde la meilleure</div>
            </div>
            <p class="dw-lead">Ce n'est pas un vrai RLHF. C'est l'idée, rendue visible.</p>
        </section>
        <footer class="dw-footer"><span>10 / rlhf</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Streaming</p>
            <h2 class="dw-heading">Chaque chunk devient une frame.</h2>
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
        <footer class="dw-footer"><span>11 / streaming</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Live</p>
            <h1 class="dw-title">Demo</h1>
            <p class="dw-lead">Live demonstration of training and generation</p>
            <div class="mt-10 dw-embed dw-embed-demo">
                <iframe src="http://127.0.0.1:8000/runs" title="LLM Game of Life run viewer"></iframe>
            </div>
        </section>
        <footer class="dw-footer"><span>12 / demo</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Conclusion</p>
            <h2 class="dw-heading">Ce projet n'est pas un LLM… mais un outil pour les comprendre.</h2>
            <p class="dw-lead">Les tokens deviennent visibles. Le training devient observable. La génération devient une animation.</p>
        </section>
        <footer class="dw-footer"><span>13 / conclusion</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>
</x-slidewire::deck>
