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
Darkwood · PHP · <span class="dw-accent">Flow</span> · <span class="dw-accent">RxJS</span> · stream_select
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The Fiber<br>Illusion
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
What <span class="dw-accent">RxJS</span> taught us about<br><span class="dw-accent">real async pipelines</span> in PHP
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-flow">
                <div class="dw-node">Fibers</div>
                <div class="dw-arrow">≠</div>
                <div class="dw-node">Overlapping I/O</div>
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
The initial assumption
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">Flow</span> already looked asynchronous.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-grid dw-grid-2" style="gap:12px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-chip">Fibers</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">Drivers</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">MaxIpStrategy</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">await()</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <p class="dw-question mt-8">But what exactly was “async”?</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4">Multiple Fibers do not automatically mean overlapping I/O.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | assumption" />
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
Why study <span class="dw-accent">RxJS</span>?
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Not for <code>pipe()</code>. Not to clone Observables.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Interesting because of the execution lifecycle.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  A[subscribe] --&gt; B[values]
  B --&gt; C[track work]
  C --&gt; D[completion]
  D --&gt; E[errors / cancel]
  E --&gt; F[teardown]</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-2">Architectural mirror — not a port.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | why-rxjs" />
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
What <span class="dw-accent">RxJS</span> really teaches
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Lifecycle under the operators
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
from(urls).pipe(
  mergeMap(fetchImage, 4),
  map(hashBinary),
  retry(2),
  finalize(cleanup),
);
SW_CODE_3_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3><code>pipe()</code></h3><p>Composes only</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3><code>subscribe()</code></h3><p>Starts execution</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3><code>mergeMap(..., 4)</code></h3><p>Bounds active work</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-card"><h3><code>finalize()</code></h3><p>Cleanup on terminal paths</p></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | rxjs-pipeline" />
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
<span class="dw-accent">Flow</span> vocabulary
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Packets through stages
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
                <div class="dw-arrow">→</div>
                <div class="dw-node">Collector</div>
            </div>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>Ip</h3><p>Instruction packet</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>Job</h3><p>Stage work</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>IpStrategy</h3><p>Admission control</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>MaxIpStrategy</h3><p>Bounded concurrency</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-card"><h3>Driver</h3><p>Schedule &amp; wait</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <div class="dw-card"><h3>await()</h3><p>Completion barrier</p></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | flow-model" />
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
The Fiber illusion
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A Fiber can suspend PHP.<br>It cannot unblock a socket.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_5_0'
file_get_contents($url); // still blocks the process
SW_CODE_5_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="mt-6 dw-split">
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Concurrent PHP</h3>
                        <p style="margin:0">Many Fibers scheduled</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Overlapping I/O</h3>
                        <p style="margin:0">Non-blocking + <code>stream_select</code></p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="mt-4 dw-split-panel is-warn" style="max-width:720px;margin:0 auto">
                    <h3>Fake async</h3>
                    <p style="margin:0">Blocking calls hidden inside Fibers</p>
                </div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | fiber-illusion" />
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
The missing stack
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Async is a runtime contract
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  S[PHP streams]
  NB[non-blocking mode]
  SEL[stream_select]
  FIB[Fiber suspension]
  RES[readiness resume]
  DRV[Driver]
  FL[Flow]
  S --&gt; NB --&gt; SEL --&gt; FIB --&gt; RES --&gt; DRV --&gt; FL</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-2">Not a naming convention. A yield-on-readiness story.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | stack" />
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
The concrete experiment
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Real sockets. Real concurrency.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-flow">
                <div class="dw-node">URLs</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">fetch</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">checksum</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">save</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">summary</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_7_0'
php bin/console app:fetch-images --concurrency=4 --timeout=5
SW_CODE_7_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">Local fixture server</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">One deliberate 404</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">Per-item success / failure</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-chip"><code>content/rxjs-flow</code></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | experiment" />
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
What we shipped
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">StreamSelectDriver</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_8_0'
$driver->waitReadable($stream, $timeout);
$driver->waitWritable($stream, $timeout);
SW_CODE_8_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>Driver</h3><p>Schedule · wait · resume · deadlines</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>Job</h3><p>HTTP / protocol / application</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>IpStrategy</h3><p>Admission &amp; concurrency</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-card"><h3>Collector</h3><p>Aggregate results</p></div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <p class="dw-question mt-6">The Driver is not an HTTP client.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | stream-select-driver" />
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
What concurrency=4 means
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A concurrency limit is only meaningful<br>if the runtime can say <span class="dw-accent">exactly what it limits</span>.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-3" style="gap:8px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-chip">Queued packets</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">Admitted jobs</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">Active Fibers</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">Open sockets</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-chip">In-flight requests</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_9_0'
MaxIpStrategy(4)
+ non-blocking fetch job
+ StreamSelectDriver
→ ≤ 4 fetch packets admitted
→ ≤ 4 fetch Fibers active
→ ≤ 4 sockets waiting on stream_select
SW_CODE_9_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | concurrency" />
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
Errors · completion · cleanup
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Teardown is part of the run
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Batch</h3>
                        <p style="margin:0">Collect per-item errors<br><span class="dw-muted">(demo: soft DTO errors)</span></p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>Critical</h3>
                        <p style="margin:0">Fail fast via <code>errorJob</code></p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <ul class="dw-list mt-6">
                    <li><code>await()</code> → no queued packets, no active jobs, no pending waits</li>
                    <li>Cleanup on success, error, timeout, cancellation</li>
                </ul>
</div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4"><span class="dw-accent">Flow</span>’s version of teardown / <code>finalize</code> discipline.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | teardown" />
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
What <span class="dw-accent">Flow</span> keeps from <span class="dw-accent">RxJS</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Steal the guarantees
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>Lifecycle discipline</h3><p>Start → track → complete → tear down</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>Lazy compose / explicit run</h3><p><code>FlowFactory</code> then <code>await()</code></p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>Bounded concurrency</h3><p><code>MaxIpStrategy</code> + yielding jobs</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>Deterministic completion</h3><p>Wait for active work</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-card"><h3>Cleanup</h3><p>Every terminal path</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <div class="dw-card"><h3>Per-run isolation</h3><p>+ observability</p></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | keep" />
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
What <span class="dw-accent">Flow</span> rejects
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Steal the guarantees,<br>not the API
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn" style="margin:0"><h3>Observable / Observer / Subscriber</h3><p style="margin:0">As public app types</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn" style="margin:0"><h3>Subjects · operator zoo</h3><p style="margin:0">JS method names in PHP</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn" style="margin:0"><h3>Custom Promise type</h3><p style="margin:0">Not required for pipelines</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn" style="margin:0"><h3>HTTP inside Drivers</h3><p style="margin:0">Or a second demo engine</p></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | reject" />
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
Where <span class="dw-accent">Flow</span> goes next
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Prove the waits further
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>01 · Multi-connection benchmark</h3><p>Next acceptance test — not measured yet</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>02 · Cancellation tokens</h3><p>For StreamSelectDriver waits</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>03 · Richer result API</h3><p>If collectors become painful</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>04 · Production examples</h3><p>Beyond the teaching fetch Job</p></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | next" />
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
When a job waits on I/O,<br>does the runtime actually wait with it—
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <h3 class="dw-heading mt-4"><span class="dw-accent">or does it only pretend?</span></h3>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="mt-10 dw-flow">
                    <div class="dw-node">Jobs yield</div>
                    <div class="dw-arrow">→</div>
                    <div class="dw-node">Drivers wait</div>
                    <div class="dw-arrow">→</div>
                    <div class="dw-node">I/O overlaps</div>
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
                <li>Article — <em>The Fiber Illusion</em></li>
                <li><code>content/rxjs-flow</code> — <code>app:fetch-images</code></li>
                <li><code>darkwood/flow</code> — <code>StreamSelectDriver</code></li>
                <li>RxJS 8.0.0-alpha.14 — lifecycle study source</li>
            </ul>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-note mt-8">Steal the guarantees. Keep jobs, packets, Drivers.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | resources" />
</x-darkwood.slide>
</x-darkwood.deck>
