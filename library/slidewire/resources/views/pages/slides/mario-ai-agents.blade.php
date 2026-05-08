<x-slidewire::deck theme="black" transition="fade" show-progress="true" show-controls="true" show-fullscreen-button="true">
    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Video Darkwood / IA x Game Dev</p>
            <h1 class="dw-title">J'ai force 4 IA a recreer Mario de zero</h1>
            <p class="dw-lead">Mais au lieu de juste comparer les modeles, j'ai voulu comprendre comment orchestrer des agents IA pour produire un jeu.</p>
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
        <footer class="dw-footer"><span>01 / hook</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Le probleme du vibe coding classique</p>
            <h2 class="dw-heading">Prompt unique → IA → code fragile</h2>
            <div class="mt-12 dw-grid dw-grid-2">
                <div class="dw-card"><h3>Collision bugs</h3><p>Le perso traverse le sol ou reste bloque.</p></div>
                <div class="dw-card"><h3>Niveaux impossibles</h3><p>Sauts injustes, progression cassee.</p></div>
                <div class="dw-card"><h3>Logique incoherente</h3><p>Regles qui se contredisent d'un fichier a l'autre.</p></div>
                <div class="dw-card"><h3>Dette technique immediate</h3><p>Chaque patch casse autre chose.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>02 / probleme</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Stack IA</p>
            <h2 class="dw-heading">Symfony AI: agents, tools, RAG, multi-modal pour PHP.</h2>
            <div class="mt-12 dw-grid dw-grid-2">
                <div class="dw-card"><h3>Agent</h3><p>Tool calling, subagents, orchestration.</p></div>
                <div class="dw-card"><h3>Platform</h3><p>Abstraction multi-modeles et streaming.</p></div>
                <div class="dw-card"><h3>Store</h3><p>RAG, indexation et recherche semantique.</p></div>
                <div class="dw-card"><h3>AI Bundle</h3><p>Configuration Symfony via YAML.</p></div>
            </div>
            <p class="dw-lead mt-8">Site: <a href="https://ai.symfony.com/" target="_blank" rel="noopener">ai.symfony.com</a></p>
        </section>
        <footer class="dw-footer"><span>03 / symfony-ai</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Moteur de jeu</p>
            <h2 class="dw-heading">Godot: moteur open-source parfait pour prototyper vite.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>2D solide</h3><p>Nodes et scenes pour platformers.</p></div>
                <div class="dw-card"><h3>Scriptable</h3><p>GDScript rapide a iterer.</p></div>
                <div class="dw-card"><h3>Open source</h3><p>Pipeline dev sans lock-in.</p></div>
            </div>
            <p class="dw-lead mt-8">Site: <a href="https://godotengine.org/" target="_blank" rel="noopener">godotengine.org</a></p>
        </section>
        <footer class="dw-footer"><span>04 / godot</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Approche orchestration</p>
            <h2 class="dw-heading">Un agent chef route vers des agents specialises.</h2>
            <div class="mt-12 dw-flow" style="display:flex;flex-direction:column;gap:16px;align-items:flex-start;">
                <div class="dw-node">User Prompt</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Orchestrator Agent</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-grid dw-grid-2" style="width:100%;">
                    <div class="dw-card"><h3>Layout Agent</h3></div>
                    <div class="dw-card"><h3>Gameplay Agent</h3></div>
                    <div class="dw-card"><h3>Godot Export Agent</h3></div>
                    <div class="dw-card"><h3>QA Agent</h3></div>
                </div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Generated Game / Level JSON</div>
            </div>
        </section>
        <footer class="dw-footer"><span>05 / architecture</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Le script Symfony AI</p>
            <h2 class="dw-heading">Pattern: Agent + Handoff + MultiAgent + MessageBag</h2>
            <pre class="mt-10 dw-code">$messages = MessageBag::of(Message::ofUser($prompt));

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

$result = $flow->run($messages);</pre>
            <p class="dw-lead mt-8">Idee cle: un agent chef route vers des agents specialises avec un contexte partage.</p>
        </section>
        <footer class="dw-footer"><span>06 / symfony-ai</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Les agents</p>
            <h2 class="dw-heading">4 roles complementaires</h2>
            <div class="mt-12 dw-grid dw-grid-2">
                <div class="dw-card"><h3>Layout Agent</h3><p>Construit le niveau.</p></div>
                <div class="dw-card"><h3>Gameplay Agent</h3><p>Ajoute les mecaniques.</p></div>
                <div class="dw-card"><h3>Godot Export Agent</h3><p>Genere les fichiers exploitables.</p></div>
                <div class="dw-card"><h3>QA Agent</h3><p>Verifie la jouabilite.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>07 / agents</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Les 4 IA testees</p>
            <h2 class="dw-heading">Meme prompt initial. Resultats tres differents.</h2>
            <div class="mt-12 dw-grid dw-grid-4" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;">
                <div class="dw-card"><h3>ChatGPT</h3></div>
                <div class="dw-card"><h3>Claude</h3></div>
                <div class="dw-card"><h3>Gemini</h3></div>
                <div class="dw-card"><h3>Grok</h3></div>
            </div>
            <p class="dw-lead mt-10">Objectif: comparer creativite, stabilite, cout et jouabilite.</p>
        </section>
        <footer class="dw-footer"><span>08 / benchmark</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Generation automatique</p>
            <h2 class="dw-heading">La generation se fait en une seule commande Symfony.</h2>
            <pre class="mt-10 dw-code">php bin/console app:mario:orchestrate</pre>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Prompt</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Multi-agent</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Export jeu</div>
            </div>
        </section>
        <footer class="dw-footer"><span>09 / commande</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Demo ChatGPT</p>
            <h2 class="dw-heading">ChatGPT - bonne base, iteration rapide</h2>
            <div class="mt-8" style="height:66vh;border:1px solid rgba(255,255,255,0.18);border-radius:16px;overflow:hidden;background:#05070d;">
                <iframe src="http://localhost:8062/Super%20Mario%20Bros.html" title="Demo ChatGPT Mario" style="width:100%;height:100%;border:0;" loading="eager"></iframe>
            </div>
        </section>
        <footer class="dw-footer"><span>10 / chatgpt</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Demo Claude</p>
            <h2 class="dw-heading">Claude - meilleur raisonnement, plus cher</h2>
            <div class="mt-8" style="height:66vh;border:1px solid rgba(255,255,255,0.18);border-radius:16px;overflow:hidden;background:#05070d;">
                <iframe src="http://localhost:8061/Super%20mario%20bros.html" title="Demo Claude Mario" style="width:100%;height:100%;border:0;" loading="eager"></iframe>
            </div>
        </section>
        <footer class="dw-footer"><span>11 / claude</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Demo Gemini</p>
            <h2 class="dw-heading">Gemini - tres bon rendu visuel et creativite</h2>
            <div class="mt-8" style="height:66vh;border:1px solid rgba(255,255,255,0.18);border-radius:16px;overflow:hidden;background:#05070d;">
                <iframe src="http://localhost:8063/Solar%20Engine.html" title="Demo Gemini Mario" style="width:100%;height:100%;border:0;" loading="eager"></iframe>
            </div>
        </section>
        <footer class="dw-footer"><span>12 / gemini</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Demo Grok</p>
            <h2 class="dw-heading">Grok - interessant, mais instable</h2>
            <div class="mt-8" style="height:66vh;border:1px solid rgba(255,255,255,0.18);border-radius:16px;overflow:hidden;background:#05070d;">
                <iframe src="http://localhost:8060/Platformer.html" title="Demo Grok Mario" style="width:100%;height:100%;border:0;" loading="eager"></iframe>
            </div>
        </section>
        <footer class="dw-footer"><span>13 / grok</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Ce que l'experience montre</p>
            <h2 class="dw-heading">Le vrai sujet n'est pas "quelle IA code le mieux".</h2>
            <div class="mt-12 dw-flow" style="display:flex;flex-direction:column;gap:14px;align-items:flex-start;">
                <div class="dw-node">Cadrer</div>
                <div class="dw-node">Orchestrer</div>
                <div class="dw-node">Tester</div>
                <div class="dw-node">Corriger</div>
            </div>
            <p class="dw-lead mt-10">L'efficacite vient du systeme d'agents, pas d'un prompt unique.</p>
        </section>
        <footer class="dw-footer"><span>14 / enseignements</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Conclusion</p>
            <h1 class="dw-title">Le futur du vibe coding, c'est l'orchestration</h1>
            <p class="dw-lead">Un seul agent peut produire un prototype. Plusieurs agents bien cadres peuvent produire un systeme.</p>
            <div class="mt-12 dw-card">
                <h3>CTA</h3>
                <p>Abonne-toi si tu veux voir la suite avec Godot, Symfony AI et orchestration d'agents.</p>
            </div>
        </section>
        <footer class="dw-footer"><span>15 / conclusion</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{--<x-slidewire::slide class="dw-slide" style="background: radial-gradient(circle at top right, rgba(84, 197, 255, 0.2), transparent 45%), radial-gradient(circle at bottom left, rgba(128, 0, 255, 0.2), transparent 40%), #05070d;">
        <section class="dw-wrap">
            <p class="dw-kicker">Bonus optionnel</p>
            <h2 class="dw-heading">OBS checklist</h2>
            <div class="mt-12 dw-grid dw-grid-2">
                <div class="dw-card"><h3>Serveurs</h3><p>Verifier que les 4 serveurs locaux tournent.</p></div>
                <div class="dw-card"><h3>Ports</h3><p>Verifier 8060, 8061, 8062, 8063.</p></div>
                <div class="dw-card"><h3>Audio</h3><p>Verifier le son avant enregistrement.</p></div>
                <div class="dw-card"><h3>Cadrage</h3><p>Verifier le cadrage iframe pour chaque scene.</p></div>
            </div>
            <p class="dw-lead mt-8">Preparer une scene OBS par modele IA.</p>
        </section>
        <footer class="dw-footer"><span>16 / bonus</span></footer>
    </x-slidewire::slide>--}}
</x-slidewire::deck>
