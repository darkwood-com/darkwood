<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="section" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--section',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-section-title dw-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="section" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Darkwood · PHP · PHPUnit · agents · <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
From No-Life<br>Testing to<br>Behavioral Testing
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
AI made writing tests cheap.<br><span class="dw-accent">Choosing what deserves an oracle is now the hard part.</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-flow">
                <div class="dw-node">Jobs</div>
                <div class="dw-arrow">≠</div>
                <div class="dw-node">Drivers</div>
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Opening
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Most unit tests are<br>protecting the<br><span class="dw-accent">wrong thing.</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-10">Not because PHPUnit is weak.<br>Because the oracle chose implementation over meaning.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | provocation" />
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
Fabien Potencier · Sebastian Bergmann
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Your AI writes tests.<br>Are they worth anything?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="mt-10 dw-grid dw-grid-2" style="gap:12px">
                    <div class="dw-card"><h3>Liability</h3><p>Weak tests under agent velocity</p></div>
                    <div class="dw-card"><h3>Oracle</h3><p>What decides “correct”</p></div>
                </div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | the question" />
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
Scope
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Not a tutorial. Not a bake-off.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-vs-list">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-vs-item is-no">✗ PHPUnit basics</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-vs-item is-no">✗ TDD sermon</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-vs-item is-no">✗ ReactPHP vs Amp</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-vs-item is-yes">✓ Oracles that survive refactoring</div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6"><span class="dw-accent">Jobs</span> = behavior · <span class="dw-accent">Drivers</span> = furniture</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | scope" />
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
The economics flipped
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Writing got cheap. Thinking got scarce.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-8 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel">
                        <h3>Before AI</h3>
                        <p style="margin:0;font-weight:700">Cost of writing<br>≫ cost of choosing oracles</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>With agents</h3>
                        <p style="margin:0;font-weight:700">Cost of writing<br>≪ cost of choosing oracles</p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Volume without oracles is false insurance.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | economics" />
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
Scarce skill
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Not more tests.<br><span class="dw-accent">Better oracles.</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-grid dw-grid-2" style="gap:12px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-chip">Which behaviors deserve insurance?</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">Which assertions survive a refactor?</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">Which doubles hide a design smell?</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">Would green still pass if meaning broke?</div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | judgment" />
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
Vocabulary
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
No-life testing
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
A green suite that pins implementation,<br>call order, and runtime plumbing —
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-question mt-6">while leaving business meaning unasserted.</p>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Alive in CI. Dead as insurance.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | no-life" />
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
Bad · 01 · ImplementationCoupledTest
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Mocks all the way down
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_7_0'
$parse->expects($this->once())
    ->method('__invoke')
    ->with($raw)
    ->willReturn($parsed);
// …
$this->assertTrue(true); // Green. Never asserted meaning.
SW_CODE_7_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4">Broken real <code>ParseJob</code> → suite stays green.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | bad-mocks" />
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
Bad · 02 · RuntimeCoupledTest
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Testing the furniture
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
public function await(array &$stream): void
{
    ++$this->counter->awaitCalls;
    $this->inner->await($stream);
}

$this->assertSame(1, $counter->awaitCalls);
SW_CODE_8_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4">Correct excerpt + driver swap → still red. Wrong claim.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | bad-runtime" />
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
Bad · 03 · CoverageTheaterTest
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Shape without meaning
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_9_0'
$this->assertInstanceOf(ExcerptResult::class, $result);
$this->assertNotNull($result->title);
$this->assertIsString($result->excerpt);
// Never asked: is the excerpt faithful?
SW_CODE_9_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4">Coverage theatre. Agents love it.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | bad-theatre" />
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
Same workflow · three lies
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
False insurance, catalogued
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>Call order</h3><p>Mocks erase Jobs</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>await()</h3><p>Pins the Driver</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>Shape</h3><p>Green on nonsense</p></div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <p class="dw-question mt-8">What should we assert instead?</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | three-lies" />
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
<span class="dw-accent">Flow</span> · Jobs vs Drivers
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Behavior lives in Jobs
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-3 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TD
    Req[DocumentRequest] --&gt; Fetch[FetchJob]
    Fetch --&gt; Parse[ParseJob]
    Parse --&gt; Excerpt[ExcerptJob]
    Excerpt --&gt; Validate[ValidateJob]
    Validate --&gt; Result[ExcerptResult]

    subgraph behavior ["Business behavior — test this"]
        Fetch
        Parse
        Excerpt
        Validate
    end

    subgraph furniture ["Runtime furniture — do not pin"]
        D1[FiberDriver]
        D2[AmpDriver]
        D3[ReactDriver]
    end

    behavior -.-&gt;|scheduled by| furniture</pre>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | jobs-vs-drivers" />
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
Workflow · not furniture
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Input · Steps · Output
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-flow" style="flex-wrap:wrap">
                <div class="dw-node" style="min-height:72px">Request</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:72px">Fetch</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:72px">Parse</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:72px">Excerpt</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:72px">Validate</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:72px">Result</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_12_0'
yield new FetchJob($source);
yield new ParseJob();
yield new ExcerptJob($maxExcerptLength);
yield new ValidateJob($minExcerptLength);
SW_CODE_12_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-note mt-4">Same chain. Inject <code>FiberDriver</code> or <code>AmpDriver</code>. Jobs do not care.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | workflow" />
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
Good · JobBehaviorTest
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Observable transforms
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_13_0'
$parsed = (new ParseJob())(new RawDocument('doc://1', $html));

$this->assertSame('Hello Flow', $parsed->title);
$this->assertSame(
    'Jobs transform packets. Drivers schedule them.',
    $parsed->body,
);
SW_CODE_13_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4">No <span class="dw-accent">Flow</span>. No Driver. Still business.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | job-oracle" />
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
Good · WorkflowBehaviorTest
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Faithfulness, not wiring
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_14_0'
$result = FlowCollector::run($flow, new Ip(
    new DocumentRequest('fixture://article'),
));

$this->assertSame('Darkwood Flow Notes', $result->title);
$this->assertStringContainsString(
    'Jobs transform information packets',
    $result->excerpt,
);
SW_CODE_14_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4">Oracle = excerpt meaning. Not call order.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | workflow-oracle" />
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
Good · MultiDriverBehaviorTest
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Same oracle. Two runtimes.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-3 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
    Oracle[Same behavioral oracle] --&gt; Fiber[FiberDriver]
    Oracle --&gt; Amp[AmpDriver]
    Fiber --&gt; Green1[Green]
    Amp --&gt; Green2[Green]
    Bad[await / driver spies] --&gt; Fiber
    Bad --&gt; Red[Red after swap]</pre>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_15_0'
yield 'fiber' => [new FiberDriver()];
yield 'amp'   => [new AmpDriver()];
// identical assertions on title + excerpt
SW_CODE_15_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-3">Survive the driver swap → you tested the product.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="15 | multi-driver" />
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
Beyond <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The runtime will change.<br>Your architecture should not.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>Sequential PHP</h3><p>Call stack ≠ outcome</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>ReactPHP</h3><p>Promises / loop furniture</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>Amp</h3><p>Amp types ≠ domain packets</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-card"><h3>Framework X</h3><p>HTTP adapter ≠ product</p></div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <p class="dw-note mt-6">If the suite breaks while product meaning is unchanged — you tested furniture.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="16 | runtime-independence" />
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
Doubles under agents
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Stub the port.<br>Don’t mock the Jobs.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok">
                        <h3>Do</h3>
                        <p style="margin:0"><code>InMemoryDocumentSource</code><br>Unit-test <code>App\Job\*</code><br><code>composer test:good</code></p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn">
                        <h3>Don’t</h3>
                        <p style="margin:0">Mock <code>DriverInterface</code><br>Assert job call order<br>Treat Bad as insurance</p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_17_0'
$source = new InMemoryDocumentSource([
    'fixture://article' => $html,
]);
SW_CODE_17_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="17 | stubs" />
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
Guillaume Moigneu · Agent Skills
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Pressure-test the decision.<br>Don’t generate the suite.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_18_0'
Use $pressure-test-testing-decisions on
tests/Bad/ImplementationCoupledTest.php —
should we keep, rewrite, or delete it?
SW_CODE_18_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">Oracle</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">Furniture</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">Coverage theatre</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-chip">Driver swap</div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="18 | pressure-test" />
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
Useful grill · one at a time
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Defend the insurance claim
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <ul class="dw-list mt-6">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <li>Should this be tested at all?</li>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <li>Would this fail for the <em>right</em> reason?</li>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <li>Does this assertion survive a refactoring?</li>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <li>Are these mocks hiding a design problem?</li>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <li>Fiber → Amp: would this fail — and <em>should</em> it?</li>
</div>
                <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <li>Suite green, product wrong — which test lied?</li>
</div>
            </ul>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="19 | grill" />
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
One habit change
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Rewrite the brief
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-split">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-split-panel is-warn" style="min-height:200px">
                        <h3>Weak</h3>
                        <p style="margin:0">“Add unit tests.<br>Aim for high coverage.”</p>
                    </div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-split-panel is-ok" style="min-height:200px">
                        <h3>Strong</h3>
                        <p style="margin:0">“Pressure-test first.<br>Oracle on Job / workflow meaning.<br>Stub the port. No driver spies.”</p>
                    </div>
</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="mt-4 dw-embed" >
                    <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TD
    Ask[Agent proposes a test] --&gt; PT{Pressure-test}
    PT --&gt;|furniture / theatre| Del[Delete or park under Bad]
    PT --&gt;|Job meaning| Job[JobBehaviorTest]
    PT --&gt;|composition| Wf[WorkflowBehaviorTest]
    PT --&gt;|runtime claim| Swap[MultiDriverBehaviorTest]</pre>
                </div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="20 | brief" />
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
Companion · <code>content/nolife-tests</code>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Clone it. Break it. Decide.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_21_0'
composer install
composer test:good   # behavioral insurance
composer test:bad    # educational false insurance
php bin/excerpt.php fiber
php bin/excerpt.php amp
SW_CODE_21_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
            <div class="mt-4 dw-grid dw-grid-4" style="gap:8px">
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">workflows</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">tests Good/Bad</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">skills/</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-chip">fixtures</div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="21 | repository" />
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
Homework
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Break <code>ParseJob</code> on purpose.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-flow">
                <div class="dw-node">Bad</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">stays green</div>
            </div>
            <div class="mt-4 dw-flow">
                <div class="dw-node">Good</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">goes red</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-8">Worth more than another coverage report.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="22 | experiment" />
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
Honesty
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What this does not prove
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-chip">No sync <code>run(Ip)</code> yet</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-chip">Stubs at ports still useful</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-chip">Driver CI ≠ app suite</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="3" data-fragment="data-fragment">
    <div class="dw-chip">Not an Amp vs Fiber bench</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="4" data-fragment="data-fragment">
    <div class="dw-chip">AI can write good tests</div>
</div>
                <div class="slidewire-fragment" data-fragment-index="5" data-fragment="data-fragment">
    <div class="dw-chip">Risk = volume without oracles</div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="23 | limits" />
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
The value of a test<br>is not that it passes.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <h3 class="dw-heading mt-6"><span class="dw-accent">It is that it fails<br>for the right reason.</span></h3>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="mt-10 dw-flow">
                    <div class="dw-node">Jobs</div>
                    <div class="dw-arrow">→</div>
                    <div class="dw-node">Oracles</div>
                    <div class="dw-arrow">→</div>
                    <div class="dw-node">Insurance</div>
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
<section class="dw-wrap dw-wrap--top">


            <ul class="dw-list mt-6">
                <li>Article — <em>From No-Life Testing to Behavioral Testing</em></li>
                <li><code>content/nolife-tests</code> — <code>composer test:good</code></li>
                <li>Darkwood Flow — darkwood-com.github.io/flow</li>
                <li>Potencier — Weak tests are now a liability</li>
                <li>Bergmann — Seeing the Truth: Test Oracles</li>
                <li>Moigneu — <code>pressure-test-decisions</code> / testing specialization</li>
            </ul>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Your AI writes tests. Make sure they are worth claiming.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="24 | resources" />
</x-darkwood.slide>
</x-darkwood.deck>
