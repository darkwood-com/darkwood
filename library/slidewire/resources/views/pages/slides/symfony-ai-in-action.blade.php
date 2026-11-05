<x-darkwood.deck>
    <x-darkwood.cover
        title="Symfony AI in Action"
        subtitle="Construire des fonctionnalités IA réalistes avec Symfony"
        overline="Darkwood selection / SymfonyLive Berlin 2026"
    />

    <x-darkwood.content
        title="L'IA produit beaucoup de buzzwords. Le produit, lui, demande du contrôle."
        lead="Le vrai défi n'est pas de faire répondre un modèle. C'est de le brancher sur une application qui a des règles, des données, des droits et des effets de bord."
        overline="Le problème"
        label="01 / problème"
    />

    <x-darkwood.content
        title="Symfony AI ne vend pas un chatbot. Il donne une stack pour activer des features IA."
        overline="Vision"
        label="02 / vision"
    >
        <x-darkwood.columns :count="3">
            <div class="dw-original-column"><h3>Inférence</h3><p>Changer de modèle sans réécrire l'app.</p></div>
            <div class="dw-original-column"><h3>Contexte</h3><p>Relier l'IA aux données métier.</p></div>
            <div class="dw-original-column"><h3>Action</h3><p>Exposer du code Symfony contrôlé.</p></div>
        </x-darkwood.columns>
    </x-darkwood.content>

    <x-darkwood.section
        title="Une stack, pas un helper HTTP."
        subtitle="Platform · Agent · Store · AI Bundle · MCP Bundle · MCP SDK"
        overline="Les briques"
        label="03 / overview"
    />

    <x-darkwood.content
        title="Une abstraction d'inférence pour parler à plusieurs providers."
        overline="Platform"
        label="04 / platform"
    >
        <div class="dw-original-process">
            <div class="dw-original-process-step">Votre code PHP</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Platform</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">OpenAI<br>Claude<br>Gemini<br>Mistral</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="Le code applicatif reste stable."
        overline="Même intention, modèle différent"
        label="05 / providers"
        :top="true"
    >
        <x-darkwood.code><x-slidewire::code language="php">$result = $platform->request(
    model: new GptModel('gpt-4.1-mini'),
    input: 'Résume cette commande client.'
);</x-slidewire::code></x-darkwood.code>
        <div class="dw-original-content">
            <x-darkwood.columns :count="4">
                <div class="dw-original-column"><h3>OpenAI</h3></div>
                <div class="dw-original-column"><h3>Claude</h3></div>
                <div class="dw-original-column"><h3>Gemini</h3></div>
                <div class="dw-original-column"><h3>Mistral</h3></div>
            </x-darkwood.columns>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="Streamer, ce n'est pas juste afficher des mots plus vite."
        overline="Streaming"
        label="06 / streaming"
    >
        <x-darkwood.columns :count="3">
            <div class="dw-original-column"><h3>Deltas typés</h3><p>Texte, tool call, signal de fin.</p></div>
            <div class="dw-original-column"><h3>Usage tokens</h3><p>Observer le coût pendant la réponse.</p></div>
            <div class="dw-original-column"><h3>UX live</h3><p>Répondre sans bloquer l'interface.</p></div>
        </x-darkwood.columns>
    </x-darkwood.content>

    <x-darkwood.section
        title="L'entrée et la sortie ne sont plus seulement du texte."
        subtitle="Audio · Image · PDF · Binaire"
        overline="Multimodal"
        label="07 / multimodal"
    >
        <p class="dw-original-lead">Le modèle devient une pièce d'un pipeline média, pas une zone de texte magique.</p>
    </x-darkwood.section>

    <x-darkwood.content
        title="On veut des objets PHP fiables, pas du JSON fragile dans une string."
        overline="Structured Output"
        label="08 / structure"
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
        title="Un agent, c'est une boucle contrôlée entre modèle et application."
        overline="Agent"
        label="09 / agent"
    >
        <div class="dw-original-process">
            <div class="dw-original-process-step">LLM</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Tool call</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Service Symfony</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Réponse</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="Exposer du code métier avec une frontière explicite."
        overline="Tool Calling"
        label="10 / tools"
        :top="true"
    >
        <x-darkwood.code><x-slidewire::code language="php">#[AsTool('refund_order')]
public function refundOrder(string $orderId): RefundResult
{
    return $this->refunds->request($orderId);
}</x-slidewire::code></x-darkwood.code>
    </x-darkwood.content>

    <x-darkwood.content
        title="Le modèle peut proposer. L'application décide quand agir."
        overline="Human in the Loop"
        label="11 / hitl"
    >
        <div class="dw-original-process">
            <div class="dw-original-process-step">Tool call proposé</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Validation humaine</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Exécution ou refus</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="La mémoire utile vient souvent de votre application."
        overline="Memory"
        label="12 / memory"
    >
        <x-darkwood.columns :count="3">
            <div class="dw-original-column"><h3>Profil</h3><p>Préférences, rôle, langue.</p></div>
            <div class="dw-original-column"><h3>Historique</h3><p>Actions et conversations récentes.</p></div>
            <div class="dw-original-column"><h3>Permissions</h3><p>Ce que l'agent peut voir ou faire.</p></div>
        </x-darkwood.columns>
    </x-darkwood.content>

    <x-darkwood.content
        title="Le RAG commence avant la recherche vectorielle."
        overline="Store"
        label="13 / store"
    >
        <div class="dw-original-process">
            <div class="dw-original-process-step">Loading</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Filtering</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Transforming</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Vectorizing</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="Connecter l'agent aux données applicatives, avec du contexte vérifiable."
        overline="RAG"
        label="14 / rag"
    >
        <div class="dw-original-process">
            <div class="dw-original-process-step">Question</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">SimilaritySearch</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Documents</div>
            <div class="dw-original-process-arrow">→</div>
            <div class="dw-original-process-step">Réponse sourcée</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.content
        title="Plusieurs agents, oui. Mais seulement avec une orchestration lisible."
        overline="Multi-agent"
        label="15 / multi-agent"
    >
        <x-darkwood.columns :count="3">
            <div class="dw-original-column"><h3>Subagents</h3><p>Spécialiser une tâche bornée.</p></div>
            <div class="dw-original-column"><h3>Handoff</h3><p>Passer le relais avec contexte.</p></div>
            <div class="dw-original-column"><h3>Traces</h3><p>Comprendre qui a décidé quoi.</p></div>
        </x-darkwood.columns>
    </x-darkwood.content>

    <x-darkwood.content
        title="MCP ouvre la porte à des architectures agentiques interopérables."
        overline="MCP"
        label="16 / mcp"
    >
        <div class="dw-original-process">
            <div class="dw-original-process-step">Symfony app</div>
            <div class="dw-original-process-arrow">↔</div>
            <div class="dw-original-process-step">MCP Server</div>
            <div class="dw-original-process-arrow">↔</div>
            <div class="dw-original-process-step">Agents<br>IDE<br>Outils externes</div>
        </div>
    </x-darkwood.content>

    <x-darkwood.section
        title="Le vrai sujet n'est pas le chatbot. C'est l'orchestration."
        subtitle="Qui appelle quoi ? Avec quel contexte ? Sous quelles permissions ? Avec quelle trace ? Et comment on reprend la main quand le modèle sort du cadre ?"
        overline="Analyse Darkwood"
        label="17 / analyse"
    />

    <x-darkwood.content
        title="Symfony AI + MCP + Flow + Navi + Uniflow."
        overline="Stack cible"
        label="18 / stack"
    >
        <x-darkwood.columns :count="3">
            <div class="dw-original-column"><h3>Symfony AI</h3><p>Modèles, agents, store, bundles.</p></div>
            <div class="dw-original-column"><h3>MCP</h3><p>Interopérabilité des tools et contextes.</p></div>
            <div class="dw-original-column"><h3>Flow / Navi / Uniflow</h3><p>Workflows, navigation agentique, orchestration métier.</p></div>
        </x-darkwood.columns>
    </x-darkwood.content>

    <x-darkwood.conclusion
        title="Construire des features IA, pas seulement des prompts."
        subtitle="Briques Symfony + données produit + orchestration contrôlée. C'est là que Symfony AI devient intéressant pour une vraie application."
        label="19 / conclusion"
    />
</x-darkwood.deck>
