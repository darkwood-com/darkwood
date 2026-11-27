<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Systemes multi-agent partout
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Pourquoi maintenant ?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="mx-auto grid w-full max-w-6xl grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] items-center gap-9 sm:gap-8">
    <div class="min-w-0"><div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p>Infrastructure : Docker orchestre deja des services autonomes.</p>
</div>
                    <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p>Organisation : Paperclip + Hermes pilotent le travail agentique.</p>
</div>
                    <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p>Application : Symfony AI rend les agents actionnables dans le produit.</p>
</div></div>
    </div>

    </div></div>
    <div class="min-w-0"><pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TD
    A[Docker] --&gt; B[Paperclip + Hermes]
    B --&gt; C[Symfony]
    C --&gt; D[Flow]
    D --&gt; E[Navi]</pre></div>
</div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Sans gouvernance
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Le probleme
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-markdown"><p>Les demos multi-agent deviennent vite des <strong>chaines de prompts opaques</strong> :</p>
<ul>
<li>contrats flous entre agents</li>
<li>peu de controles pre/post execution</li>
<li>traces insuffisantes pour auditer</li>
<li>risques sur couts, securite et qualite</li>
</ul>
</div></div>
    </div>

    </div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
MVP Symfony 8
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
L'idee NoLife Agency
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="mx-auto grid w-full max-w-6xl grid-cols-1 lg:grid-cols-2 items-center gap-7 sm:gap-6">
    <div class="min-w-0"><div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-markdown"><p>Une agence digitale modelisee comme un systeme d'agents, avec un objectif :</p>
<p><strong>autonomie utile sous gouvernance explicite</strong>.</p>
</div></div>
    </div>

    </div></div>
    <div class="min-w-0"><pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
    C[Client] --&gt; A[NoLife Agency]
    A --&gt; R[Roles agents]
    A --&gt; P[Politiques]
    A --&gt; T[Traces]</pre></div>
</div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Ports / Adapters
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Vue architecture globale
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="relative rounded-[2rem]">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
    App[Symfony App] --&gt; Runner[AgentRunner]
    Runner --&gt; AI[Symfony AI Port]
    Runner --&gt; FLOW[Flow Port]
    Runner --&gt; NAVI[Navi Port]
    Runner --&gt; MEDIA[Media Port]</pre></div>
    </div>

    </div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Contrats stables
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Ports exposes
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="mx-auto grid min-h-full w-full max-w-6xl grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] items-start gap-7 sm:gap-6">
    <div class="min-w-0">
@endverbatim
@php
$snippet = <<<'SW_CODE_4_0'
interface AiAgentClient {}
interface FlowWorkflowClient {}
interface NaviTraceClient {}
interface MediaGenerationClient {}

final class AgentRunner
{
    // Frontiere unique de gouvernance
}
SW_CODE_4_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim
</div>
    <div class="min-w-0"><div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-markdown"><ul>
<li><code>AiAgentClient</code></li>
<li><code>FlowWorkflowClient</code></li>
<li><code>NaviTraceClient</code></li>
<li><code>MediaGenerationClient</code></li>
</ul>
</div></div>
    </div>

    </div></div>
</div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Single governance boundary
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
AgentRunner
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-markdown"><p>Point d'application unique pour :</p>
<ul>
<li>politiques d'execution</li>
<li>audit des decisions</li>
<li>cycle de vie des traces</li>
<li>dispatch des workflows YAML applicatifs</li>
</ul>
</div></div>
    </div>

    </div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Control plane
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Modele de gouvernance
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="mx-auto grid w-full max-w-6xl grid-cols-1 lg:grid-cols-2 items-center gap-7 sm:gap-6">
    <div class="min-w-0"><div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p>Pre-checks: contexte, droits, policies.</p>
</div>
                    <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p>Approval gates: validation humaine obligatoire.</p>
</div>
                    <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p>Post-checks: qualite, securite, coherence.</p>
</div>
                    <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p>Artifact path safety: ecriture bornees et auditables.</p>
</div></div>
    </div>

    </div></div>
    <div class="min-w-0"><pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TD
    A[Pre-checks] --&gt; B[Execution]
    B --&gt; C[Approval gate]
    C --&gt; D[Post-checks]
    D --&gt; E[Audit + Trace]</pre></div>
</div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Etat metier
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Workflow client website
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="relative rounded-[2rem]">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><pre class="mermaid slidewire-diagram" data-slidewire-diagram>stateDiagram-v2
    [*] --&gt; lead_qualified
    lead_qualified --&gt; brief_created
    brief_created --&gt; architecture_defined
    architecture_defined --&gt; content_generated
    content_generated --&gt; seo_optimized
    seo_optimized --&gt; media_brief_created
    media_brief_created --&gt; delivery_reviewed
    delivery_reviewed --&gt; human_approval_required</pre></div>
    </div>

    </div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
CLI de reference
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Commande de demo
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="mx-auto grid min-h-full w-full max-w-6xl grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] items-center gap-7 sm:gap-6">
    <div class="min-w-0"><div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none">
@endverbatim
@php
$snippet = <<<'SW_CODE_8_0'
php bin/console nolife:demo:client-website acme-saas \
  --real-ai \
  --real-flow \
  --real-navi \
  --real-media \
  --dry-run
SW_CODE_8_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="shellscript" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim
</div>
    </div>

    </div></div>
    <div class="min-w-0"><div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">

                            <h3 class="max-w-[24ch] text-balance font-semibold tracking-tight">Options</h3>
                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-markdown"><ul>
<li><code>--real-ai</code></li>
<li><code>--real-flow</code></li>
<li><code>--real-navi</code></li>
<li><code>--real-media</code></li>
<li><code>--dry-run</code></li>
</ul>
</div></div>
    </div>

    </div></div>
</div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Artifacts generes
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Sorties runtime
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none">
@endverbatim
@php
$snippet = <<<'SW_CODE_9_0'
brief.json
article-draft.md
seo-geo.md
media-brief.yaml
delivery-report.md
trace.json
audit.jsonl
SW_CODE_9_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim
</div>
    </div>

    </div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Mode local demo
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Null adapters
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="mx-auto grid min-h-full w-full max-w-6xl grid-cols-1 lg:grid-cols-2 items-center gap-7 sm:gap-6">
    <div class="min-w-0"><div class="relative overflow-hidden rounded-[2rem] border border-dashed">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-markdown"><ul>
<li>deterministic</li>
<li>rapide</li>
<li>sans dependances externes</li>
<li>parfait pour onboarding et tests</li>
</ul>
</div></div>
    </div>

    </div></div>
    <div class="min-w-0"><div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">
                            <p class="text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em]">Integrations swappables</p>

                            <h3 class="max-w-[24ch] text-balance font-semibold tracking-tight">Real adapters</h3>
                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-markdown"><p>Activation par configuration :</p>
<ul>
<li>IA reelle</li>
<li>workflow reel</li>
<li>trace reelle</li>
<li>generation media reelle</li>
</ul>
</div></div>
    </div>

    </div></div>
</div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Message cle
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Ce que NoLife Agency prouve
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-markdown"><p>L'autonomie ne suffit pas.</p>
<p>Il faut :</p>
<ul>
<li>de la gouvernance</li>
<li>des contrats de ports clairs</li>
<li>une orchestration explicite</li>
<li>des traces exploitables pour l'audit</li>
</ul>
</div></div>
    </div>

    </div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline text-sm/6 sm:text-[0.8125rem]/6 font-medium tracking-[0.24em] text-cyan-200',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl text-white text-3xl sm:text-2xl',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Roadmap
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Next steps
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<div class="relative overflow-hidden rounded-[2rem] border">
            <header class="relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7">



                    </header>

    <div class="relative px-6 py-5 sm:px-7 sm:py-6 px-6 py-5 sm:px-7 sm:py-6">
        <div class="max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 max-w-none"><div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p>Symfony AI real adapter</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p>Flow real bridge</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p>Navi trace bridge</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p>Bonzai premium content</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <p>Uniflow packaging</p>
</div></div>
    </div>

    </div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>
</x-darkwood.deck>
