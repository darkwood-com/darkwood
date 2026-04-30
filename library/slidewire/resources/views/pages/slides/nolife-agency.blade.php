<x-slidewire::deck theme="aurora" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">
    <x-slidewire::title-slide
        title="NoLife Agency"
        subtitle="Construire une architecture multi-agent gouvernee avec Symfony AI, Flow et Navi"
        overline="Article technique Darkwood"
        speaker="@matyo91"
        event="Developer Conference Deck"
        align="left"
        variant="hero"
    />

    <x-slidewire::slide>
        <x-slidewire::two-column-slide ratio="2:1" gap="xl" align="center">
            <x-slot name="left">
                <x-slidewire::panel title="Pourquoi maintenant ?" overline="Systemes multi-agent partout" variant="glass">
                    <x-slidewire::fragment :index="0">
                        <p>Infrastructure : Docker orchestre deja des services autonomes.</p>
                    </x-slidewire::fragment>
                    <x-slidewire::fragment :index="1">
                        <p>Organisation : Paperclip + Hermes pilotent le travail agentique.</p>
                    </x-slidewire::fragment>
                    <x-slidewire::fragment :index="2">
                        <p>Application : Symfony AI rend les agents actionnables dans le produit.</p>
                    </x-slidewire::fragment>
                </x-slidewire::panel>
            </x-slot>
            <x-slot name="right">
                <x-slidewire::diagram>
flowchart TD
    A[Docker] --> B[Paperclip + Hermes]
    B --> C[Symfony]
    C --> D[Flow]
    D --> E[Navi]
                </x-slidewire::diagram>
            </x-slot>
        </x-slidewire::two-column-slide>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::panel title="Le probleme" overline="Sans gouvernance" variant="elevated">
            <x-slidewire::markdown>
Les demos multi-agent deviennent vite des **chaines de prompts opaques** :

- contrats flous entre agents
- peu de controles pre/post execution
- traces insuffisantes pour auditer
- risques sur couts, securite et qualite
            </x-slidewire::markdown>
        </x-slidewire::panel>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::two-column-slide ratio="1:1" align="center">
            <x-slot name="left">
                <x-slidewire::panel title="L'idee NoLife Agency" overline="MVP Symfony 8" variant="glass">
                    <x-slidewire::markdown>
Une agence digitale modelisee comme un systeme d'agents, avec un objectif :

**autonomie utile sous gouvernance explicite**.
                    </x-slidewire::markdown>
                </x-slidewire::panel>
            </x-slot>
            <x-slot name="right">
                <x-slidewire::diagram>
flowchart LR
    C[Client] --> A[NoLife Agency]
    A --> R[Roles agents]
    A --> P[Politiques]
    A --> T[Traces]
                </x-slidewire::diagram>
            </x-slot>
        </x-slidewire::two-column-slide>
    </x-slidewire::slide>

    <x-slidewire::agenda-slide title="Roles de l'agence" subtitle="Organisation complete" style="cards">
        <x-slidewire::agenda-item title="Direction" description="CEO, CTO, Governance Manager, Directeur de clientele, PO, Delivery Manager" />
        <x-slidewire::agenda-item title="Production" description="Architecte, Developer, UX Designer, UI Designer, Graphiste, Motion Designer, QA" />
        <x-slidewire::agenda-item title="Growth" description="SEO/GEO, Marketing, Sales, Web Analytics, Data Analyst" />
        <x-slidewire::agenda-item title="Operations" description="DevOps, Security Officer, Experience Officer, Office Manager, Happiness Chief Officer, Nolife Resource" />
    </x-slidewire::agenda-slide>

    <x-slidewire::slide>
        <x-slidewire::panel title="Vue architecture globale" overline="Ports / Adapters" variant="outlined">
            <x-slidewire::diagram>
flowchart LR
    App[Symfony App] --> Runner[AgentRunner]
    Runner --> AI[Symfony AI Port]
    Runner --> FLOW[Flow Port]
    Runner --> NAVI[Navi Port]
    Runner --> MEDIA[Media Port]
            </x-slidewire::diagram>
        </x-slidewire::panel>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::two-column-slide ratio="2:1" align="start">
            <x-slot name="left">
                <x-slidewire::code language="php">
interface AiAgentClient {}
interface FlowWorkflowClient {}
interface NaviTraceClient {}
interface MediaGenerationClient {}

final class AgentRunner
{
    // Frontiere unique de gouvernance
}
                </x-slidewire::code>
            </x-slot>
            <x-slot name="right">
                <x-slidewire::panel title="Ports exposes" overline="Contrats stables" variant="glass">
                    <x-slidewire::markdown>
- `AiAgentClient`
- `FlowWorkflowClient`
- `NaviTraceClient`
- `MediaGenerationClient`
                    </x-slidewire::markdown>
                </x-slidewire::panel>
            </x-slot>
        </x-slidewire::two-column-slide>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::panel title="AgentRunner" overline="Single governance boundary" variant="elevated">
            <x-slidewire::markdown>
Point d'application unique pour :

- politiques d'execution
- audit des decisions
- cycle de vie des traces
- dispatch des workflows YAML applicatifs
            </x-slidewire::markdown>
        </x-slidewire::panel>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::two-column-slide ratio="1:1" align="center">
            <x-slot name="left">
                <x-slidewire::panel title="Modele de gouvernance" overline="Control plane" variant="glass">
                    <x-slidewire::fragment :index="0"><p>Pre-checks: contexte, droits, policies.</p></x-slidewire::fragment>
                    <x-slidewire::fragment :index="1"><p>Approval gates: validation humaine obligatoire.</p></x-slidewire::fragment>
                    <x-slidewire::fragment :index="2"><p>Post-checks: qualite, securite, coherence.</p></x-slidewire::fragment>
                    <x-slidewire::fragment :index="3"><p>Artifact path safety: ecriture bornees et auditables.</p></x-slidewire::fragment>
                </x-slidewire::panel>
            </x-slot>
            <x-slot name="right">
                <x-slidewire::diagram>
flowchart TD
    A[Pre-checks] --> B[Execution]
    B --> C[Approval gate]
    C --> D[Post-checks]
    D --> E[Audit + Trace]
                </x-slidewire::diagram>
            </x-slot>
        </x-slidewire::two-column-slide>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::panel title="Workflow client website" overline="Etat metier" variant="outlined">
            <x-slidewire::diagram>
stateDiagram-v2
    [*] --> lead_qualified
    lead_qualified --> brief_created
    brief_created --> architecture_defined
    architecture_defined --> content_generated
    content_generated --> seo_optimized
    seo_optimized --> media_brief_created
    media_brief_created --> delivery_reviewed
    delivery_reviewed --> human_approval_required
            </x-slidewire::diagram>
        </x-slidewire::panel>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::two-column-slide ratio="2:1" align="center">
            <x-slot name="left">
                <x-slidewire::panel title="Commande de demo" overline="CLI de reference" variant="elevated">
                    <x-slidewire::code language="bash">
php bin/console nolife:demo:client-website acme-saas \
  --real-ai \
  --real-flow \
  --real-navi \
  --real-media \
  --dry-run
                    </x-slidewire::code>
                </x-slidewire::panel>
            </x-slot>
            <x-slot name="right">
                <x-slidewire::panel title="Options" variant="glass">
                    <x-slidewire::markdown>
- `--real-ai`
- `--real-flow`
- `--real-navi`
- `--real-media`
- `--dry-run`
                    </x-slidewire::markdown>
                </x-slidewire::panel>
            </x-slot>
        </x-slidewire::two-column-slide>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::panel title="Sorties runtime" overline="Artifacts generes" variant="glass">
            <x-slidewire::code language="text">
brief.json
article-draft.md
seo-geo.md
media-brief.yaml
delivery-report.md
trace.json
audit.jsonl
            </x-slidewire::code>
        </x-slidewire::panel>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::two-column-slide ratio="1:1" align="center">
            <x-slot name="left">
                <x-slidewire::panel title="Null adapters" overline="Mode local demo" variant="outlined">
                    <x-slidewire::markdown>
- deterministic
- rapide
- sans dependances externes
- parfait pour onboarding et tests
                    </x-slidewire::markdown>
                </x-slidewire::panel>
            </x-slot>
            <x-slot name="right">
                <x-slidewire::panel title="Real adapters" overline="Integrations swappables" variant="elevated">
                    <x-slidewire::markdown>
Activation par configuration :

- IA reelle
- workflow reel
- trace reelle
- generation media reelle
                    </x-slidewire::markdown>
                </x-slidewire::panel>
            </x-slot>
        </x-slidewire::two-column-slide>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::panel title="Ce que NoLife Agency prouve" overline="Message cle" variant="glass">
            <x-slidewire::markdown>
L'autonomie ne suffit pas.

Il faut :

- de la gouvernance
- des contrats de ports clairs
- une orchestration explicite
- des traces exploitables pour l'audit
            </x-slidewire::markdown>
        </x-slidewire::panel>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::panel title="Next steps" overline="Roadmap" variant="elevated">
            <x-slidewire::fragment :index="0"><p>Symfony AI real adapter</p></x-slidewire::fragment>
            <x-slidewire::fragment :index="1"><p>Flow real bridge</p></x-slidewire::fragment>
            <x-slidewire::fragment :index="2"><p>Navi trace bridge</p></x-slidewire::fragment>
            <x-slidewire::fragment :index="3"><p>Bonzai premium content</p></x-slidewire::fragment>
            <x-slidewire::fragment :index="4"><p>Uniflow packaging</p></x-slidewire::fragment>
        </x-slidewire::panel>
    </x-slidewire::slide>
</x-slidewire::deck>
