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
Darkwood · <span class="dw-accent">PHP</span> 8.6 · <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
PHP Speed Tooling
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Partial Function Application,<br>Tokens and <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <p class="dw-question mt-8">How much tooling can PHP provide<br>before another abstraction earns its place?</p>
            <p class="dw-note mt-8">PHP 8.6 experiment · Darkwood</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The rule</p>
            <div class="mt-6" style="max-width:720px;margin:0 auto;display:flex;flex-direction:column;gap:12px">
                <div class="dw-pipeline-row" style="justify-content:space-between"><span>Need argument binding?</span><span><span class="dw-arrow">→</span> <span class="dw-accent">PFA</span></span></div>
                <div class="dw-pipeline-row" style="justify-content:space-between"><span>Need lightweight source structure?</span><span><span class="dw-arrow">→</span> <span class="dw-accent">PHP tokens</span></span></div>
                <div class="dw-pipeline-row" style="justify-content:space-between"><span>Need to iterate local files?</span><span><span class="dw-arrow">→</span> <span class="dw-accent">foreach</span></span></div>
                <div class="dw-pipeline-row" style="justify-content:space-between"><span>Need actual orchestration?</span><span><span class="dw-arrow">→</span> <span class="dw-accent">consider Flow</span></span></div>
            </div>
            <p class="dw-takeaway mt-8">Use the PHP primitive first.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | rule" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Experiment 1 · closure tax</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_2_0'
fn ($value) => transform($value, $configuration)
SW_CODE_2_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="dw-note mt-4">↓</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_2_1'
transform(?, $configuration)
SW_CODE_2_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="dw-note mt-6">PHP 8.6 Partial Function Application</p>
            <p class="dw-takeaway mt-6">The extra closure existed only to bind one argument.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | pfa" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">FCC vs PFA</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>First-class callable</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_3_0'
fn ($ctx) => $step->apply($ctx)

$step->apply(...)
SW_CODE_3_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Partial Function Application</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_3_1'
fn ($v) => applyBudget($v, $budget)

applyBudget(?, $budget)
SW_CODE_3_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <p class="dw-takeaway mt-6">PFA starts where FCC stops: some arguments are already known.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | fcc-pfa" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker"><code>?</code> vs <code>...</code></p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel">

@endverbatim
@php
$snippet = <<<'SW_CODE_4_0'
foo(?, $config)
SW_CODE_4_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    <p class="dw-note mt-4">exact placeholder<br>required in the resulting Closure</p>
                </div>
                <div class="dw-split-panel">

@endverbatim
@php
$snippet = <<<'SW_CODE_4_1'
foo('value', ...)
SW_CODE_4_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    <p class="dw-note mt-4">remaining list stays open<br>optionals keep their defaults</p>
                </div>
            </div>
            <p class="dw-takeaway mt-8">Bound arguments are evaluated when the partial is created.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | placeholders" />
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
  'class' => 'sw-heading dw-heading mt-6',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
<span class="dw-accent">PFA</span> goes straight into <span class="dw-accent">Flow</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">Flow</span> changed: <span class="dw-accent">0 lines</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


@endverbatim
@php
$snippet = <<<'SW_CODE_5_0'
$flow = new Flow(
    static fn (BenchmarkState $state): BenchmarkState =>
        loadPassages($state, $corpus),
);
SW_CODE_5_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="dw-note" style="margin:8px 0">↓</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_5_1'
$flow = new Flow(loadPassages(?, $corpus));
$flow
    ->fn(applyBudgetToState(?, 12))
    ->fn(collect(?, $box));
SW_CODE_5_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim


            <p class="dw-note mt-2">PFA already produces the <code>Closure</code> Flow expects.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | flow" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Pipe ≠ Partial Application</p>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:10px">
                <div class="dw-card"><h3>PFA</h3><p>bind arguments</p></div>
                <div class="dw-card"><h3><code>|&gt;</code></h3><p>thread a value</p></div>
                <div class="dw-card"><h3>Flow</h3><p>execute jobs</p></div>
            </div>

@endverbatim
@php
$snippet = <<<'SW_CODE_6_0'
$input
    |> removeNoise(...)
    |> normalizeWhitespace(...)
    |> applyBudget(?, 14);
SW_CODE_6_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="dw-takeaway mt-6">They are complementary, not competing syntax.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | pipe" />
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
First conclusion
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
We almost added nothing.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-grid dw-grid-2" style="gap:10px">
                <div class="dw-chip">No <code>partial()</code></div>
                <div class="dw-chip">No curry API</div>
                <div class="dw-chip">No pipe emulation</div>
                <div class="dw-chip">No PHP 8.6 requirement for Flow</div>
            </div>
            <p class="dw-takeaway mt-8">PHP already solved the syntax problem.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | nothing" />
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
PHP Speed Tooling
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What if the same rule applies<br>to tools written about PHP?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-flow dw-flow--vertical" style="max-width:420px;margin:0 auto;gap:6px">
                <div class="dw-node">Need a parser stack?</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Maybe just tokens</div>
            </div>
            <p class="dw-takeaway mt-8">Choose the cheapest representation<br>that still contains the information you need.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | tooling" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Our tool · <code>tools/pfa-opportunities</code></p>

@endverbatim
@php
$snippet = <<<'SW_CODE_9_0'
PHP files
        ↓
PhpToken::tokenize()
        ↓
ignore whitespace / comments
        ↓
find simple arrow functions
        ↓
PFA / FCC / ignore
SW_CODE_9_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-6 dw-grid dw-grid-3" style="gap:8px">
                <div class="dw-chip">No php-parser</div>
                <div class="dw-chip">~540 lines</div>
                <div class="dw-chip">report only</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | scanner" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Conservative by design</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>Accepted</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_10_0'
fn ($x) => foo($x, $bound)
→ foo(?, $bound)

fn ($x) => $step->apply($x)
→ $step->apply(...)
SW_CODE_10_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-warn">
                    <h3>Rejected</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_10_1'
fn ($p) => $p->toArray()

fn ($x) => foo(bar($x), $c)

fn ($x) => complicated($x)
    && other($x)
SW_CODE_10_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <p class="dw-takeaway mt-6">False negatives are acceptable.<br>False refactoring advice is not.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | conservative" />
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
  'class' => 'sw-heading dw-heading mt-8',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
What did it find?
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Zero is a result.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">

            <div class="mt-4 dw-grid dw-grid-3" style="gap:10px">
                <div class="dw-card"><h3>144</h3><p>PHP files</p></div>
                <div class="dw-card"><h3>498,107</h3><p>bytes</p></div>
                <div class="dw-card"><h3>51,663</h3><p>significant tokens</p></div>
            </div>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px">
                <div class="dw-card"><h3 class="dw-accent">0</h3><p>PFA candidates</p></div>
                <div class="dw-card"><h3>3</h3><p>FCC candidates</p></div>
                <div class="dw-card"><h3>0</h3><p>Flow src</p></div>
            </div>

            <p class="dw-note mt-2">The selected production code did not contain obvious PFA migration sites.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | findings" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Tokens are enough… until they aren’t</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>Tokens are enough for</h3>
                    <p style="margin:0">simple syntactic shapes<br>arrow functions<br>argument positions<br>method call patterns</p>
                </div>
                <div class="dw-split-panel is-warn">
                    <h3>AST / semantics may be needed for</h3>
                    <p style="margin:0">scope<br>types<br>name resolution<br>reliable automated rewriting</p>
                </div>
            </div>
            <p class="dw-takeaway mt-6">Speed Tooling means avoiding unnecessary machinery —<br>not refusing necessary machinery.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | limits" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Should the scanner use Flow?</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>Plain PHP</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_13_0'
foreach ($files as $file) {
    scanFile($file, $config);
}
SW_CODE_13_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel">
                    <h3>Flow</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_13_1'
$scan = scanFile(?, $config);
$flow = new Flow($scan);
$flow(new Ip($file));
$flow->await();
SW_CODE_13_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:8px">
                <div class="dw-chip">Plain ~79.5 ms</div>
                <div class="dw-chip">Flow ~120.2 ms</div>
                <div class="dw-chip">Peak ~6 MiB both</div>
            </div>
            <p class="dw-takeaway mt-6"><code>foreach</code> wins here. Flow added orchestration without an orchestration problem.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | foreach" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">When Flow earns its place</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>PHP primitives</h3>
                    <p style="margin:0">argument binding<br>callables<br>pipeline syntax<br>tokenization<br>simple iteration</p>
                </div>
                <div class="dw-split-panel">
                    <h3>Flow</h3>
                    <p style="margin:0">multiple IPs<br>driver-controlled execution<br>error jobs<br>events<br>IP strategies<br>await()</p>
                </div>
            </div>
            <p class="dw-takeaway mt-8">Flow becomes useful when execution itself becomes the problem.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | boundary" />
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
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :title-attributes="$titleAttributes">
<x-slot:title>@verbatim
PHP primitive first.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">

            <div class="mt-8" style="display:flex;flex-direction:column;gap:12px;max-width:720px;margin:0 auto">
                <div class="dw-card"><p style="margin:0"><span class="dw-accent">PFA</span> instead of an adapter abstraction.</p></div>
                <div class="dw-card"><p style="margin:0"><span class="dw-accent">PhpToken</span> instead of a parser stack.</p></div>
                <div class="dw-card"><p style="margin:0"><code>foreach</code> instead of an orchestration engine.</p></div>
            </div>
            <p class="dw-takeaway mt-8">Add Flow when execution becomes orchestration.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>
</x-darkwood.deck>
