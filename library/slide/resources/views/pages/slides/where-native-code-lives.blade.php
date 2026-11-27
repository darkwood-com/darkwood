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
Darkwood · PHP architecture · 2026
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Where Should Native Code Live?
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Phalcon, Symfony, <span class="dw-accent">TrueAsync</span> and <span class="dw-accent">Flow</span><br>at different layers of the stack
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-flow">
                <div class="dw-node">Framework</div>
                <div class="dw-arrow">·</div>
                <div class="dw-node">Application</div>
                <div class="dw-arrow">·</div>
                <div class="dw-node">Runtime</div>
                <div class="dw-arrow">·</div>
                <div class="dw-node">Orchestration</div>
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
The context
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A client architecture POC - a familiar dilemma.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-split">
                <div class="dw-split-panel is-warn">
                    <h3>The visible question</h3>
                    <p style="margin:0">Should we migrate from Phalcon to Symfony?</p>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>The deeper question</h3>
                    <p style="margin:0">Where should performance, boundaries, and workflows live?</p>
                </div>
            </div>
            <p class="dw-note mt-8">High-traffic PHP platform · proven performance · growing workflow complexity · maintenance pressure.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | context" />
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
The false debate
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Phalcon vs Symfony vs <span class="dw-accent">TrueAsync</span>
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
They are not direct competitors.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-chip">Different layers</div>
                <div class="dw-chip">Different constraints</div>
                <div class="dw-chip">Different tradeoffs</div>
            </div>
            <p class="dw-question mt-10">Framework choice is only part of the architecture.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | false-debate" />
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
Three native layers
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Each technology optimizes a different concern.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-map">
                <div><strong>Phalcon</strong><span>Native framework layer - MVC, router, ORM, cache</span></div>
                <div><strong>Symfony</strong><span>Application structure - DI, security, ecosystem, boundaries</span></div>
                <div><strong>TrueAsync</strong><span>Native runtime - coroutines, async I/O, execution</span></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | layers" />
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
Phalcon
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Phalcon was not a mistake.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-card"><h3>C extension</h3><p>Zephir/C · low overhead on the MVC path.</p></div>
                <div class="dw-card"><h3>Read-heavy fit</h3><p>Predictable latency · monolith APIs · cache-friendly.</p></div>
                <div class="dw-card"><h3>Still maintained</h3><p>v5.x active · releases in 2026 · ecosystem backers.</p></div>
                <div class="dw-card"><h3>Valid choice</h3><p>When MVC performance and team expertise still dominate.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | phalcon" />
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
The shift
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The optimization target changed.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-split mt-8">
                <div class="dw-split-panel">
                    <h3>Before</h3>
                    <ul class="dw-list-compact" style="margin:0">
                        <li>MVC bootstrap</li>
                        <li>Dispatch cost</li>
                        <li>Framework overhead</li>
                    </ul>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Now</h3>
                    <ul class="dw-list-compact" style="margin:0">
                        <li>I/O in workers</li>
                        <li>Multi-step workflows</li>
                        <li>Domain boundaries</li>
                        <li>Interop &amp; hiring</li>
                    </ul>
                </div>
            </div>
            <p class="dw-note mt-8">Obsolete? Wrong question. <strong>What optimization matters now?</strong></p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | optimization" />
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
Where should native code live?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  subgraph orch [Orchestration layer]
    W[Workflows and pipelines]
  end
  subgraph app [Application layer]
    S[Symfony structure\nboundaries DI ecosystem]
  end
  subgraph fw [Framework layer]
    P[Phalcon native MVC]
  end
  subgraph rt [Runtime layer]
    T[TrueAsync ext-async\noptional execution]
  end
  Q[Where does complexity live?]
  Q --&gt; orch
  Q --&gt; app
  Q --&gt; fw
  Q --&gt; rt</pre>
            </div>
            <p class="dw-note mt-4">Four independent decisions - not one migration checkbox.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | where-native" />
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
Boundaries
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Boundaries before frameworks.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Many migrations fail because the container changes - not the domain.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-chip">Bounded contexts</div>
                <div class="dw-chip">Domain vs application services</div>
                <div class="dw-chip">CQRS / read models</div>
                <div class="dw-chip">Eventual consistency</div>
            </div>
            <p class="dw-takeaway mt-8">Redraw boundaries first. Framework second.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | boundaries" />
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
Archetype · PEFT
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Read-heavy editorial platform pattern.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  IN[Ingestion] --&gt; PUB[Publication]
  PUB --&gt; STORE[(Canonical store)]
  PUB --&gt; INV[Invalidation]
  INV --&gt; RM[Read models]
  RM --&gt; API[Read APIs]</pre>
            </div>
            <div class="mt-6 dw-grid dw-grid-3">
                <div class="dw-chip">Event-driven</div>
                <div class="dw-chip">Derived views</div>
                <div class="dw-chip">Cache-heavy read path</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | peft" />
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
Orchestration
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Frameworks excel at request → response.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Mature systems exceed that cycle.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-flow">
                <div class="dw-node">Validate</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Persist</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Invalidate</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Rebuild</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Emit</div>
            </div>
            <p class="dw-note mt-8">Multi-step workflows need a first-class architectural place.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | orchestration" />
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
<span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Same workflow. Interchangeable execution.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
<code>DriverInterface</code> - business logic stable, execution model changes.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



            <div class="mt-6 dw-grid dw-grid-3">
                <div class="dw-chip">Fiber <span class="dw-muted">default</span></div>
                <div class="dw-chip">Amp</div>
                <div class="dw-chip">ReactPHP</div>
                <div class="dw-chip">Swoole</div>
                <div class="dw-chip">TrueAsync <span class="dw-muted">experimental</span></div>
            </div>

@endverbatim
@php
$snippet = <<<'SW_CODE_10_0'
$flow = (new Flow(job: new Validate(), driver: $driver))
    ->fn(new Persist())
    ->fn(new RebuildReadModel());
SW_CODE_10_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="dw-note mt-4"><span class="dw-accent">Flow</span> explores orchestration as an architectural primitive - not a framework replacement.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | flow" />
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
Paths
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Four credible outcomes - none is default.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-map">
                <div><strong>01</strong><span>Stay on Phalcon · modernize incrementally</span></div>
                <div><strong>02</strong><span>Migrate to Symfony · ecosystem &amp; structure</span></div>
                <div><strong>03</strong><span>Hybrid · coexistence with clear API contracts</span></div>
                <div><strong>04</strong><span>Runtime async · independent of web framework</span></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | paths" />
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
Anti-patterns
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What not to do.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <ul class="mt-10 dw-list">
                <li>Migrate by reflex - when SLOs and team fit are fine</li>
                <li>Put async everywhere - <span class="dw-accent">TrueAsync</span> is experimental, opt-in only</li>
                <li>Change framework without redrawing boundaries</li>
                <li>Confuse framework migration with workflow design</li>
                <li>Turn <span class="dw-accent">Flow</span> into a framework for every feature</li>
            </ul>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | anti-patterns" />
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
  'class' => 'sw-subtitle dw-lead mt-8',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Conclusion
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Framework choice still matters.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
The deeper questions:
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-8 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Workflows</h3><p>Where do multi-step pipelines live?</p></div>
                <div class="dw-card"><h3>Boundaries</h3><p>Where does domain end and coordination begin?</p></div>
                <div class="dw-card"><h3>Orchestration</h3><p>Who owns execution strategy?</p></div>
            </div>
            <p class="dw-takeaway mt-10">Native code distributes across the stack.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | conclusion" />
</x-darkwood.slide>
</x-darkwood.deck>
