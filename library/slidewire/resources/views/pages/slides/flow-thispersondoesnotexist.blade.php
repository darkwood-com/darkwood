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
Darkwood · PHP · Orchestration
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="sw-emoji">😶‍🌫️</span> This Person Does Not Exist
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
From Synchronous PHP<br>to Asynchronous Orchestration with <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <p class="dw-note">A minimal Symfony experiment</p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">HTTP request</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Image bytes</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">File saved</div>
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
Download 10 images. Respect a rate limit. Save each file.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-grid dw-grid-5" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-chip">Image 1</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">Image 2</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">Image 3</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">…</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-chip">Image 10</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <p class="dw-note mt-8">Same pipeline for every image. No dependency between them.</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="6" data-fragment="data-fragment">
    <p class="dw-question">Why are we waiting?</p>
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
The synchronous loop
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
One image at a time. The process blocks on every step.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6" style="display:grid;gap:14px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-pipeline-row is-active">
                        <span class="dw-pipeline-label">Image 1</span>
                        <span>Fetch</span><span class="dw-arrow">→</span>
                        <span>Save</span><span class="dw-arrow">→</span>
                        <span class="dw-pipeline-step">Sleep</span>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-pipeline-row is-muted">
                        <span class="dw-pipeline-label">Image 2</span>
                        <span class="dw-pipeline-step">waits…</span>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-pipeline-row is-muted">
                        <span class="dw-pipeline-label">Image 3</span>
                        <span class="dw-pipeline-step">waits…</span>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p class="dw-takeaway">Everything waits.</p>
</div>

@endverbatim
@php
$snippet = <<<'SW_CODE_2_0'
for (...) { fetch(); save(); sleep(); }
SW_CODE_2_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | sync-loop" />
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
What is actually happening?
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A hidden pipeline, repeated N times.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-flow dw-flow--vertical" style="max-width:520px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-node">Fetch</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-arrow">↓</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-node">Save</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-arrow">↓</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-node">Report</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <p class="dw-takeaway">Each image is an <strong>independent work unit</strong>.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | pipeline" />
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
The hidden opportunity
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Independent units. Same pipeline.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  I1[Image 1]
  I2[Image 2]
  I3[Image 3]
  P[Pipeline\nFetch → Save → Report]
  I1 --&gt; P
  I2 --&gt; P
  I3 --&gt; P</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-question" style="margin-top:24px">Why not run them together?</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | opportunity" />
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
Introducing <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Work units enter an orchestrator.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-flow dw-flow--vertical" style="max-width:640px;margin-left:auto;margin-right:auto">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-grid dw-grid-3" style="gap:10px">
                        <div class="dw-chip"><span class="dw-accent">Ip</span>(1)</div>
                        <div class="dw-chip"><span class="dw-accent">Ip</span>(2)</div>
                        <div class="dw-chip"><span class="dw-accent">Ip</span>(3)</div>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-arrow">↓</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-node">Flow</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-arrow">↓</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-node">Pipeline</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <p class="dw-note mt-6" style="text-align:center">An <span class="dw-accent">Ip</span> represents one work unit - a payload traveling through the pipeline.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | flow" />
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
The pipeline
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Describe steps once. Every <span class="dw-accent">Ip</span> follows the same path.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-flow dw-flow--vertical" style="max-width:480px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-node"><span class="dw-accent">Ip</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-arrow">↓</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-node">Fetch Image</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-arrow">↓</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-node">Save Image</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <div class="dw-arrow">↓</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="6" data-fragment="data-fragment">
    <div class="dw-node">Report</div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | pipeline-steps" />
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
The migration
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Suspend the work unit - not the whole process.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>Before · blocking</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_7_0'
sleep(1);
SW_CODE_7_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                        <p class="dw-muted" style="margin:0">Entire process frozen.</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>After · cooperative</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_7_1'
$driver->delay($delay);
SW_CODE_7_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                        <p class="dw-muted" style="margin:0">Only this fiber suspends.</p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway">Rate limiting per unit. Progress for everyone else.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | migration" />
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
Fibers · PHP 8.1
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Waiting becomes productive.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-fiber-lane">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-fiber-row">
                        <strong>Fiber A</strong>
                        <span>delay - suspended</span>
                        <em>waiting</em>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-fiber-row">
                        <strong>Fiber B</strong>
                        <span>downloading image</span>
                        <em>working</em>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-fiber-row">
                        <strong>Fiber C</strong>
                        <span>saving to disk</span>
                        <em>working</em>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-fiber-row">
                        <strong>Fiber A</strong>
                        <span>resumed - fetch starts</span>
                        <em>resume</em>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <p class="dw-note mt-6"><code>FiberDriver</code> delegates execution. <span class="dw-accent">Flow</span> orchestrates the pipeline.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | fibers" />
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
The new execution model
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Enqueue work. Await completion.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_9_0'
for (...) {
    $flow(new Ip(...));
}
$flow->await();
SW_CODE_9_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-6 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  E1[enqueue Ip]
  E2[enqueue Ip]
  E3[enqueue Ip]
  S[Flow scheduler]
  P[pipeline execution]
  A[await]
  E1 --&gt; S
  E2 --&gt; S
  E3 --&gt; S
  S --&gt; P
  P --&gt; A</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-note mt-4">The <code>for</code> loop produces work. <span class="dw-accent">Flow</span> schedules it.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | execution" />
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
Clarification
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What <span class="dw-accent">Flow</span> is - and is not.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-vs-list">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-vs-item is-no"><span>✕</span> Event Loop</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-vs-item is-no"><span>✕</span> Fiber implementation</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-vs-item is-no"><span>✕</span> Runtime</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-vs-item is-no"><span>✕</span> ReactPHP / Amp replacement</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-vs-item is-yes"><span>✓</span> Orchestration model</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <div class="mt-6 dw-grid dw-grid-4" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px">
                    <div class="dw-chip">FiberDriver</div>
                    <div class="dw-chip">AmpDriver</div>
                    <div class="dw-chip">ReactDriver</div>
                    <div class="dw-chip">SwooleDriver</div>
                </div>
                <p class="dw-note mt-4">Describe the pipeline once. Choose the driver for execution.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | what-flow-is" />
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
Beyond the POC
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Same orchestration model. Different domains.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-3">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>YouTube</h3>
                        <div class="dw-flow dw-flow--vertical" style="margin-top:14px;gap:8px">
                            <div class="dw-chip" style="border-radius:8px">Scrape</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px">Transcript</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px">Publish</div>
                        </div>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>Video</h3>
                        <div class="dw-flow dw-flow--vertical" style="margin-top:14px;gap:8px">
                            <div class="dw-chip" style="border-radius:8px">Scene gen</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px">Encode</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px">Persist</div>
                        </div>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card">
                        <h3>AI Agents</h3>
                        <div class="dw-flow dw-flow--vertical" style="margin-top:14px;gap:8px">
                            <div class="dw-chip" style="border-radius:8px">Read</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px">Transform</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px">Act</div>
                        </div>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p class="dw-takeaway" style="margin-top:20px">Independent units. Composable steps. One orchestrator.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | applications" />
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
Takeaway
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Not about performance.<br>About orchestration.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-split" style="margin-top:28px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>Before</h3>
                        <p style="line-height:1.4;margin:0">Execute task A<br>then B<br>then C</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>After</h3>
                        <ul class="dw-list-compact" style="margin:0">
                            <li>Describe work units</li>
                            <li>Describe a pipeline</li>
                            <li>Let the orchestrator schedule</li>
                        </ul>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="mt-8 dw-flow" style="margin-top:32px">
                    <div class="dw-node"><span class="dw-accent">Ip</span></div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-node"><span class="dw-accent">Flow</span></div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-node">Fetch</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-node">Save</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-node">await()</div>
                </div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | takeaway" />
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
                <li><code>flow-thispersondoesnotexist</code> - POC repo · commits <code>fae47e8</code> → <code>c61c929</code></li>
                <li><code>darkwood/flow</code> - orchestration package · <code>examples/flow.php</code></li>
                <li>Frédéric Bouchery - <em>The Evolution of Async PHP</em></li>
            </ul>
            <a class="dw-demo-url" href="https://f2r.github.io/fr/asynchrone.html" target="_blank" rel="noopener">f2r.github.io/fr/asynchrone.html</a>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Sync loop</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node"><span class="dw-accent">Flow</span> pipeline</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Darkwood scale</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>
</x-darkwood.deck>
