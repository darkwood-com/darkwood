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
Darkwood · PHP · agents · <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The Thanks<br>Agent
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
When orchestration guarantees<br><span class="dw-accent">execution, not value</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-flow">
                <div class="dw-node">Execution</div>
                <div class="dw-arrow">≠</div>
                <div class="dw-node">Value</div>
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
  'class' => 'sw-heading dw-question',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The question
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What happens when an<br>orchestration system executes<br>work that has <span class="dw-accent">no value</span>?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-10">Not rhetorical. An engineering experiment.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | question" />
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
The experiment · content/thanks-agent
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Five useful. One useless.<br>Same runtime.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>FetchDocs</h3><p>Value +25</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>Github</h3><p>Value +40</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>PHP</h3><p>Value +30</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>Mozilla</h3><p>Value +20</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-card"><h3>Cost</h3><p>Value +15</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <div class="dw-card"><h3>Thanks</h3><p>Value <span class="dw-accent">+0</span></p></div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="6" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">+ SummaryAgent (+15) after fan-in</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | experiment" />
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
ThanksAgent
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Always says “Thanks.”
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_3_0'
return [
  'output'   => 'Thanks.', // or Looks good. / Good job.
  'tokens'   => 2,
  'messages' => 28,
  'didWork'  => false,     // value forced to 0
];
SW_CODE_3_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">No tools · no context · no work</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">Still scheduled · metered · logged</div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | thanks-agent" />
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
Thesis
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Activity is not throughput
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-vs-list">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-vs-item">execution ≠ value</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-vs-item">messages ≠ work</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-vs-item">visibility ≠ outcome</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p class="dw-takeaway mt-8">The scheduler cannot distinguish<br>productive work from performative work.</p>
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
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Cost is plural
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What an agent consumes
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>Scheduler</h3><p>WIP / MaxIpStrategy</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>Tokens</h3><p>Simulated in demo</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>Messages</h3><p>Chatty acknowledgements</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>Context</h3><p>Prompt / index slices</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-card"><h3>Logs</h3><p>Observability volume</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <div class="dw-card"><h3>Attention</h3><p>Decision queue</p></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | consumption" />
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
<span class="dw-accent">Flow</span> today · PHP ≥ 8.5
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Linear async pipeline.<br>Not a DAG engine.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  IP[Ip] --&gt; Job[Job]
  Job --&gt; Driver[Driver]
  Driver --&gt; Events[PUSH PULL POP ASYNC]
  Events --&gt; Job</pre>
            </div>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-chip">FlowFactory · MaxIpStrategy</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">FiberDriver · StreamSelectDriver</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4">Many IPs in flight ≠ graph joins.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | flow" />
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
Scatter / gather · demo pattern
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Multiple IPs. Shared bag. Sync join.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-3 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  Ips[Multiple Ips] --&gt; Stage[ExecuteAgentJob]
  Stage --&gt; Store[RunStore]
  Store --&gt; Summary[SummaryAgent]
  Summary --&gt; Report[Scoreboard]</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-2">MaxIpStrategy caps concurrency · fan-in is application state</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | scatter-gather" />
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
Scoreboard · FiberDriver · PHP 8.5.4
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Value Δ = <span class="dw-accent">0</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Without Thanks</h3>
                        <p style="margin:0;line-height:1.45">
                            Value <strong>145</strong><br>
                            Cost 0.012<br>
                            Messages 19<br>
                            Events 5/5/5/5
                        </p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>With Thanks</h3>
                        <p style="margin:0;line-height:1.45">
                            Value <strong>145</strong><br>
                            Cost 0.022 · <span class="dw-accent">+0.010</span><br>
                            Messages 48 · <span class="dw-accent">+29</span><br>
                            Events 6/6/6/6
                        </p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Useful output unchanged. Resources up.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | scoreboard" />
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
Economics
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Cost without value
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel">
                        <h3>Cost / request</h3>
                        <p style="margin:0">Looks tiny on the invoice line</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>Cost / useful result</h3>
                        <p style="margin:0">Thanks → tends to <span class="dw-accent">∞</span></p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-8">Cheap useless work is not cheap.<br>It is subsidized noise.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | cost" />
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
Bottleneck moves
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Supervision is scarce too
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>Model concurrency</h3><p>Calls you can fire</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>Runtime concurrency</h3><p>IPs in flight</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>Human supervision</h3><p>Open contexts</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>Validation capacity</h3><p>Reject / accept bandwidth</p></div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Stop counting running tasks.<br>Count decisions waiting. · <code>SupervisionBudget</code></p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | supervision" />
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
thanks-agent:bench-io · synthetic I/O · PHP 8.5.4
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Async is waiting efficiently
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>sequential</h3><p>~600 ms · value 130</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>stream_select</h3><p>~167 ms · value 130</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>Flow StreamSelect</h3><p>~179 ms · value 130</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>PHP 8.6 Poll</h3><p>skipped on 8.5.4</p></div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">~3.6× wall improvement. Same emptiness from Thanks.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | async" />
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
Editorial callback
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Polling is not<br><span class="dw-accent">value</span> either
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-lead mt-8">A better poller can execute<br>useless work faster.</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-8">Performance amplifies whatever<br>the system is optimizing.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | amplify" />
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
Terminal lifecycle
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The unhappy path must be billed
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-3 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  Exec[Execute] --&gt; Success[Success]
  Exec --&gt; Failure[Failure]
  Exec --&gt; Abandon[Abandon]
  Success --&gt; Complete[CompleteEvent]
  Failure --&gt; Error[ErrorEvent]
  Abandon --&gt; Hole[Accounting hole]
  Complete --&gt; Ledger[CostLedger]
  Error --&gt; Ledger</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-2">Happy-path-only FinOps lies.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | terminals" />
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
What changed in <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Shipped · next
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Promoted</h3>
                        <p style="margin:0;line-height:1.5">
                            CostLedger · ValueTag<br>
                            Complete / Error<br>
                            SupervisionBudget<br>
                            PollerInterface · Duration
                        </p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel">
                        <h3>Roadmap</h3>
                        <p style="margin:0;line-height:1.5">
                            Cancel · abandon terminal<br>
                            True DAG joins<br>
                            Decision-queue metrics<br>
                            CodeMap · AgentRouter
                        </p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Value and cost belong in the runtime vocabulary.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | flow-delta" />
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
Closing
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
We already know how<br>to make agents run.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-lead mt-8">The harder problem is deciding<br>which work should exist at all.</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-8">The next optimization is not another agent.<br>It is a better definition of <span class="dw-accent">value</span>.</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="mt-10 dw-flow">
                    <div class="dw-node">Activity</div>
                    <div class="dw-arrow">→</div>
                    <div class="dw-node"><span class="dw-accent">value</span></div>
                </div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>
</x-darkwood.deck>
