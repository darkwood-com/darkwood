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
Darkwood · PHP 8.5 · Laravel · <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Laravel Pipeline<br>&amp; Darkwood Flow
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Two execution models for<br><span class="dw-accent">composing PHP steps</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <p class="dw-note mt-6">Symfony Console demo · <code>demo:pipeline:compare</code></p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Composition</div>
                <div class="dw-arrow">≠</div>
                <div class="dw-node">Execution</div>
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
The overloaded word
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
"Pipeline" means composition.<br>Not execution.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-grid dw-grid-2" style="gap:12px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-chip">HTTP middleware</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">Unix pipes</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">CI/CD stages</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">Dataflow / ETL</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <p class="dw-question mt-8">Same word. Different execution contracts.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | pipeline" />
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
Core idea
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A pipeline defines<br><span class="dw-accent">how work is composed</span>.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <h3 class="dw-heading mt-6">The runtime defines<br><span class="dw-accent">how that work executes</span>.</h3>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-note mt-8">Ask: "what are the steps?" and "how does it execute?"</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | thesis" />
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
<span class="dw-accent">Laravel Pipeline</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The closure onion
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-grid dw-grid-3" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>array_reverse</h3><p>Pipes folded outer-first</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>array_reduce</h3><p>Nested closures</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>One passable</h3><p>One sync call stack</p></div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_3_0'
$pipeline = array_reduce(
    array_reverse($pipes),
    $carry,
    $destination
);
return $pipeline($passable);
SW_CODE_3_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | laravel-onion" />
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
Laravel execution
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<code>A(B(C(destination)))</code>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  subgraph onion ["through A, B, C"]
    A["Pipe A\noutermost"]
    B["Pipe B"]
    C["Pipe C"]
    D["destination"]
  end
  A --&gt; B --&gt; C --&gt; D</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4"><code>$next</code> = the rest of the chain, already folded.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | laravel-diagram" />
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
Why <code>$next</code> matters
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The middleware contract
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Before</h3>
                        <p style="margin:0">Run logic, then <code>$next($passable)</code></p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>After</h3>
                        <p style="margin:0"><code>$response = $next($passable)</code>, then transform</p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="mt-4 dw-split-panel is-warn" style="max-width:640px;margin:0 auto">
                    <h3>Short-circuit</h3>
                    <p style="margin:0">Return without calling <code>$next</code> → chain stops</p>
                </div>
</div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p class="dw-note mt-6">Perfect for one HTTP request · one passable · one call stack</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | next" />
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
The demo
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Same pipeline. Two runtimes.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-flow">
                <div class="dw-node">Fetch</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Hash</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Save</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="mt-6 dw-grid dw-grid-3" style="gap:8px">
                    <div class="dw-chip">0.8s</div>
                    <div class="dw-chip">0.3s</div>
                    <div class="dw-chip">0.6s</div>
                    <div class="dw-chip">0.2s</div>
                    <div class="dw-chip">0.5s</div>
                </div>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_6_0'
php bin/console demo:pipeline:compare
SW_CODE_6_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-note mt-4">In-memory fixture · controlled timers · not a benchmark</p>
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
Common misconception
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Fibers do <span class="dw-accent">not</span> make blocking code async.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>Still blocks the thread</h3>
                        <ul class="dw-list-compact" style="margin:0">
                            <li><code>sleep()</code> / <code>usleep()</code></li>
                            <li>Blocking HTTP clients</li>
                            <li><code>file_get_contents()</code></li>
                        </ul>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Useful overlap needs</h3>
                        <ul class="dw-list-compact" style="margin:0">
                            <li>Event-loop timers</li>
                            <li>Non-blocking I/O integration</li>
                            <li>Driver that schedules waits</li>
                        </ul>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Demo uses <code>AmpDriver</code> + <code>Amp\delay()</code> — not raw Fibers alone.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | fibers" />
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
Darkwood <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Nodes · packets · driver
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>Flow</h3><p>Transformation node</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>Job</h3><p><code>T1 → T2</code></p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>Ip</h3><p>Instruction packet</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>Driver</h3><p>Runtime scheduler</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-card"><h3>await()</h3><p>Completion barrier · returns void</p></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | flow-model" />
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
<span class="dw-accent">Flow</span> execution
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Five packets · one pipeline graph
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  subgraph input ["Submit"]
    I1[Ip task 1]
    I2[Ip task 2]
    I5[Ip task 5]
  end
  F[FetchJob]
  H[HashJob]
  S[SaveJob]
  C[Collector]
  I1 --&gt; F
  I2 --&gt; F
  I5 --&gt; F
  F --&gt; H --&gt; S --&gt; C</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_9_0'
foreach ($tasks as $task) { $flow(new Ip($task)); }
$flow->await();
SW_CODE_9_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | flow-diagram" />
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
AmpDriver
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Event-loop scheduling · one thread
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>sequenceDiagram
    participant T1 as Task1
    participant Loop as RevoltEventLoop
    participant T2 as Task2
    T1-&gt;&gt;Loop: Amp delay 0.8s
    T2-&gt;&gt;Loop: Amp delay 0.3s
    Note over T1,T2: timers registered
    Loop-&gt;&gt;T2: resume 0.3s
    Loop-&gt;&gt;T1: resume 0.8s</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="mt-4 dw-grid dw-grid-2" style="gap:10px">
                    <div class="dw-chip">✓ concurrent waits</div>
                    <div class="dw-chip">✗ parallel CPU</div>
                </div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | amp" />
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
Measurements
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Scheduling behavior — not a benchmark
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>Sync onion</h3>
                        <p style="margin:0">~2.42 s</p>
                        <p class="dw-muted" style="margin:8px 0 0">delays accumulate (Σ 2.4s)</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3><span class="dw-accent">Flow</span> + Amp</h3>
                        <p style="margin:0">~0.81 s</p>
                        <p class="dw-muted" style="margin:8px 0 0">delays overlap (max 0.8s)</p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="mt-4 dw-embed" >
                    <pre class="mermaid slidewire-diagram" data-slidewire-diagram>gantt
    title Fetch phase only
    dateFormat X
    axisFormat %Ls
    section Sync
    T1 :0, 800
    T2 :800, 1100
    T3 :1100, 1700
    T4 :1700, 1900
    T5 :1900, 2400
    section Flow
    T1f :0, 800
    T2f :0, 300
    T3f :0, 600
    T4f :0, 200
    T5f :0, 500</pre>
                </div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | timing" />
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
What the demo proves
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Evidence, not hype
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <ul class="dw-list mt-6">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <li>Independent timer waits can overlap on one thread</li>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <li>Multiple <code>Ip</code> packets traverse the same stages</li>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <li>Logs interleave — task #2 fetch starts before task #1 fetch ends</li>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <li>Identical hashes and output files (PHPUnit verified)</li>
</div>
            </ul>
            <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_12_0'
[FLOW][task #1][fetch] start
[FLOW][task #2][fetch] start
[FLOW][task #5][fetch] start
[FLOW][task #4][fetch] end
SW_CODE_12_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | proves" />
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
What it does NOT prove
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Stay precise
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-grid dw-grid-2" style="gap:12px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>Not universal speed</h3><p><span class="dw-accent">Flow</span> is not always faster</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>Not slow Laravel</h3><p>Pipeline is not poorly designed</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>Not parallel CPU</h3><p>Single OS thread throughout</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>Not async HTTP</h3><p>Blocking I/O would still block</p></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | limits" />
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
Comparison
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Different problems · different tools
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4" style="max-width:900px;margin:0 auto">
                <div class="dw-split" style="grid-template-columns:1fr 1fr;gap:16px">
                    <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card">
                            <h3>Laravel Pipeline</h3>
                            <ul class="dw-list-compact" style="margin:0">
                                <li>Simple onion (~30 LOC core)</li>
                                <li>Sync · one passable</li>
                                <li>Explicit <code>$next</code></li>
                                <li>Returns from <code>then()</code></li>
                                <li>Middleware / commands</li>
                            </ul>
                        </div>
</div>
                    <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card">
                            <h3>Darkwood Flow</h3>
                            <ul class="dw-list-compact" style="margin:0">
                                <li>Nodes · drivers · dispatchers</li>
                                <li>Async-capable · many <code>Ip</code>s</li>
                                <li><code>await()</code> returns void</li>
                                <li>Terminal collector required</li>
                                <li>Overlapping wait-heavy work</li>
                            </ul>
                        </div>
</div>
                </div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-note mt-4">Sync demo: 5 sequential onions · Flow demo: 5 concurrent packets</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | compare" />
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
What <span class="dw-accent">Flow</span> can learn
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Laravel got ergonomics right
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-flow dw-flow--vertical" style="max-width:680px;margin:0 auto;gap:6px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">await()</span><span>Returns void — easy to forget</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">results</span><span>Terminal collector, not <code>thenReturn()</code></span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">Ip</span><span>Manual wrapping per input</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-pipeline-row"><span class="dw-pipeline-label">bundle</span><span>Symfony services config is empty</span></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-pipeline-row is-active"><span class="dw-pipeline-label">ideas</span><span><code>sink()</code> · <code>runAll()</code> · TraceSubscriber · <code>hrtime()</code> delays</span></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="15 | api-lessons" />
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
Choosing the model
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Match runtime to workload
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Laravel Pipeline</h3>
                        <ul class="dw-list-compact" style="margin:0">
                            <li>One passable per call</li>
                            <li>Middleware / command intercept</li>
                            <li>Short-circuit via <code>$next</code></li>
                            <li>Sync, fast steps</li>
                        </ul>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Darkwood Flow</h3>
                        <ul class="dw-list-compact" style="margin:0">
                            <li>Many independent packets</li>
                            <li>Non-blocking driver waits</li>
                            <li>Per-node error handling</li>
                            <li>Runtime flexibility</li>
                        </ul>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">They coexist. Different layers, different models.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="16 | choose" />
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
Both are pipelines.<br>They are not the same machine.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-lead mt-6">Always ask:</p>
                <p class="dw-heading mt-2"><span class="dw-accent">How does it execute?</span></p>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="mt-8 dw-flow">
                    <div class="dw-node">Onion</div>
                    <div class="dw-arrow">or</div>
                    <div class="dw-node">Dataflow</div>
                    <div class="dw-arrow">—</div>
                    <div class="dw-node">composition ≠ execution</div>
                </div>
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
Resources
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Go deeper
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <ul class="dw-list mt-8">
                <li><code>darkwood/content/laravel-pipeline</code> — runnable demo</li>
                <li><code>demo:pipeline:sync</code> · <code>demo:pipeline:flow</code> · <code>demo:pipeline:compare</code></li>
                <li><code>darkwood/flow</code> — dataflow component</li>
                <li>Blog article — execution models deep dive</li>
            </ul>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-note mt-8">Re-run timings on your machine before citing exact numbers in slides.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="17 | resources" />
</x-darkwood.slide>
</x-darkwood.deck>
