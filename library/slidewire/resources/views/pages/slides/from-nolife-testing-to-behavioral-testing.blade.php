{{-- From No-Life Testing to Behavioral Testing — Darkwood Flow × PHPUnit × AI agents --}}
{{-- Source: tasks/#cebfae_nolife-tests/article.md · Companion: content/nolife-tests --}}
{{-- Route: /slides/from-nolife-testing-to-behavioral-testing --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $flow = '<span class="dw-accent">Flow</span>';
        $oracle = '<span class="dw-accent">oracle</span>';
        $jobs = '<span class="dw-accent">Jobs</span>';
        $drivers = '<span class="dw-accent">Drivers</span>';
    @endphp

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 1 · TITLE --}}
    {{-- @notes
        OPENING (~60s)
        Story: Welcome. Darkwood talk on testing in the age of coding agents — not a PHPUnit tutorial.
        Hook: “Your AI writes tests. Are they worth anything?” — Fabien Potencier’s framing; Sebastian Bergmann’s vocabulary (test oracles).
        Set expectation: we will move from an old mental model (tests = coverage / call order) toward a new one (tests = insurance on business meaning).
        Mention companion repo content/nolife-tests — the laboratory, not an appendix.
        Transition: open with a provocation, not a definition.
        Timing: title + pause. Let people read the subtitle.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · PHP · PHPUnit · agents · {!! $flow !!}</p>
            <h1 class="dw-title">From No-Life<br>Testing to<br>Behavioral Testing</h1>
            <p class="dw-lead">AI made writing tests cheap.<br><span class="dw-accent">Choosing what deserves an oracle is now the hard part.</span></p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Jobs</div>
                <div class="dw-arrow">≠</div>
                <div class="dw-node">Drivers</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · PROVOCATION --}}
    {{-- @notes
        PROVOCATION (~45s)
        Transition from title: “Let’s start where the article starts — with discomfort.”
        Deliver the line slowly. Pause after “wrong thing.”
        Audience interaction (optional): “Raise a hand if you’ve merged a PR because the suite was green and you still felt uneasy.”
        Do not explain yet. Curiosity first.
        Joke (optional, calm): “Green bars are not a personality.”
        Next: name the AI angle without hype.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Opening</p>
            <h2 class="dw-heading">Most unit tests are<br>protecting the<br><span class="dw-accent">wrong thing.</span></h2>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-10">Not because PHPUnit is weak.<br>Because the oracle chose implementation over meaning.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>01 / provocation</span></footer>
    </x-slidewire::slide>

    {{-- 3 · THE QUESTION --}}
    {{-- @notes
        THE QUESTION (~60s)
        Credit Fabien Potencier: “Weak tests are now a liability.” Agents change code faster than reviewers can follow. The suite is the insurance claim.
        Credit Bergmann: behind every green bar sits a test oracle — the procedure that decides correctness. Passing ≠ product right; it means the oracle was satisfied.
        Key line for the room: “If the suite only proves mocks were called in order, the insurance is theatre.”
        Transition: this talk is opinionated, not a framework bake-off.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Fabien Potencier · Sebastian Bergmann</p>
            <h2 class="dw-question" style="margin-top:0;">Your AI writes tests.<br>Are they worth anything?</h2>
            <x-slidewire::fragment :index="0">
                <div class="mt-10 dw-grid dw-grid-2" style="gap:12px;">
                    <div class="dw-card"><h3>Liability</h3><p>Weak tests under agent velocity</p></div>
                    <div class="dw-card"><h3>Oracle</h3><p>What decides “correct”</p></div>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>02 / the question</span></footer>
    </x-slidewire::slide>

    {{-- 4 · WHAT THIS IS NOT --}}
    {{-- @notes
        BOUNDARY (~40s)
        Clear the air: not PHPUnit 101, not TDD sermon, not ReactPHP vs Amp.
        Thesis in one sentence: Jobs are business behavior; Drivers are runtime furniture; app suites pin the former.
        Success criterion from the article: if you leave proud of every green test, I failed; if you delete or rewrite a few, it worked.
        Transition: why does this matter *now*? Economics.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Scope</p>
            <h2 class="dw-heading-slide">Not a tutorial. Not a bake-off.</h2>
            <div class="mt-6 dw-vs-list">
                <x-slidewire::fragment :index="0"><div class="dw-vs-item is-no">✗ PHPUnit basics</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-vs-item is-no">✗ TDD sermon</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-vs-item is-no">✗ ReactPHP vs Amp</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-vs-item is-yes">✓ Oracles that survive refactoring</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="4">
                <p class="dw-takeaway mt-6">{!! $jobs !!} = behavior · {!! $drivers !!} = furniture</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>03 / scope</span></footer>
    </x-slidewire::slide>

    {{-- 5 · ECONOMICS FLIPPED --}}
    {{-- @notes
        ECONOMICS (~75s)
        Before agents: good tests expensive → teams skipped → lived with risk.
        Today: agents flood PRs with coverage. Potencier: old complaint (“empty assertions, mocks all the way down”) is mostly outdated — models can write real suites.
        So tests are solved? No. What became scarce is *judgment*.
        Walk the inequality on the slide. Pause on the flip.
        Interaction: “Who still measures success as coverage %?”
        Transition: define the scarce skill.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The economics flipped</p>
            <h2 class="dw-heading-slide">Writing got cheap. Thinking got scarce.</h2>
            <div class="mt-8 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel">
                        <h3>Before AI</h3>
                        <p style="margin:0;font-size:clamp(22px,2.2vw,30px);font-weight:700;">Cost of writing<br>≫ cost of choosing oracles</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok">
                        <h3>With agents</h3>
                        <p style="margin:0;font-size:clamp(22px,2.2vw,30px);font-weight:700;">Cost of writing<br>≪ cost of choosing oracles</p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-6">Volume without oracles is false insurance.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>04 / economics</span></footer>
    </x-slidewire::slide>

    {{-- 6 · WHAT BECAME SCARCE --}}
    {{-- @notes
        JUDGMENT (~50s)
        List the four judgment questions from the article mentally: which behaviors deserve insurance, which assertions survive refactor, which doubles hide design smell, which greens would pass if product meaning broke.
        That distinction *is* the whole talk.
        Transition: show what the wrong kind of green looks like — “no-life testing.”
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Scarce skill</p>
            <h2 class="dw-heading-slide">Not more tests.<br><span class="dw-accent">Better oracles.</span></h2>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:12px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">Which behaviors deserve insurance?</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">Which assertions survive a refactor?</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">Which doubles hide a design smell?</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">Would green still pass if meaning broke?</div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>05 / judgment</span></footer>
    </x-slidewire::slide>

    {{-- 7 · NO-LIFE DEFINED --}}
    {{-- @notes
        DEFINITION (~45s)
        Coin the phrase: no-life testing — suite is alive in CI; insurance is dead.
        Pins: implementation, call order, runtime plumbing. Leaves business meaning unasserted.
        Point to companion: composer test:bad (green, low value) vs composer test:good (behavioral).
        Three foils coming — same workflow, three ways to lie.
        Optional joke: “They’re not flaky. They’re loyal to the wrong master.”
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Vocabulary</p>
            <h2 class="dw-heading-slide">No-life testing</h2>
            <p class="dw-lead">A green suite that pins implementation,<br>call order, and runtime plumbing —</p>
            <x-slidewire::fragment :index="0">
                <p class="dw-question mt-6">while leaving business meaning unasserted.</p>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-6">Alive in CI. Dead as insurance.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>06 / no-life</span></footer>
    </x-slidewire::slide>

    {{-- 8 · BAD · IMPLEMENTATION --}}
    {{-- @notes
        BAD #1 (~90s)
        File: tests/Bad/ImplementationCoupledTest.php
        Walk the code: four mocked JobInterface, expects once, canned returns, assertTrue(true).
        Pressure question: “If a broken real ParseJob shipped, would this stay green?” Yes — mocks never call the real job.
        Insurance claim = call order under doubles, not excerpt faithfulness.
        Audience: senior PHP folks have written this; agents now produce it at machine speed (Potencier’s “mocks all the way down”).
        Transition: furniture next.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Bad · 01 · ImplementationCoupledTest</p>
            <h2 class="dw-heading-slide">Mocks all the way down</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-4 dw-code" style="font-size:15px;">$parse->expects($this->once())
    ->method('__invoke')
    ->with($raw)
    ->willReturn($parsed);
// …
$this->assertTrue(true); // Green. Never asserted meaning.</pre>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-4">Broken real <code>ParseJob</code> → suite stays green.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>07 / bad-mocks</span></footer>
    </x-slidewire::slide>

    {{-- 9 · BAD · RUNTIME --}}
    {{-- @notes
        BAD #2 (~75s)
        File: tests/Bad/RuntimeCoupledTest.php — wraps FiberDriver, counts await() calls.
        Proves the event-loop wrapper was exercised. Does not prove excerpt faithfulness.
        Would Fiber → Amp break this even when excerpt is correct? Yes. That is testing furniture.
        Line for the room: “ReactPHP does not need your application tests. Amp does not either. Flow’s package CI may smoke drivers. Your product suite should not.”
        Transition: theatre.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Bad · 02 · RuntimeCoupledTest</p>
            <h2 class="dw-heading-slide">Testing the furniture</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-4 dw-code" style="font-size:16px;">public function await(array &$stream): void
{
    ++$this->counter->awaitCalls;
    $this->inner->await($stream);
}

$this->assertSame(1, $counter->awaitCalls);</pre>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-4">Correct excerpt + driver swap → still red. Wrong claim.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>08 / bad-runtime</span></footer>
    </x-slidewire::slide>

    {{-- 10 · BAD · COVERAGE THEATRE --}}
    {{-- @notes
        BAD #3 (~60s)
        File: tests/Bad/CoverageTheaterTest.php — instanceof, notNull, isString.
        Agent loves this: high coverage, zero oracle. Change excerpt to “lorem ipsum” → still green.
        Bergmann: the oracle chose “shape.” Shape is not product truth.
        Summarize three lies before flipping to good.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Bad · 03 · CoverageTheaterTest</p>
            <h2 class="dw-heading-slide">Shape without meaning</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-4 dw-code" style="font-size:16px;">$this->assertInstanceOf(ExcerptResult::class, $result);
$this->assertNotNull($result->title);
$this->assertIsString($result->excerpt);
// Never asked: is the excerpt faithful?</pre>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-4">Coverage theatre. Agents love it.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>09 / bad-theatre</span></footer>
    </x-slidewire::slide>

    {{-- 11 · THREE LIES --}}
    {{-- @notes
        BRIDGE (~40s)
        Same workflow. Three different ways to lie. Ask the room which they have in production.
        Then flip: architecture already draws the line — Flow Jobs vs Drivers.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Same workflow · three lies</p>
            <h2 class="dw-heading-slide">False insurance, catalogued</h2>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>Call order</h3><p>Mocks erase Jobs</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>await()</h3><p>Pins the Driver</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>Shape</h3><p>Green on nonsense</p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <p class="dw-question mt-8">What should we assert instead?</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>10 / three-lies</span></footer>
    </x-slidewire::slide>

    {{-- 12 · FLOW DIAGRAM · JOBS VS DRIVERS --}}
    {{-- @notes
        ARCHITECTURE (~90s)
        Darkwood Flow is not a node/edge DAG library. Work moves as Ips through Jobs. Concurrency = IpStrategy + pluggable Driver.
        Walk the Mermaid: DocumentRequest → Fetch → Parse → Excerpt → Validate → ExcerptResult.
        Highlight subgraphs: behavior (test this) vs furniture (do not pin in app tests).
        Table verbally: Job yes, Driver no, Port stub here.
        Punch: if tests break when you swap the driver, they were never testing the product.
        Transition: show the factory — same chain, inject driver.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">{!! $flow !!} · Jobs vs Drivers</p>
            <h2 class="dw-heading-slide">Behavior lives in Jobs</h2>
            <div class="mt-3 dw-embed" style="height:min(420px,52vh)">
                <x-slidewire::diagram>
flowchart TD
    Req[DocumentRequest] --> Fetch[FetchJob]
    Fetch --> Parse[ParseJob]
    Parse --> Excerpt[ExcerptJob]
    Excerpt --> Validate[ValidateJob]
    Validate --> Result[ExcerptResult]

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

    behavior -.->|scheduled by| furniture
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>11 / jobs-vs-drivers</span></footer>
    </x-slidewire::slide>

    {{-- 13 · WORKFLOW ASSEMBLY --}}
    {{-- @notes
        ASSEMBLY (~60s)
        ExcerptWorkflowFactory — yield Jobs once. Pass FiberDriver or AmpDriver. Jobs do not know which.
        Demo commands: php bin/excerpt.php fiber | amp — same title, same excerpt, different furniture.
        Visual rhythm: Input → Steps → Output, not Loop → Fiber → Promise.
        Transition: unit-test Jobs without Flow.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Workflow · not furniture</p>
            <h2 class="dw-heading-slide">Input · Steps · Output</h2>
            <div class="mt-6 dw-flow" style="flex-wrap:wrap;">
                <div class="dw-node" style="min-height:72px;font-size:18px;">Request</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:72px;font-size:18px;">Fetch</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:72px;font-size:18px;">Parse</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:72px;font-size:18px;">Excerpt</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:72px;font-size:18px;">Validate</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:72px;font-size:18px;">Result</div>
            </div>
            <x-slidewire::fragment :index="0">
                <pre class="mt-6 dw-code" style="font-size:14px;">yield new FetchJob($source);
yield new ParseJob();
yield new ExcerptJob($maxExcerptLength);
yield new ValidateJob($minExcerptLength);</pre>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-note mt-4">Same chain. Inject <code>FiberDriver</code> or <code>AmpDriver</code>. Jobs do not care.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>12 / workflow</span></footer>
    </x-slidewire::slide>

    {{-- 14 · GOOD · JOB BEHAVIOR --}}
    {{-- @notes
        GOOD #1 (~75s)
        JobBehaviorTest — ParseJob, ExcerptJob, ValidateJob as ordinary callables. No Flow. No Driver.
        Show parse assertion: title + body meaning.
        Mental model half #1: “If you can unit-test a Job without constructing a Flow, you have found business behavior.”
        Would still be correct if Flow disappeared tomorrow — Symfony Messenger, CLI, Framework X handler.
        Transition: composition still matters — workflow oracle.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Good · JobBehaviorTest</p>
            <h2 class="dw-heading-slide">Observable transforms</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-4 dw-code" style="font-size:15px;">$parsed = (new ParseJob())(new RawDocument('doc://1', $html));

$this->assertSame('Hello Flow', $parsed->title);
$this->assertSame(
    'Jobs transform packets. Drivers schedule them.',
    $parsed->body,
);</pre>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-4">No {!! $flow !!}. No Driver. Still business.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>13 / job-oracle</span></footer>
    </x-slidewire::slide>

    {{-- 15 · GOOD · WORKFLOW ORACLE --}}
    {{-- @notes
        GOOD #2 (~75s)
        WorkflowBehaviorTest — full chain, fixture article.html, assert faithfulness: title, excerpt contains key phrases, length bound.
        Second case: thin document → validation failure (not happy-path shape).
        What is NOT asserted: call order, driver type, await counts, mock expectations on Jobs.
        Honesty about Ip: readonly data; FlowCollector terminal job — same pattern as Flow’s own tests. Optional wishlist: sync run(Ip).
        Transition: punch line of the repo.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Good · WorkflowBehaviorTest</p>
            <h2 class="dw-heading-slide">Faithfulness, not wiring</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-4 dw-code" style="font-size:14px;">$result = FlowCollector::run($flow, new Ip(
    new DocumentRequest('fixture://article'),
));

$this->assertSame('Darkwood Flow Notes', $result->title);
$this->assertStringContainsString(
    'Jobs transform information packets',
    $result->excerpt,
);</pre>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-4">Oracle = excerpt meaning. Not call order.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>14 / workflow-oracle</span></footer>
    </x-slidewire::slide>

    {{-- 16 · MULTI-DRIVER PUNCHLINE --}}
    {{-- @notes
        PUNCHLINE (~90s) — CENTRAL SLIDE
        MultiDriverBehaviorTest: identical assertions under Fiber and Amp via DataProvider.
        If changing the runtime requires rewriting the tests, the tests were coupled to implementation.
        Walk Mermaid: same oracle → both green; await spies → red after swap.
        Applies beyond Flow: Sequential PHP, ReactPHP, Amp, Framework X — if suite breaks while product meaning unchanged, you tested furniture.
        “ReactPHP vs Amp” is the wrong frame for *testing*. Performance/DX matter for runtime choice; almost never for application oracles.
        Pause. Let it land.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Good · MultiDriverBehaviorTest</p>
            <h2 class="dw-heading-slide">Same oracle. Two runtimes.</h2>
            <div class="mt-3 dw-embed" style="height:min(300px,38vh)">
                <x-slidewire::diagram>
flowchart LR
    Oracle[Same behavioral oracle] --> Fiber[FiberDriver]
    Oracle --> Amp[AmpDriver]
    Fiber --> Green1[Green]
    Amp --> Green2[Green]
    Bad[await / driver spies] --> Fiber
    Bad --> Red[Red after swap]
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <pre class="mt-3 dw-code" style="font-size:13px;">yield 'fiber' => [new FiberDriver()];
yield 'amp'   => [new AmpDriver()];
// identical assertions on title + excerpt</pre>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-3">Survive the driver swap → you tested the product.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>15 / multi-driver</span></footer>
    </x-slidewire::slide>

    {{-- 17 · RUNTIME INDEPENDENCE --}}
    {{-- @notes
        GENERALIZATION (~60s)
        Expand beyond Flow. Table from article: if you switch from X and suite breaks while meaning unchanged → diagnosis.
        Architecture should make runtimes replaceable. Flow makes the experiment cheap: inject at factory boundary.
        Transition: mocks under agents — stub ports.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Beyond {!! $flow !!}</p>
            <h2 class="dw-heading-slide">The runtime will change.<br>Your architecture should not.</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>Sequential PHP</h3><p>Call stack ≠ outcome</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>ReactPHP</h3><p>Promises / loop furniture</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>Amp</h3><p>Amp types ≠ domain packets</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>Framework X</h3><p>HTTP adapter ≠ product</p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="4">
                <p class="dw-note mt-6">If the suite breaks while product meaning is unchanged — you tested furniture.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>16 / runtime-independence</span></footer>
    </x-slidewire::slide>

    {{-- 18 · STUB PORTS --}}
    {{-- @notes
        MOCKS (~75s)
        Mocks are not evil. Unexamined mocks under agent velocity are.
        Bergmann stub/mock distinction: stub at a port keeps real Jobs; mock chain replaces collaborators until nothing real remains.
        In nolife-tests: DocumentSource / InMemoryDocumentSource. FetchJob still runs. Network does not.
        Contrast: mock FetchJob → stop URI→RawDocument mapping → agent mocks the next Job…
        Rule of thumb table: Do / Don’t from article.
        When mocks proliferate: maybe you have no named port — architecture never earned the isolation.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Doubles under agents</p>
            <h2 class="dw-heading-slide">Stub the port.<br>Don’t mock the Jobs.</h2>
            <div class="mt-6 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-ok">
                        <h3>Do</h3>
                        <p style="margin:0;"><code>InMemoryDocumentSource</code><br>Unit-test <code>App\Job\*</code><br><code>composer test:good</code></p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-warn">
                        <h3>Don’t</h3>
                        <p style="margin:0;">Mock <code>DriverInterface</code><br>Assert job call order<br>Treat Bad as insurance</p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <pre class="mt-6 dw-code" style="font-size:15px;">$source = new InMemoryDocumentSource([
    'fixture://article' => $html,
]);</pre>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>17 / stubs</span></footer>
    </x-slidewire::slide>

    {{-- 19 · PRESSURE-TEST DECISIONS --}}
    {{-- @notes
        SKILLS (~75s)
        Guillaume Moigneu’s pressure-test-decisions: Socratic protocol — frame decision, audit assumptions, generate options, force closure keep/experiment/information-action.
        Agents should not only write tests — they should grill whether a test deserves to exist.
        Companion vendors the skill + specialization pressure-test-testing-decisions (keep/rewrite/delete/experiment).
        Show example prompt against ImplementationCoupledTest.
        Vocabulary: Oracle, Furniture, Coverage theatre, Driver swap.
        Condensed session end: keep as educational Bad only / never gate merges.
        Shift: from “generate tests” to “defend the insurance claim.”
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Guillaume Moigneu · Agent Skills</p>
            <h2 class="dw-heading-slide">Pressure-test the decision.<br>Don’t generate the suite.</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-6 dw-code" style="font-size:15px;">Use $pressure-test-testing-decisions on
tests/Bad/ImplementationCoupledTest.php —
should we keep, rewrite, or delete it?</pre>
            </x-slidewire::fragment>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="1"><div class="dw-chip">Oracle</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">Furniture</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">Coverage theatre</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-chip">Driver swap</div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>18 / pressure-test</span></footer>
    </x-slidewire::slide>

    {{-- 20 · GRILL QUESTIONS --}}
    {{-- @notes
        GRILL (~60s)
        Walk a few questions one at a time — don’t rush the list.
        Highlight: “Would this fail for the right reason?” and “Six months from now: suite green, product wrong — which test lied?”
        Potencier still stands: agent writes test first, watch it fail for the *right* reason. Pressure-test adds: is this the right reason to care?
        Transition: change the brief you give the agent.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Useful grill · one at a time</p>
            <h2 class="dw-heading-slide">Defend the insurance claim</h2>
            <ul class="dw-list mt-6">
                <x-slidewire::fragment :index="0"><li>Should this be tested at all?</li></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><li>Would this fail for the <em>right</em> reason?</li></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><li>Does this assertion survive a refactoring?</li></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><li>Are these mocks hiding a design problem?</li></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><li>Fiber → Amp: would this fail — and <em>should</em> it?</li></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><li>Suite green, product wrong — which test lied?</li></x-slidewire::fragment>
            </ul>
        </section>
        <footer class="dw-footer"><span>19 / grill</span></footer>
    </x-slidewire::slide>

    {{-- 21 · WEAK VS STRONG BRIEF --}}
    {{-- @notes
        BRIEF (~75s)
        If you change one habit: change the brief.
        Weak: “Add unit tests… high coverage.”
        Strong: pressure-test first; behavioral oracle; stub DocumentSource; no Job mocks; no driver asserts; prefer Red on faithfulness.
        Walk Mermaid: agent proposes → pressure-test → delete/park Bad · Job style · Workflow style · MultiDriver proof.
        TDD still works. Red on Job behavior. Never Red on driver spies.
        Bergmann adjacent: slow suites = coverage ceiling for LLM reviewers; faster than understanding; untouched tests are half the proof.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">One habit change</p>
            <h2 class="dw-heading-slide">Rewrite the brief</h2>
            <div class="mt-4 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-warn" style="min-height:200px;">
                        <h3>Weak</h3>
                        <p style="margin:0;">“Add unit tests.<br>Aim for high coverage.”</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok" style="min-height:200px;">
                        <h3>Strong</h3>
                        <p style="margin:0;">“Pressure-test first.<br>Oracle on Job / workflow meaning.<br>Stub the port. No driver spies.”</p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <div class="mt-4 dw-embed" style="height:min(240px,30vh)">
                    <x-slidewire::diagram>
flowchart TD
    Ask[Agent proposes a test] --> PT{Pressure-test}
    PT -->|furniture / theatre| Del[Delete or park under Bad]
    PT -->|Job meaning| Job[JobBehaviorTest]
    PT -->|composition| Wf[WorkflowBehaviorTest]
    PT -->|runtime claim| Swap[MultiDriverBehaviorTest]
                    </x-slidewire::diagram>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>20 / brief</span></footer>
    </x-slidewire::slide>

    {{-- 22 · LIVE REPOSITORY --}}
    {{-- @notes
        LAB (~60s)
        content/nolife-tests is the laboratory. Walk layout: src/Job, Port, Workflow, tests/Good, tests/Bad, skills/, fixtures.
        Commands on slide — offer to run live if time: composer test:good / test:bad, bin/excerpt.php fiber|amp.
        Highlight skills/ for pressure-test sessions.
        Transition: the experiment worth more than another coverage report.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Companion · <code>content/nolife-tests</code></p>
            <h2 class="dw-heading-slide">Clone it. Break it. Decide.</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-4 dw-code" style="font-size:16px;">composer install
composer test:good   # behavioral insurance
composer test:bad    # educational false insurance
php bin/excerpt.php fiber
php bin/excerpt.php amp</pre>
            </x-slidewire::fragment>
            <div class="mt-4 dw-grid dw-grid-4" style="gap:8px;">
                <x-slidewire::fragment :index="1"><div class="dw-chip" style="font-size:16px;">workflows</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip" style="font-size:16px;">tests Good/Bad</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip" style="font-size:16px;">skills/</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-chip" style="font-size:16px;">fixtures</div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>21 / repository</span></footer>
    </x-slidewire::slide>

    {{-- 23 · BREAK THE CODE --}}
    {{-- @notes
        EXPERIMENT (~45s)
        Concrete homework: open ImplementationCoupledTest, break real ParseJob on purpose, watch which suites notice.
        Bad stays green. Good goes red. That visceral demo beats another coverage report.
        Optional live demo if environment ready.
        Transition: honest limits — credibility.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Homework</p>
            <h2 class="dw-heading-slide">Break <code>ParseJob</code> on purpose.</h2>
            <div class="mt-8 dw-flow">
                <div class="dw-node">Bad</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="border-color:rgba(184,255,106,.45);">stays green</div>
            </div>
            <div class="mt-4 dw-flow">
                <div class="dw-node">Good</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="border-color:rgba(255,120,100,.45);">goes red</div>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-8">Worth more than another coverage report.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>22 / experiment</span></footer>
    </x-slidewire::slide>

    {{-- 24 · HONEST LIMITS --}}
    {{-- @notes
        LIMITS (~50s)
        Credibility slide — do not skip. From article: no sync run(Ip) yet; stubs at ports remain useful; driver tests belong in Flow package; Amp vs Fiber not benchmarked; concurrency strategies in Flow contracts; AI *can* write good tests — risk is volume without oracles.
        What prototype *does* show: readable workflow, two runtimes, three worthless greens, three behavioral oracles, skills for keep/rewrite/delete.
        Transition: close.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Honesty</p>
            <h2 class="dw-heading-slide">What this does not prove</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">No sync <code>run(Ip)</code> yet</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">Stubs at ports still useful</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">Driver CI ≠ app suite</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">Not an Amp vs Fiber bench</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-chip">AI can write good tests</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><div class="dw-chip">Risk = volume without oracles</div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>23 / limits</span></footer>
    </x-slidewire::slide>

    {{-- 25 · CONCLUSION --}}
    {{-- @notes
        CLOSE (~60s)
        Return to opening: most unit tests in agent-touched codebases protect the wrong thing — oracle chose implementation/runtime over meaning.
        Flow already draws the line. AI changed economics: writing is not the bottleneck; choosing trustworthy oracles is.
        Memorable line — deliver slowly, leave silence:
        “The value of a test is not that it passes. The value of a test is that it fails for the right reason.”
        Alternate closer from article: “Your AI writes tests. Make sure they are worth claiming.”
        Insurance that survives a driver swap is insurance on your product. Everything else is furniture polish.
        CTA: clone repo, pressure-test one green test this week, delete furniture polish.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Closing</p>
            <h2 class="dw-heading" style="font-size:clamp(1.7rem,3.6vw,2.8rem);">The value of a test<br>is not that it passes.</h2>
            <x-slidewire::fragment :index="0">
                <h2 class="dw-heading mt-6" style="font-size:clamp(1.7rem,3.6vw,2.8rem);"><span class="dw-accent">It is that it fails<br>for the right reason.</span></h2>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <div class="mt-10 dw-flow">
                    <div class="dw-node">Jobs</div>
                    <div class="dw-arrow">→</div>
                    <div class="dw-node">Oracles</div>
                    <div class="dw-arrow">→</div>
                    <div class="dw-node">Insurance</div>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 26 · RESOURCES --}}
    {{-- @notes
        RESOURCES (~30s)
        Point to article, companion, Flow docs, Potencier LinkedIn posts, Bergmann oracle / stub-mock articles, Moigneu skills.
        Leave URL / path on screen. Thank the room. Offer Q&A.
        Final optional line: “Writing tests is cheap. Claiming them is not.”
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Resources</p>
            <h2 class="dw-heading-slide">Go deeper</h2>
            <ul class="dw-list mt-6">
                <li>Article — <em>From No-Life Testing to Behavioral Testing</em></li>
                <li><code>content/nolife-tests</code> — <code>composer test:good</code></li>
                <li>Darkwood Flow — darkwood-com.github.io/flow</li>
                <li>Potencier — Weak tests are now a liability</li>
                <li>Bergmann — Seeing the Truth: Test Oracles</li>
                <li>Moigneu — <code>pressure-test-decisions</code> / testing specialization</li>
            </ul>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-6">Your AI writes tests. Make sure they are worth claiming.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>24 / resources</span></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
