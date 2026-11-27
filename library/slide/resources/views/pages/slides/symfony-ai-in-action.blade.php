<x-darkwood.deck>
    <x-darkwood.cover
        title="Symfony AI in Action"
        subtitle="Construire des fonctionnalités IA réalistes avec Symfony"
        overline="Darkwood selection / SymfonyLive Berlin 2026"
    />

    <x-darkwood.content
        title="Le problème"
        subtitle="L'IA produit beaucoup de buzzwords. Le produit, lui, demande du contrôle."
        lead="Le vrai défi n'est pas de faire répondre un modèle. C'est de le brancher sur une application qui a des règles, des données, des droits et des effets de bord."
        label="01 | problème"
    />

    <x-darkwood.content
        title="Vision"
        subtitle="Symfony AI ne vend pas un chatbot. Il donne une stack pour activer des features IA."
        label="02 | vision"
    >
        <x-darkwood.columns :count="3">
            <div class="sw-column"><h3>Inférence</h3><p>Changer de modèle sans réécrire l'app.</p></div>
            <div class="sw-column"><h3>Contexte</h3><p>Relier l'IA aux données métier.</p></div>
            <div class="sw-column"><h3>Action</h3><p>Exposer du code Symfony contrôlé.</p></div>
        </x-darkwood.columns>
    </x-darkwood.content>

    <x-darkwood.section
        title="Les briques"
        subtitle="Une stack, pas un helper HTTP."
        label="03 | overview"
    >
        <p class="sw-lead">Platform · Agent · Store · AI Bundle · MCP Bundle · MCP SDK</p>
    </x-darkwood.section>

    <x-darkwood.content
        title="Platform"
        subtitle="Une abstraction d'inférence pour parler à plusieurs providers."
        label="04 | platform"
    >
        <div class="sw-process">
            <div class="sw-process-step">Votre code PHP</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Platform</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">OpenAI<br>Claude<br>Gemini<br>Mistral</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="Le code applicatif reste stable."
        overline="Même intention, modèle différent"
        label="05 | providers"
        :top="true"
    >
        <x-darkwood.code><x-slidewire::code language="php">$result = $platform->request(
    model: new GptModel('gpt-4.1-mini'),
    input: 'Résume cette commande client.'
);</x-slidewire::code></x-darkwood.code>
        <div class="sw-content">
            <x-darkwood.columns :count="4">
                <div class="sw-column"><h3>OpenAI</h3></div>
                <div class="sw-column"><h3>Claude</h3></div>
                <div class="sw-column"><h3>Gemini</h3></div>
                <div class="sw-column"><h3>Mistral</h3></div>
            </x-darkwood.columns>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="Streaming"
        subtitle="Streamer, ce n'est pas juste afficher des mots plus vite."
        label="06 | streaming"
    >
        <x-darkwood.columns :count="3">
            <div class="sw-column"><h3>Deltas typés</h3><p>Texte, tool call, signal de fin.</p></div>
            <div class="sw-column"><h3>Usage tokens</h3><p>Observer le coût pendant la réponse.</p></div>
            <div class="sw-column"><h3>UX live</h3><p>Répondre sans bloquer l'interface.</p></div>
        </x-darkwood.columns>
    </x-darkwood.content>

    <x-darkwood.section
        title="Multimodal"
        subtitle="L'entrée et la sortie ne sont plus seulement du texte."
        label="07 | multimodal"
    >
        <p class="sw-lead">Audio · Image · PDF · Binaire</p>
        <p class="sw-lead">Le modèle devient une pièce d'un pipeline média, pas une zone de texte magique.</p>
    </x-darkwood.section>

    <x-darkwood.content
        title="Structured Output"
        subtitle="On veut des objets PHP fiables, pas du JSON fragile dans une string."
        label="08 | structure"
        :top="true"
    >
        <x-darkwood.code><x-slidewire::code language="php">final readonly class ProductInsight
{
    public function __construct(
        public string $risk,
        public array $nextActions,
    ) {}
}</x-slidewire::code></x-darkwood.code>
    </x-darkwood.content>

    <x-darkwood.content
        title="Agent"
        subtitle="Un agent, c'est une boucle contrôlée entre modèle et application."
        label="09 | agent"
    >
        <div class="sw-process">
            <div class="sw-process-step">LLM</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Tool call</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Service Symfony</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Réponse</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="Tool Calling"
        subtitle="Exposer du code métier avec une frontière explicite."
        label="10 | tools"
        :top="true"
    >
        <x-darkwood.code><x-slidewire::code language="php">#[AsTool('refund_order')]
public function refundOrder(string $orderId): RefundResult
{
    return $this->refunds->request($orderId);
}</x-slidewire::code></x-darkwood.code>
    </x-darkwood.content>

    <x-darkwood.content
        title="Human in the Loop"
        subtitle="Le modèle peut proposer. L'application décide quand agir."
        label="11 | hitl"
    >
        <div class="sw-process">
            <div class="sw-process-step">Tool call proposé</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Validation humaine</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Exécution ou refus</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="Memory"
        subtitle="La mémoire utile vient souvent de votre application."
        label="12 | memory"
    >
        <ul class="sw-list">
            <li>Profil<ul><li>Préférences, rôle, langue.</li></ul></li>
            <li>Historique<ul><li>Actions et conversations récentes.</li></ul></li>
            <li>Permissions<ul><li>Ce que l'agent peut voir ou faire.</li></ul></li>
        </ul>
    </x-darkwood.content>

    <x-darkwood.content
        title="Store"
        subtitle="Le RAG commence avant la recherche vectorielle."
        label="13 | store"
    >
        <div class="sw-process">
            <div class="sw-process-step">Loading</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Filtering</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Transforming</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Vectorizing</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="RAG"
        subtitle="Connecter l'agent aux données applicatives, avec du contexte vérifiable."
        label="14 | rag"
    >
        <div class="sw-process">
            <div class="sw-process-step">Question</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">SimilaritySearch</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Documents</div>
            <div class="sw-process-arrow">→</div>
            <div class="sw-process-step">Réponse sourcée</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="Multi-agent"
        subtitle="Plusieurs agents, oui. Mais seulement avec une orchestration lisible."
        label="15 | multi-agent"
    >
        <x-darkwood.columns :count="3">
            <div class="sw-column"><h3>Subagents</h3><p>Spécialiser une tâche bornée.</p></div>
            <div class="sw-column"><h3>Handoff</h3><p>Passer le relais avec contexte.</p></div>
            <div class="sw-column"><h3>Traces</h3><p>Comprendre qui a décidé quoi.</p></div>
        </x-darkwood.columns>
    </x-darkwood.content>

    <x-darkwood.content
        title="MCP"
        subtitle="MCP ouvre la porte à des architectures agentiques interopérables."
        label="16 | mcp"
    >
        <div class="sw-process">
            <div class="sw-process-step">Symfony app</div>
            <div class="sw-process-arrow">↔</div>
            <div class="sw-process-step">MCP Server</div>
            <div class="sw-process-arrow">↔</div>
            <div class="sw-process-step">Agents<br>IDE<br>Outils externes</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.section
        title="Le vrai sujet n'est pas le chatbot. C'est l'orchestration."
        subtitle="Qui appelle quoi ? Avec quel contexte ? Sous quelles permissions ? Avec quelle trace ? Et comment on reprend la main quand le modèle sort du cadre ?"
        overline="Analyse Darkwood"
        label="17 | analyse"
    />

    <x-darkwood.content
        title="Stack cible"
        subtitle="Symfony AI + MCP + Flow + Navi + Uniflow."
        label="18 | stack"
    >
        <x-darkwood.columns :count="3">
            <div class="sw-column"><h3>Symfony AI</h3><p>Modèles, agents, store, bundles.</p></div>
            <div class="sw-column"><h3>MCP</h3><p>Interopérabilité des tools et contextes.</p></div>
            <div class="sw-column"><h3>Flow / Navi / Uniflow</h3><p>Workflows, navigation agentique, orchestration métier.</p></div>
        </x-darkwood.columns>
    </x-darkwood.content>

    <x-darkwood.conclusion
        title="Construire des features IA, pas seulement des prompts."
        subtitle="Briques Symfony + données produit + orchestration contrôlée. C'est là que Symfony AI devient intéressant pour une vraie application."
        label="19 | conclusion"
    />
</x-darkwood.deck>
