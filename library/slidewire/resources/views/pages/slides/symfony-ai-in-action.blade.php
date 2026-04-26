<x-slidewire::deck theme="black" transition="fade" show-progress="true" show-controls="true" show-fullscreen-button="true">
    @php
        $brand = '<span class="dw-accent">Symfony AI</span>';
    @endphp

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood selection / SymfonyLive Berlin 2026</p>
            <h1 class="dw-title">Symfony AI in Action</h1>
            <p class="dw-lead">Construire des fonctionnalités IA réalistes avec Symfony</p>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Models</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Tools</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Orchestration</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Le problème</p>
            <h2 class="dw-heading">L'IA produit beaucoup de buzzwords. Le produit, lui, demande du contrôle.</h2>
            <p class="dw-lead">Le vrai défi n'est pas de faire répondre un modèle. C'est de le brancher sur une application qui a des règles, des données, des droits et des effets de bord.</p>
        </section>
        <footer class="dw-footer"><span>01 / problème</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Vision</p>
            <h2 class="dw-heading">{!! $brand !!} ne vend pas un chatbot. Il donne une stack pour activer des features IA.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Inférence</h3><p>Changer de modèle sans réécrire l'app.</p></div>
                <div class="dw-card"><h3>Contexte</h3><p>Relier l'IA aux données métier.</p></div>
                <div class="dw-card"><h3>Action</h3><p>Exposer du code Symfony contrôlé.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>02 / vision</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Les briques</p>
            <h2 class="dw-heading">Une stack, pas un helper HTTP.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-chip">Platform</div>
                <div class="dw-chip">Agent</div>
                <div class="dw-chip">Store</div>
                <div class="dw-chip">AI Bundle</div>
                <div class="dw-chip">MCP Bundle</div>
                <div class="dw-chip">MCP SDK</div>
            </div>
        </section>
        <footer class="dw-footer"><span>03 / overview</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Platform</p>
            <h2 class="dw-heading">Une abstraction d'inférence pour parler à plusieurs providers.</h2>
            <div class="dw-flow">
                <div class="dw-node">Votre code PHP</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Platform</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">OpenAI<br>Claude<br>Gemini<br>Mistral</div>
            </div>
        </section>
        <footer class="dw-footer"><span>04 / platform</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Même intention, modèle différent</p>
            <h2 class="dw-heading">Le code applicatif reste stable.</h2>
            <pre class="mt-10 dw-code">$result = $platform->request(
    model: new GptModel('gpt-4.1-mini'),
    input: 'Résume cette commande client.'
);</pre>
            <div class="mt-8 dw-grid dw-grid-4" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px">
                <div class="dw-chip">OpenAI</div>
                <div class="dw-chip">Claude</div>
                <div class="dw-chip">Gemini</div>
                <div class="dw-chip">Mistral</div>
            </div>
        </section>
        <footer class="dw-footer"><span>05 / providers</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Streaming</p>
            <h2 class="dw-heading">Streamer, ce n'est pas juste afficher des mots plus vite.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Deltas typés</h3><p>Texte, tool call, signal de fin.</p></div>
                <div class="dw-card"><h3>Usage tokens</h3><p>Observer le coût pendant la réponse.</p></div>
                <div class="dw-card"><h3>UX live</h3><p>Répondre sans bloquer l'interface.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>06 / streaming</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Multimodal</p>
            <h2 class="dw-heading">L'entrée et la sortie ne sont plus seulement du texte.</h2>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Audio</div>
                <div class="dw-node">Image</div>
                <div class="dw-node">PDF</div>
                <div class="dw-node">Binaire</div>
            </div>
            <p class="dw-lead">Le modèle devient une pièce d'un pipeline média, pas une zone de texte magique.</p>
        </section>
        <footer class="dw-footer"><span>07 / multimodal</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Structured Output</p>
            <h2 class="dw-heading">On veut des objets PHP fiables, pas du JSON fragile dans une string.</h2>
            <pre class="mt-10 dw-code">final readonly class ProductInsight
{
    public function __construct(
        public string $risk,
        public array $nextActions,
    ) {}
}</pre>
        </section>
        <footer class="dw-footer"><span>08 / structure</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Agent</p>
            <h2 class="dw-heading">Un agent, c'est une boucle contrôlée entre modèle et application.</h2>
            <div class="dw-flow">
                <div class="dw-node">LLM</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Tool call</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Service Symfony</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Réponse</div>
            </div>
        </section>
        <footer class="dw-footer"><span>09 / agent</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Tool Calling</p>
            <h2 class="dw-heading">Exposer du code métier avec une frontière explicite.</h2>
            <pre class="mt-10 dw-code">#[AsTool('refund_order')]
public function refundOrder(string $orderId): RefundResult
{
    return $this->refunds->request($orderId);
}</pre>
        </section>
        <footer class="dw-footer"><span>10 / tools</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Human in the Loop</p>
            <h2 class="dw-heading">Le modèle peut proposer. L'application décide quand agir.</h2>
            <div class="dw-flow">
                <div class="dw-node">Tool call proposé</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Validation humaine</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Exécution ou refus</div>
            </div>
        </section>
        <footer class="dw-footer"><span>11 / hitl</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Memory</p>
            <h2 class="dw-heading">La mémoire utile vient souvent de votre application.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Profil</h3><p>Préférences, rôle, langue.</p></div>
                <div class="dw-card"><h3>Historique</h3><p>Actions et conversations récentes.</p></div>
                <div class="dw-card"><h3>Permissions</h3><p>Ce que l'agent peut voir ou faire.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>12 / memory</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Store</p>
            <h2 class="dw-heading">Le RAG commence avant la recherche vectorielle.</h2>
            <div class="dw-flow">
                <div class="dw-node">Loading</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Filtering</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Transforming</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Vectorizing</div>
            </div>
        </section>
        <footer class="dw-footer"><span>13 / store</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">RAG</p>
            <h2 class="dw-heading">Connecter l'agent aux données applicatives, avec du contexte vérifiable.</h2>
            <div class="dw-flow">
                <div class="dw-node">Question</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">SimilaritySearch</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Documents</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Réponse sourcée</div>
            </div>
        </section>
        <footer class="dw-footer"><span>14 / rag</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Multi-agent</p>
            <h2 class="dw-heading">Plusieurs agents, oui. Mais seulement avec une orchestration lisible.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Subagents</h3><p>Spécialiser une tâche bornée.</p></div>
                <div class="dw-card"><h3>Handoff</h3><p>Passer le relais avec contexte.</p></div>
                <div class="dw-card"><h3>Traces</h3><p>Comprendre qui a décidé quoi.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>15 / multi-agent</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">MCP</p>
            <h2 class="dw-heading">MCP ouvre la porte à des architectures agentiques interopérables.</h2>
            <div class="dw-flow">
                <div class="dw-node">Symfony app</div>
                <div class="dw-arrow">↔</div>
                <div class="dw-node">MCP Server</div>
                <div class="dw-arrow">↔</div>
                <div class="dw-node">Agents<br>IDE<br>Outils externes</div>
            </div>
        </section>
        <footer class="dw-footer"><span>16 / mcp</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Analyse Darkwood</p>
            <h2 class="dw-heading">Le vrai sujet n'est pas le chatbot. C'est l'orchestration.</h2>
            <p class="dw-lead">Qui appelle quoi ? Avec quel contexte ? Sous quelles permissions ? Avec quelle trace ? Et comment on reprend la main quand le modèle sort du cadre ?</p>
        </section>
        <footer class="dw-footer"><span>17 / analyse</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Stack cible</p>
            <h2 class="dw-heading">Symfony AI + MCP + Flow + Navi + Uniflow.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Symfony AI</h3><p>Modèles, agents, store, bundles.</p></div>
                <div class="dw-card"><h3>MCP</h3><p>Interopérabilité des tools et contextes.</p></div>
                <div class="dw-card"><h3>Flow / Navi / Uniflow</h3><p>Workflows, navigation agentique, orchestration métier.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>18 / stack</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Conclusion</p>
            <h2 class="dw-heading">Construire des features IA, pas seulement des prompts.</h2>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Briques Symfony</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Données produit</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Orchestration contrôlée</div>
            </div>
            <p class="dw-lead">C'est là que Symfony AI devient intéressant pour une vraie application.</p>
        </section>
        <footer class="dw-footer"><span>19 / conclusion</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>
</x-slidewire::deck>
