{{-- PHP Speed Tooling: Partial Function Application, Tokens and Flow | /slides/php-speed-tooling --}}
{{-- Source: tasks/#af5bbc_flow-partial-function-application/article.md --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $pfa = '<span class="dw-accent">PFA</span>';
        $flow = '<span class="dw-accent">Flow</span>';
        $php = '<span class="dw-accent">PHP</span>';
        $tokens = '<span class="dw-accent">PhpToken</span>';
    @endphp

    {{-- 1 · TITLE --}}
    {{-- @notes How much tooling can PHP provide before another abstraction earns its place? Three experiments. PHP 8.6.0beta2. ~20s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · {!! $php !!} 8.6 · {!! $flow !!}</p>
            <h1 class="dw-title">PHP Speed Tooling</h1>
            <p class="dw-lead">Partial Function Application,<br>Tokens and {!! $flow !!}</p>
            <p class="dw-question mt-8">How much tooling can PHP provide<br>before another abstraction earns its place?</p>
            <p class="dw-note mt-8">PHP 8.6 experiment · Darkwood</p>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · THE RULE --}}
    {{-- @notes This is the whole talk. Point at each row. Come back here at the end. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The rule</p>
            <div class="mt-6" style="max-width:720px;margin:0 auto;display:flex;flex-direction:column;gap:12px;">
                <div class="dw-pipeline-row" style="justify-content:space-between;"><span>Need argument binding?</span><span><span class="dw-arrow">→</span> <span class="dw-accent">PFA</span></span></div>
                <div class="dw-pipeline-row" style="justify-content:space-between;"><span>Need lightweight source structure?</span><span><span class="dw-arrow">→</span> <span class="dw-accent">PHP tokens</span></span></div>
                <div class="dw-pipeline-row" style="justify-content:space-between;"><span>Need to iterate local files?</span><span><span class="dw-arrow">→</span> <span class="dw-accent">foreach</span></span></div>
                <div class="dw-pipeline-row" style="justify-content:space-between;"><span>Need actual orchestration?</span><span><span class="dw-arrow">→</span> <span class="dw-accent">consider Flow</span></span></div>
            </div>
            <p class="dw-takeaway mt-8">Use the PHP primitive first.</p>
        </section>
        <footer class="dw-footer"><span>01 / rule</span></footer>
    </x-slidewire::slide>

    {{-- 3 · CLOSURE TAX --}}
    {{-- @notes The extra fn exists only to leave one argument open. PHP 8.6 PFA deletes that adapter. ~30s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Experiment 1 · closure tax</p>
            <pre class="dw-code" style="font-size:18px;">fn ($value) => transform($value, $configuration)</pre>
            <p class="dw-note mt-4" style="font-size:22px;">↓</p>
            <pre class="dw-code" style="font-size:18px;">transform(?, $configuration)</pre>
            <p class="dw-note mt-6">PHP 8.6 Partial Function Application</p>
            <p class="dw-takeaway mt-6">The extra closure existed only to bind one argument.</p>
        </section>
        <footer class="dw-footer"><span>02 / pfa</span></footer>
    </x-slidewire::slide>

    {{-- 4 · FCC VS PFA --}}
    {{-- @notes Left is already PHP 8.1. Right is 8.6. PFA starts when some args are known. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">FCC vs PFA</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>First-class callable</h3>
                    <pre class="dw-code" style="font-size:16px;margin:0;white-space:pre;">fn ($ctx) => $step->apply($ctx)

$step->apply(...)</pre>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Partial Function Application</h3>
                    <pre class="dw-code" style="font-size:16px;margin:0;white-space:pre;">fn ($v) => applyBudget($v, $budget)

applyBudget(?, $budget)</pre>
                </div>
            </div>
            <p class="dw-takeaway mt-6">PFA starts where FCC stops: some arguments are already known.</p>
        </section>
        <footer class="dw-footer"><span>03 / fcc-pfa</span></footer>
    </x-slidewire::slide>

    {{-- 5 · ? VS ... --}}
    {{-- @notes ? is always required. ... keeps defaults. Bound args run at creation, not invoke. Not an RFC slide. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker"><code>?</code> vs <code>...</code></p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel">
                    <pre class="dw-code" style="font-size:16px;margin:0;">foo(?, $config)</pre>
                    <p class="dw-note mt-4">exact placeholder<br>required in the resulting Closure</p>
                </div>
                <div class="dw-split-panel">
                    <pre class="dw-code" style="font-size:16px;margin:0;">foo('value', ...)</pre>
                    <p class="dw-note mt-4">remaining list stays open<br>optionals keep their defaults</p>
                </div>
            </div>
            <p class="dw-takeaway mt-8">Bound arguments are evaluated when the partial is created.</p>
        </section>
        <footer class="dw-footer"><span>04 / placeholders</span></footer>
    </x-slidewire::slide>

    {{-- 6 · PFA INTO FLOW --}}
    {{-- @notes Same Closure type-hint. Zero Flow source changes. 8.6.0beta2. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">{!! $pfa !!} goes straight into {!! $flow !!}</p>
            <pre class="dw-code" style="font-size:14px;">$flow = new Flow(
    static fn (BenchmarkState $state): BenchmarkState =>
        loadPassages($state, $corpus),
);</pre>
            <p class="dw-note" style="margin:8px 0;">↓</p>
            <pre class="dw-code" style="font-size:14px;">$flow = new Flow(loadPassages(?, $corpus));
$flow
    ->fn(applyBudgetToState(?, 12))
    ->fn(collect(?, $box));</pre>
            <h2 class="dw-heading mt-6" style="font-size:clamp(2rem,5vw,3.2rem);">{!! $flow !!} changed: <span class="dw-accent">0 lines</span></h2>
            <p class="dw-note mt-2">PFA already produces the <code>Closure</code> Flow expects.</p>
        </section>
        <footer class="dw-footer"><span>05 / flow</span></footer>
    </x-slidewire::slide>

    {{-- 7 · PIPE ≠ PFA --}}
    {{-- @notes Bind / thread / execute. Complementary. Native pipe + PFA produced hello world. ~35s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Pipe ≠ Partial Application</p>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:10px;">
                <div class="dw-card"><h3>PFA</h3><p>bind arguments</p></div>
                <div class="dw-card"><h3><code>|&gt;</code></h3><p>thread a value</p></div>
                <div class="dw-card"><h3>Flow</h3><p>execute jobs</p></div>
            </div>
            <pre class="mt-6 dw-code" style="font-size:15px;">$input
    |> removeNoise(...)
    |> normalizeWhitespace(...)
    |> applyBudget(?, 14);</pre>
            <p class="dw-takeaway mt-6">They are complementary, not competing syntax.</p>
        </section>
        <footer class="dw-footer"><span>06 / pipe</span></footer>
    </x-slidewire::slide>

    {{-- 8 · FIRST CONCLUSION --}}
    {{-- @notes Transition. PHP solved the syntax. Same rule now applied to tools. ~25s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">First conclusion</p>
            <h2 class="dw-heading" style="font-size:clamp(2.2rem,5.5vw,3.6rem);">We almost added nothing.</h2>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:10px;">
                <div class="dw-chip">No <code>partial()</code></div>
                <div class="dw-chip">No curry API</div>
                <div class="dw-chip">No pipe emulation</div>
                <div class="dw-chip">No PHP 8.6 requirement for Flow</div>
            </div>
            <p class="dw-takeaway mt-8">PHP already solved the syntax problem.</p>
        </section>
        <footer class="dw-footer"><span>07 / nothing</span></footer>
    </x-slidewire::slide>

    {{-- 9 · SPEED TOOLING --}}
    {{-- @notes Same primitive-first rule, one layer down. Tokens if tokens are enough. Not an AST-vs-tokens bake-off. ~30s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">PHP Speed Tooling</p>
            <h2 class="dw-heading-slide">What if the same rule applies<br>to tools written about PHP?</h2>
            <div class="mt-8 dw-flow dw-flow--vertical" style="max-width:420px;margin:0 auto;gap:6px;">
                <div class="dw-node">Need a parser stack?</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Maybe just tokens</div>
            </div>
            <p class="dw-takeaway mt-8">Choose the cheapest representation<br>that still contains the information you need.</p>
        </section>
        <footer class="dw-footer"><span>08 / tooling</span></footer>
    </x-slidewire::slide>

    {{-- 10 · OUR TOOL --}}
    {{-- @notes tools/ is Darkwood local convention. PhpToken. Report only. ~540 lines. ~35s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Our tool · <code>tools/pfa-opportunities</code></p>
            <pre class="mt-6 dw-code" style="font-size:20px;line-height:1.55;text-align:center;">PHP files
        ↓
PhpToken::tokenize()
        ↓
ignore whitespace / comments
        ↓
find simple arrow functions
        ↓
PFA / FCC / ignore</pre>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:8px;">
                <div class="dw-chip">No php-parser</div>
                <div class="dw-chip">~540 lines</div>
                <div class="dw-chip">report only</div>
            </div>
        </section>
        <footer class="dw-footer"><span>09 / scanner</span></footer>
    </x-slidewire::slide>

    {{-- 11 · CONSERVATIVE --}}
    {{-- @notes Receiver-is-parameter is the trap. Nested calls and && are silence. False negatives OK. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Conservative by design</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>Accepted</h3>
                    <pre class="dw-code" style="font-size:14px;margin:0;">fn ($x) => foo($x, $bound)
→ foo(?, $bound)

fn ($x) => $step->apply($x)
→ $step->apply(...)</pre>
                </div>
                <div class="dw-split-panel is-warn">
                    <h3>Rejected</h3>
                    <pre class="dw-code" style="font-size:14px;margin:0;">fn ($p) => $p->toArray()

fn ($x) => foo(bar($x), $c)

fn ($x) => complicated($x)
    && other($x)</pre>
                </div>
            </div>
            <p class="dw-takeaway mt-6">False negatives are acceptable.<br>False refactoring advice is not.</p>
        </section>
        <footer class="dw-footer"><span>10 / conservative</span></footer>
    </x-slidewire::slide>

    {{-- 12 · FINDINGS --}}
    {{-- @notes Zero is the result. 3 FCC in flow-pipe. Flow src silent. Do not inflate. ~35s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What did it find?</p>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:10px;">
                <div class="dw-card"><h3>144</h3><p>PHP files</p></div>
                <div class="dw-card"><h3>498,107</h3><p>bytes</p></div>
                <div class="dw-card"><h3>51,663</h3><p>significant tokens</p></div>
            </div>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px;">
                <div class="dw-card"><h3 class="dw-accent">0</h3><p>PFA candidates</p></div>
                <div class="dw-card"><h3>3</h3><p>FCC candidates</p></div>
                <div class="dw-card"><h3>0</h3><p>Flow src</p></div>
            </div>
            <h2 class="dw-heading mt-8" style="font-size:clamp(2rem,5vw,3.2rem);">Zero is a result.</h2>
            <p class="dw-note mt-2">The selected production code did not contain obvious PFA migration sites.</p>
        </section>
        <footer class="dw-footer"><span>11 / findings</span></footer>
    </x-slidewire::slide>

    {{-- 13 · TOKENS UNTIL THEY AREN'T --}}
    {{-- @notes Syntax vs semantics. Rewriting needs an AST. Not anti-parser. ~30s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Tokens are enough… until they aren’t</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>Tokens are enough for</h3>
                    <p style="margin:0;">simple syntactic shapes<br>arrow functions<br>argument positions<br>method call patterns</p>
                </div>
                <div class="dw-split-panel is-warn">
                    <h3>AST / semantics may be needed for</h3>
                    <p style="margin:0;">scope<br>types<br>name resolution<br>reliable automated rewriting</p>
                </div>
            </div>
            <p class="dw-takeaway mt-6">Speed Tooling means avoiding unnecessary machinery —<br>not refusing necessary machinery.</p>
        </section>
        <footer class="dw-footer"><span>12 / limits</span></footer>
    </x-slidewire::slide>

    {{-- 14 · FOREACH VS FLOW --}}
    {{-- @notes Same scanFile(). 79.5 vs 120.2 ms. Not “Flow is slow.” Orchestration without an orchestration problem. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Should the scanner use Flow?</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>Plain PHP</h3>
                    <pre class="dw-code" style="font-size:15px;margin:0;white-space:pre;">foreach ($files as $file) {
    scanFile($file, $config);
}</pre>
                </div>
                <div class="dw-split-panel">
                    <h3>Flow</h3>
                    <pre class="dw-code" style="font-size:15px;margin:0;white-space:pre;">$scan = scanFile(?, $config);
$flow = new Flow($scan);
$flow(new Ip($file));
$flow->await();</pre>
                </div>
            </div>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:8px;">
                <div class="dw-chip">Plain ~79.5 ms</div>
                <div class="dw-chip">Flow ~120.2 ms</div>
                <div class="dw-chip">Peak ~6 MiB both</div>
            </div>
            <p class="dw-takeaway mt-6"><code>foreach</code> wins here. Flow added orchestration without an orchestration problem.</p>
        </section>
        <footer class="dw-footer"><span>13 / foreach</span></footer>
    </x-slidewire::slide>

    {{-- 15 · WHEN FLOW EARNS ITS PLACE --}}
    {{-- @notes Only verified capabilities. No branching/retries/observability product. ~30s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">When Flow earns its place</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>PHP primitives</h3>
                    <p style="margin:0;">argument binding<br>callables<br>pipeline syntax<br>tokenization<br>simple iteration</p>
                </div>
                <div class="dw-split-panel">
                    <h3>Flow</h3>
                    <p style="margin:0;">multiple IPs<br>driver-controlled execution<br>error jobs<br>events<br>IP strategies<br>await()</p>
                </div>
            </div>
            <p class="dw-takeaway mt-8">Flow becomes useful when execution itself becomes the problem.</p>
        </section>
        <footer class="dw-footer"><span>14 / boundary</span></footer>
    </x-slidewire::slide>

    {{-- 16 · FINAL RULE --}}
    {{-- @notes Close on the three substitutions. Add Flow when foreach stops. ~20s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <h2 class="dw-heading" style="font-size:clamp(2.4rem,6vw,4rem);">PHP primitive first.</h2>
            <div class="mt-8" style="display:flex;flex-direction:column;gap:12px;max-width:720px;margin:0 auto;">
                <div class="dw-card"><p style="margin:0;font-size:22px;">{!! $pfa !!} instead of an adapter abstraction.</p></div>
                <div class="dw-card"><p style="margin:0;font-size:22px;">{!! $tokens !!} instead of a parser stack.</p></div>
                <div class="dw-card"><p style="margin:0;font-size:22px;"><code>foreach</code> instead of an orchestration engine.</p></div>
            </div>
            <p class="dw-takeaway mt-8">Add Flow when execution becomes orchestration.</p>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
