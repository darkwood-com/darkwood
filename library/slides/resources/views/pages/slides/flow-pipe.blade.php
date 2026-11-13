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
Darkwood · PHP 8.5 · <span class="dw-accent">Flow</span> · Symfony
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Flow Tokens
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Token optimization is<br><span class="dw-accent">stream discipline</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <p class="dw-note">Symfony Console demo · <code>flow-pipe</code></p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Noisy context</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Pipeline</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Budgeted signal</div>
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
The problem
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Agentic workflows consume too many tokens.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-grid dw-grid-3">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>Engine logs</h3><p>Verbose, repetitive, ANSI-colored.</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>Tool outputs</h3><p>Stack traces, debug lines, boilerplate.</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>RAG dumps</h3><p>Useful signal buried in noise.</p></div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p class="dw-question mt-8">Context floods the agent - before any reasoning starts.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | problem" />
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
Wrong instinct
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The issue is not word shortening.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-split mt-8">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>Abbreviate vocabulary</h3>
                        <p style="margin:0">waterfall → wf<br>pipeline → pln<br>stream → strm</p>
                        <p class="dw-muted mt-4" style="margin-bottom:0">Destroys readability. Saves almost nothing.</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Stream discipline</h3>
                        <ul class="dw-list-compact" style="margin:0">
                            <li>Strip noise</li>
                            <li>Chunk bounded segments</li>
                            <li>Compress duplicates</li>
                            <li>Apply a token budget</li>
                        </ul>
                    </div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | not-shortening" />
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
Root cause
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Uncontrolled context.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  LOG[Engine logs]
  TOOL[Tool outputs]
  TRACE[Repeated traces]
  CHUNK[Noisy chunks]
  CTX[Agent context window]
  LOG --&gt; CTX
  TOOL --&gt; CTX
  TRACE --&gt; CTX
  CHUNK --&gt; CTX</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4">Data enters as one giant string - not as a disciplined stream.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | root-cause" />
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
Thesis
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Token optimization is not word shortening.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <h3 class="dw-heading mt-6">Token optimization is <span class="dw-accent">stream discipline</span>.</h3>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-note mt-8">How data moves - not how words are spelled.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | thesis" />
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
The <span class="dw-accent">Flow</span> angle
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">Flow</span> is not only about running tasks.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
It controls how data moves:
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



            <div class="mt-6 dw-grid dw-grid-3" style="grid-template-columns:repeat(3,1fr);gap:12px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-chip">as a pipe</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">as a stream</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">as chunks</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">as measurable context</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-chip">as budgeted context</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <div class="dw-chip">for humans · machines · agents</div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | flow-angle" />
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
The demo
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<code>flow-pipe</code>
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
A Symfony Console project that runs a token pipeline through <span class="dw-accent">Flow</span>.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_6_0'
php bin/console app:flow-token-demo \
  --input=flow-engine-log --show-chunks
SW_CODE_6_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-note mt-4">Deterministic. Local. No external API.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | demo" />
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
Architecture
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Three layers. One pipeline.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  CMD[FlowTokenDemoCommand\napp:flow-token-demo]
  SVC[TokenPipelineService]
  RUN[TokenPipelineFlowRunner]
  PAR[PipelineExpressionParser]
  REG[PipelineStepRegistry]
  CTX[PipelineContext]
  FIX[FlowFixtureProvider]
  FF[FlowFactory + FiberDriver]
  CMD --&gt; SVC
  SVC --&gt; PAR
  SVC --&gt; RUN
  PAR --&gt; REG
  RUN --&gt; FF
  RUN --&gt; CTX
  FIX --&gt; CTX</pre>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | architecture" />
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
Project structure
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Application · Infrastructure · Domain
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-split" style="grid-template-columns:1fr 1fr 1fr">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Command</h3>
                        <p><code>FlowTokenDemoCommand</code></p>
                        <p class="dw-muted">CLI I/O only</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Application</h3>
                        <p><code>TokenPipelineService</code></p>
                        <p class="dw-muted">Parse + run facade</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Infrastructure</h3>
                        <p><code>TokenPipelineFlowRunner</code></p>
                        <p class="dw-muted">Full pipeline via FlowFactory</p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="mt-4 dw-card">
                    <h3>TokenFlow domain</h3>
                    <div class="mt-3 dw-flow" style="flex-wrap:wrap;justify-content:center;gap:8px">
                        <div class="dw-chip">PipelineExpressionParser</div>
                        <div class="dw-chip">PipelineStepRegistry</div>
                        <div class="dw-chip">PipelineContext</div>
                        <div class="dw-chip">FlowFixtureProvider</div>
                        <div class="dw-chip">Step classes</div>
                    </div>
                </div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | structure" />
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
Pipeline steps
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Registered operations - not hardcoded branches.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-flow dw-flow--vertical" style="max-width:720px;margin:0 auto;gap:6px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-pipeline-row is-active"><span class="dw-pipeline-label">source</span><span>Load fixture into stream</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">strip_ansi</span><span>Remove escape sequences</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">remove_noise</span><span>Drop debug / trace lines</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">normalize_whitespace</span><span>Collapse blank runs</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">chunk:300</span><span>Split into ~300-char segments</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">compress</span><span>Deduplicate &amp; collapse repeats</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="6" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">budget:1000</span><span>Keep chunks until token limit</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="7" data-fragment="data-fragment">
    <div class="dw-pipeline-row is-active"><span class="dw-pipeline-label">sink</span><span>Flush output &amp; metrics</span></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | steps" />
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
Pipeline expression
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Declarative. Shareable. Readable.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



@endverbatim
@php
$snippet = <<<'SW_CODE_10_0'
source |> strip_ansi |> remove_noise |> normalize_whitespace
  |> chunk:300 |> compress |> budget:1000 |> sink
SW_CODE_10_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-note mt-6"><code>PipelineExpressionParser</code> splits on <span class="dw-accent">|&gt;</span> and resolves names through <code>PipelineStepRegistry</code>.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | expression" />
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
Three <span class="dw-accent">|&gt;</span> semantics
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Same symbol. Three layers.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-3">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Expression DSL</h3>
                        <p>String pipeline language</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_11_0'
"source |> sink"
SW_CODE_11_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>PHP 8.5 <span class="dw-accent">|&gt;</span></h3>
                        <p>Step → ClosureJob mapping</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_11_1'
$step |> (fn ($s) => ...)
SW_CODE_11_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3><span class="dw-accent">Flow</span> fn()</h3>
                        <p>Runtime composition</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_11_2'
$flow->fn($job)
SW_CODE_11_2;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Visual rhythm of the DSL - at Flow composition time.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | three-pipes" />
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
Flow runtime
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Full pipeline. One graph.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_12_0'
$toJob = fn ($s) => new ClosureJob(fn ($ctx) => $s->apply($ctx));

$flow = (new FlowFactory())->createFlow(
    $firstStep |> $toJob,
    ['driver' => $driver],
);

foreach ($remaining as $step) {
    $flow = $flow->fn($step |> $toJob);
}

$flow(new Ip($context));
$flow->await();
SW_CODE_12_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-note mt-4"><code>TokenPipelineFlowRunner</code> - each step becomes a <code>ClosureJob</code> on <code>PipelineContext</code>.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | runtime" />
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
The result
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
From flood to signal.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  N[Noisy input\n17k chars]
  C[Cleaned stream\nstrip + normalize]
  K[Chunked data\n~300 char segments]
  Z[Compressed context\ndeduped lines]
  B[Budgeted output\n≤1000 tokens]
  N --&gt; C --&gt; K --&gt; Z --&gt; B</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-note mt-4">Fixture: <code>flow-engine-log</code> · heuristic ~1 token / 4 chars</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | stages" />
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
Before / after
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<code>flow-engine-log</code>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-split mt-8">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>Before</h3>
                        <p style="margin:0">4,488 <span>est. tokens</span></p>
                        <p class="dw-muted" style="margin:8px 0 0">17,398 characters</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>After</h3>
                        <p style="margin:0">88 <span>est. tokens</span></p>
                        <p class="dw-muted" style="margin:8px 0 0">334 characters · <strong>98% reduction</strong></p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Domain vocabulary intact. Noise and repetition removed.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | numbers" />
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
Signal vs noise
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<code>flow-lexicon</code> resists compression.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-split mt-8">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel">
                        <h3>Before</h3>
                        <p style="margin:0">371 est. tokens</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel">
                        <h3>After</h3>
                        <p style="margin:0">354 est. tokens · 4.6%</p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Semantic content carries signal - abbreviating words is not the strategy.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="15 | contrast" />
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
Tomorrow
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
From CLI demo to async streams.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-grid dw-grid-3" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-chip">Non-blocking streams</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">stream_select</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">Fibers</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">Process pipes</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-chip">PTY / TTY modes</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <div class="dw-chip">Event loop drivers</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="6" data-fragment="data-fragment">
    <div class="dw-chip">Async workers</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="7" data-fragment="data-fragment">
    <div class="dw-chip"><span class="dw-accent">Flow</span> orchestration layer</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="8" data-fragment="data-fragment">
    <p class="dw-note mt-6">Read process stdout as it arrives - not after <code>wait()</code>.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="16 | future" />
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
Takeaway
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Control the stream.<br>Not the spelling.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="mt-8 dw-flow">
                    <div class="dw-node">Expression</div>
                    <div class="dw-arrow"><span class="dw-accent">|&gt;</span></div>
                    <div class="dw-node"><span class="dw-accent">Flow</span></div>
                    <div class="dw-arrow"><span class="dw-accent">|&gt;</span></div>
                    <div class="dw-node">Budgeted context</div>
                </div>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-note mt-8">Token optimization is stream discipline.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="17 | takeaway" />
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
Resources
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Go deeper.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <ul class="dw-list mt-8">
                <li><code>darkwood/content/flow-pipe</code> - Symfony demo repo</li>
                <li><code>app:flow-token-demo</code> - run the pipeline locally</li>
                <li><code>darkwood/flow</code> - orchestration package</li>
                <li>PHP.Watch - PHP 8.5 pipe operator</li>
            </ul>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-note mt-8">Inspired by a LinkedIn post on agentic context - cited at the end of the article.</p>
</div>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Noisy logs</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Stream pipeline</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Agent-ready context</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>
</x-darkwood.deck>
