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
Darkwood / local inference infrastructure
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">NoLife Models</span>
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Exploring Local AI Runtime Infrastructure
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



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
  'class' => 'sw-heading dw-heading',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Thesis
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
We are moving from “chatbot APIs” to <span class="dw-accent">AI runtime infrastructure</span>.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Catalogs, local engines, evaluation harnesses, and exportable traces - the same primitives ops expects from any serious runtime.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | thesis" />
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
Agenda
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Five arcs - one pipeline.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Catalogs</h3><p>models.dev · modalities · cost · context.</p></div>
                <div class="dw-card"><h3>Runtimes</h3><p>Ollama as HTTP surface · future adapters.</p></div>
                <div class="dw-card"><h3>NoLife Models</h3><p>Compare · benchmark · export.</p></div>
                <div class="dw-card"><h3>Kronk</h3><p>Embedded inference - not “better Ollama”.</p></div>
                <div class="dw-card"><h3>Governance</h3><p>Ports, traces, reproducibility.</p></div>
                <div class="dw-card"><h3>Outlook</h3><p>Orchestration &amp; embedded edge.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | agenda" />
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
Part 1 - Catalog plane
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">models.dev</span> is a contract surface for the model economy.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Structured metadata: modalities, limits, pricing tiers, reasoning flags.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-flow">
                <div class="dw-node">Provider</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Model record</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Tooling &amp; SDKs</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | models.dev" />
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
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Live - models.dev
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
models.dev
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Public catalog in the browser. Some sites block iframes; if the frame is blank, open the URL in a new tab.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



            <div class="dw-embed dw-embed-demo dw-embed--live mt-4">
    <iframe src="https://models.dev/" title="models.dev" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allow="clipboard-read; clipboard-write"></iframe>
</div>

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | models.dev · site" />
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
Comparator plane
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">modles</span> - local browser lab for rows from the catalog.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Filter, sort by $/M, context, capabilities.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | modles" />
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
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Live - modles
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
modles
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Local comparator lab for catalog rows (this deck expects it on port 8001).
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



            <div class="dw-embed dw-embed-demo dw-embed--live mt-4">
    <iframe src="http://127.0.0.1:8001/" title="modles" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allow="clipboard-read; clipboard-write"></iframe>
</div>

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | modles · site" />
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
Analogy - shipping vs inference
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Docker ecosystem ↔ AI runtime ecosystem
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-map">
                <div><strong>Docker Hub</strong><span>models.dev</span></div>
                <div><strong>Images</strong><span>Model artifacts (GGUF, weights)</span></div>
                <div><strong>dockerd</strong><span>Ollama / Kronk / vLLM…</span></div>
                <div><strong>Kubernetes</strong><span>Agent &amp; batch orchestration</span></div>
                <div><strong>Observability</strong><span>Traces · exports · benchmarks</span></div>
                <div><strong>containerd / OCI</strong><span><code class="text-[var(--dw-cyan)]">LocalModelRuntimeInterface</code></span></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | analogy" />
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
Part 2 - Process-local HTTP runtime
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">Ollama</span> exposes models as <span class="dw-accent">local HTTP services</span>.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Inventory</h3><p><code>/api/tags</code> - what’s pulled, which quant.</p></div>
                <div class="dw-card"><h3>Inference</h3><p><code>/api/generate</code> - non-stream MVP in NoLife.</p></div>
                <div class="dw-card"><h3>Metadata</h3><p>Load time, eval counts, wall clock - rudimentary SRE signals.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | ollama" />
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
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Live - NoLife Models
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Catalog explorer
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Merged models.dev API + JSON overrides · live filters · modalities · $/M · context windows.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



            <div class="dw-embed dw-embed-demo dw-embed--live mt-4">
    <iframe src="http://127.0.0.1:8002/en/embed/catalog" title="NoLife catalog" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allow="clipboard-read; clipboard-write"></iframe>
</div>

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | demo · catalog" />
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
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Live - NoLife Models
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Installed local models
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Runtime discovery against your daemon - tags, quant, what’s actually callable.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



            <div class="dw-embed dw-embed-demo dw-embed--live mt-4">
    <iframe src="http://127.0.0.1:8002/en/embed/ollama" title="NoLife Ollama inventory" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allow="clipboard-read; clipboard-write"></iframe>
</div>

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | demo · ollama" />
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
Part 3 - Reference app
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">NoLife Models</span> - <span class="dw-accent">Symfony 8</span> + <span class="dw-accent">Symfony UX</span>
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
DDD / hexagonal core: catalog ports, <code class="text-[var(--dw-cyan)]">LocalModelRuntimeInterface</code>, exporters, session-backed results.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-card"><h3>UX Live</h3><p>Catalog explorer (Live Component) - filters without round-trips.</p></div>
                <div class="dw-card"><h3>Turbo</h3><p>Fast morphing between compare / results.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | nolife stack" />
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
Hexagonal slice
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Application ↔ ports ↔ adapters
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-embed dw-embed-demo" style="max-width:100%">
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  subgraph UI["UserInterface"]
    TW["Twig + Forms"]
    LC["LiveComponent"]
  end
  subgraph APP["Application"]
    RC["Compare handler"]
    RB["Benchmark handler"]
  end
  subgraph DOM["Domain"]
    LR(("LocalModelRuntime"))
    CR(("CatalogRepository"))
    EX(("Exporter"))
  end
  subgraph INF["Infrastructure"]
    OL["Ollama runtime"]
    MD["models.dev cache"]
    SE["Session + files"]
  end
  TW --&gt; RC
  LC --&gt; RC
  RC --&gt; LR
  RC --&gt; CR
  RB --&gt; LR
  RB --&gt; CR
  OL -.-&gt; LR
  MD -.-&gt; CR
  SE -.-&gt; EX</pre>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | architecture" />
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
Runtime port
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<code class="text-[var(--dw-cyan)]">LocalModelRuntimeInterface</code>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



@endverbatim
@php
$snippet = <<<'SW_CODE_13_0'
interface LocalModelRuntimeInterface
{
    /** @return list<LocalModel> */
    public function listLocalModels(): array;

    public function generate(GeneratePromptCommand $command): ModelInferenceResult;
}
SW_CODE_13_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | port" />
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
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Live - NoLife Models
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Compare two models
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Same prompt · two tags · wall clock + token-ish metrics - engineering comparison, not vibes.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



            <div class="dw-embed dw-embed-demo dw-embed--live mt-4">
    <iframe src="http://127.0.0.1:8002/en/embed/compare" title="NoLife compare" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allow="clipboard-read; clipboard-write"></iframe>
</div>

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | demo · compare" />
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
Evaluation plane
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Latency is cheap to measure. <span class="dw-accent">Quality is expensive.</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Metrics</h3><p>Duration, load, eval counts - reproducible numbers.</p></div>
                <div class="dw-card"><h3>Reasoning</h3><p>Side-by-side text for human triage.</p></div>
                <div class="dw-card"><h3>Next</h3><p>Structured rubrics · model grading pipelines.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="15 | compare concepts" />
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
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Live - NoLife Models
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Benchmark suite
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Micro-cases (EN/FR) · deterministic prompts · matrix results - orchestration over the same runtime port.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



            <div class="dw-embed dw-embed-demo dw-embed--live mt-4">
    <iframe src="http://127.0.0.1:8002/en/embed/benchmark" title="NoLife benchmark" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allow="clipboard-read; clipboard-write"></iframe>
</div>

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="16 | demo · benchmark" />
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
Orchestration
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Benchmark flow
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-embed dw-embed-demo" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  S[Suite definition] --&gt; M[Model A / Model B]
  M --&gt; P[Prompt cases]
  P --&gt; R[Runtime generate]
  R --&gt; T[Timing + text capture]
  T --&gt; X[Matrix + exports]</pre>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="17 | benchmark flow" />
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
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Live - NoLife Models
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Exports
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
JSON · CSV · Markdown - stable schema/version fields for downstream tooling.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



            <div class="dw-embed dw-embed-demo dw-embed--live mt-4">
    <iframe src="http://127.0.0.1:8002/en/embed/exports" title="NoLife exports" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allow="clipboard-read; clipboard-write"></iframe>
</div>

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="18 | demo · exports" />
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
Governance
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Inference without artifacts is <span class="dw-accent">faith</span>, not engineering.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-card"><h3>Audit trail</h3><p>Exports + session IDs - what ran, when, with which tags.</p></div>
                <div class="dw-card"><h3>Runtime governance</h3><p>Policies on top of the same port: quotas, routing, kill switches.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="19 | observability" />
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
Part 4 - Embedded inference
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">Kronk</span> is not “better Ollama”.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Ollama: models as <strong>local HTTP services</strong>. Kronk: push inference <strong>into the application process</strong> - GGUF via llama.cpp, streaming, OpenAI-compatible ergonomics where it fits.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="20 | kronk" />
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
Embedded stack
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Primitives
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-card"><h3>GGUF</h3><p>Single-file weights - ship like any binary asset.</p></div>
                <div class="dw-card"><h3>llama.cpp</h3><p>CPU/GPU inference core - predictable foot-print.</p></div>
                <div class="dw-card"><h3>Streaming</h3><p>Token deltas - UX + back-pressure aware servers.</p></div>
                <div class="dw-card"><h3>OpenAI-shaped APIs</h3><p>Interchange clients - ecosystem leverage without cloud lock-in.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="21 | embedded primitives" />
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
End-to-end
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Catalog → runtime → evaluation → export
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-embed dw-embed-demo" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  C[Catalog plane] --&gt; R[Runtime plane]
  R --&gt; E[Evaluation plane]
  E --&gt; O[Observability plane]</pre>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="22 | pipeline" />
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
Part 5 - Outlook
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Multi-runtime clusters · policy-aware routing · <span class="dw-accent">edge bundles</span> with embedded weights.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
The same governance patterns (ports, traces, exports) port from Symfony apps to agents to on-device runtimes.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="23 | future" />
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
Closing
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
We are moving from chatbot APIs to <span class="dw-accent">AI runtime infrastructure</span>.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
<span class="dw-accent">NoLife Models</span> is a thin, opinionated slice through that stack - catalogs, local engines, benchmarks, exports - built as a real Symfony product surface.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-12 dw-flow">
                <div class="dw-node">Catalog</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Runtime port</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Evaluation</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Artifacts</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="24 | outro" />
</x-darkwood.slide>
</x-darkwood.deck>
