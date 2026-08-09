{{-- The Thanks Agent — When orchestration guarantees execution, not value --}}
{{-- Source: tasks/#760c5b_thanks-agent/article.md · Companion: content/thanks-agent --}}
{{-- Route: /slides/thanks-agent --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $flow = '<span class="dw-accent">Flow</span>';
        $thanks = '<span class="dw-accent">Thanks Agent</span>';
        $value = '<span class="dw-accent">value</span>';
    @endphp

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 1 · TITLE --}}
    {{-- @notes
        OPENING (~45s)
        Standalone Darkwood talk. Original experiment — no external framing.
        Question: what happens when an orchestration system executes work that has no value?
        Companion: content/thanks-agent — Symfony 8 + darkwood/flow demo.
        Timing: title + pause. Let people read the subtitle.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · PHP · agents · {!! $flow !!}</p>
            <h1 class="dw-title">The Thanks<br>Agent</h1>
            <p class="dw-lead">When orchestration guarantees<br><span class="dw-accent">execution, not value</span></p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Execution</div>
                <div class="dw-arrow">≠</div>
                <div class="dw-node">Value</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · THE QUESTION --}}
    {{-- @notes
        THE QUESTION (~40s)
        Deliver slowly. This is the thesis question — attributed to nobody.
        Experiment, not commentary. Transition: show the setup.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The question</p>
            <h2 class="dw-question" style="margin-top:0;">What happens when an<br>orchestration system executes<br>work that has <span class="dw-accent">no value</span>?</h2>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-10">Not rhetorical. An engineering experiment.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>01 / question</span></footer>
    </x-slidewire::slide>

    {{-- 3 · THE EXPERIMENT --}}
    {{-- @notes
        EXPERIMENT (~60s)
        Five useful agents + one useless. Same runtime treatment.
        Value weights from AgentId::valueWeight(). SummaryAgent is fan-in.
        CLI: thanks-agent:compare --both --save
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The experiment · content/thanks-agent</p>
            <h2 class="dw-heading-slide">Five useful. One useless.<br>Same runtime.</h2>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>FetchDocs</h3><p>Value +25</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>Github</h3><p>Value +40</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>PHP</h3><p>Value +30</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>Mozilla</h3><p>Value +20</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-card"><h3>Cost</h3><p>Value +15</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><div class="dw-card" style="border-color:rgba(255,120,80,.45);"><h3>Thanks</h3><p>Value <span class="dw-accent">+0</span></p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="6">
                <p class="dw-takeaway mt-6">+ SummaryAgent (+15) after fan-in</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>02 / experiment</span></footer>
    </x-slidewire::slide>

    {{-- 4 · WHAT THANKS DOES --}}
    {{-- @notes
        THANKS AGENT (~50s)
        Show the produce() essence. Excellent availability. Zero production.
        Joke (calm): “It has never introduced a regression. It also has never produced anything.”
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">ThanksAgent</p>
            <h2 class="dw-heading-slide">Always says “Thanks.”</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-4 dw-code" style="font-size:15px;">return [
  'output'   => 'Thanks.', // or Looks good. / Good job.
  'tokens'   => 2,
  'messages' => 28,
  'didWork'  => false,     // value forced to 0
];</pre>
            </x-slidewire::fragment>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="1"><div class="dw-chip">No tools · no context · no work</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">Still scheduled · metered · logged</div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>03 / thanks-agent</span></footer>
    </x-slidewire::slide>

    {{-- 5 · ACTIVITY ≠ THROUGHPUT --}}
    {{-- @notes
        THESIS (~45s)
        Three inequalities. Central line: scheduler cannot distinguish productive from performative.
        Pause after the takeaway.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Thesis</p>
            <h2 class="dw-heading-slide">Activity is not throughput</h2>
            <div class="mt-6 dw-vs-list">
                <x-slidewire::fragment :index="0"><div class="dw-vs-item">execution ≠ value</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-vs-item">messages ≠ work</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-vs-item">visibility ≠ outcome</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <p class="dw-takeaway mt-8">The scheduler cannot distinguish<br>productive work from performative work.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>04 / thesis</span></footer>
    </x-slidewire::slide>

    {{-- 6 · WHAT AN AGENT CONSUMES --}}
    {{-- @notes
        COST DIMENSIONS (~60s)
        Thanks barely burns CPU/tokens. Still burns slots, messages, events, attention.
        Org pattern in miniature: visibility easier to measure than outcomes.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Cost is plural</p>
            <h2 class="dw-heading-slide">What an agent consumes</h2>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>Scheduler</h3><p>WIP / MaxIpStrategy</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>Tokens</h3><p>Simulated in demo</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>Messages</h3><p>Chatty acknowledgements</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>Context</h3><p>Prompt / index slices</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-card"><h3>Logs</h3><p>Observability volume</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><div class="dw-card"><h3>Attention</h3><p>Decision queue</p></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>05 / consumption</span></footer>
    </x-slidewire::slide>

    {{-- 7 · FLOW TODAY --}}
    {{-- @notes
        FLOW MODEL (~75s)
        Honest: linear FBP pipeline, not DAG. Ip → Job → Driver.
        Not ReactPHP/Amp replacement. How little machinery do we need?
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">{!! $flow !!} today · PHP ≥ 8.5</p>
            <h2 class="dw-heading-slide">Linear async pipeline.<br>Not a DAG engine.</h2>
            <div class="mt-4 dw-embed" style="height:min(220px,32vh)">
                <x-slidewire::diagram>
flowchart LR
  IP[Ip] --> Job[Job]
  Job --> Driver[Driver]
  Driver --> Events[PUSH PULL POP ASYNC]
  Events --> Job
                </x-slidewire::diagram>
            </div>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">FlowFactory · MaxIpStrategy</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">FiberDriver · StreamSelectDriver</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-4">Many IPs in flight ≠ graph joins.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>06 / flow</span></footer>
    </x-slidewire::slide>

    {{-- 8 · SCATTER / GATHER --}}
    {{-- @notes
        ORCHESTRATION (~70s)
        AgentPipelineFactory: push N Ips, await, SummaryAgent::executeSync.
        RunStore is the bag. Honesty: resembles scatter/gather; not a DAG API.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Scatter / gather · demo pattern</p>
            <h2 class="dw-heading-slide">Multiple IPs. Shared bag. Sync join.</h2>
            <div class="mt-3 dw-embed" style="height:min(280px,38vh)">
                <x-slidewire::diagram>
flowchart LR
  Ips[Multiple Ips] --> Stage[ExecuteAgentJob]
  Stage --> Store[RunStore]
  Store --> Summary[SummaryAgent]
  Summary --> Report[Scoreboard]
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-2">MaxIpStrategy caps concurrency · fan-in is application state</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>07 / scatter-gather</span></footer>
    </x-slidewire::slide>

    {{-- 9 · SCOREBOARD --}}
    {{-- @notes
        NUMBERS (~90s)
        Real report: compare-*-20260808-144317.json, FiberDriver, PHP 8.5.4.
        Tokens/cost simulated (RATE_PER_1K=0.002). Wall/events measured.
        Value Δ = 0. Cost +0.00962. Messages +29.
        Thanks alone: value 0, messages 28, cost 0.008904.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Scoreboard · FiberDriver · PHP 8.5.4</p>
            <h2 class="dw-heading-slide">Value Δ = <span class="dw-accent">0</span></h2>
            <div class="mt-6 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-ok">
                        <h3>Without Thanks</h3>
                        <p style="margin:0;font-size:clamp(18px,1.8vw,24px);line-height:1.45;">
                            Value <strong>145</strong><br>
                            Cost 0.012<br>
                            Messages 19<br>
                            Events 5/5/5/5
                        </p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-warn">
                        <h3>With Thanks</h3>
                        <p style="margin:0;font-size:clamp(18px,1.8vw,24px);line-height:1.45;">
                            Value <strong>145</strong><br>
                            Cost 0.022 · <span class="dw-accent">+0.010</span><br>
                            Messages 48 · <span class="dw-accent">+29</span><br>
                            Events 6/6/6/6
                        </p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-6">Useful output unchanged. Resources up.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>08 / scoreboard</span></footer>
    </x-slidewire::slide>

    {{-- 10 · COST WITHOUT VALUE --}}
    {{-- @notes
        ECONOMICS (~50s)
        Cost per request vs cost per useful result.
        Thanks: tiny request cost → infinite per useful result when useful=0.
        Simulated arithmetic — shape is the lesson.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Economics</p>
            <h2 class="dw-heading-slide">Cost without value</h2>
            <div class="mt-6 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel">
                        <h3>Cost / request</h3>
                        <p style="margin:0;">Looks tiny on the invoice line</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-warn">
                        <h3>Cost / useful result</h3>
                        <p style="margin:0;">Thanks → tends to <span class="dw-accent">∞</span></p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-8">Cheap useless work is not cheap.<br>It is subsidized noise.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>09 / cost</span></footer>
    </x-slidewire::slide>

    {{-- 11 · SUPERVISION --}}
    {{-- @notes
        SUPERVISION (~70s)
        Adding execution capacity moves the bottleneck.
        SupervisionBudget(5) — resource budget, not superstition.
        Measure decisions waiting, not tasks running.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Bottleneck moves</p>
            <h2 class="dw-heading-slide">Supervision is scarce too</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>Model concurrency</h3><p>Calls you can fire</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>Runtime concurrency</h3><p>IPs in flight</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>Human supervision</h3><p>Open contexts</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>Validation capacity</h3><p>Reject / accept bandwidth</p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="4">
                <p class="dw-takeaway mt-6">Stop counting running tasks.<br>Count decisions waiting. · <code>SupervisionBudget</code></p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>10 / supervision</span></footer>
    </x-slidewire::slide>

    {{-- 12 · ASYNC BENCH --}}
    {{-- @notes
        BENCH (~75s)
        bench-io-20260808-144318.json — synthetic delayed sockets, 6 tasks incl Thanks.
        sequential 600ms → stream_select 167ms → flow 179ms. Poll skipped on 8.5.4.
        Value stays 130. Mux ≠ value.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">thanks-agent:bench-io · synthetic I/O · PHP 8.5.4</p>
            <h2 class="dw-heading-slide">Async is waiting efficiently</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>sequential</h3><p>~600 ms · value 130</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>stream_select</h3><p>~167 ms · value 130</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>Flow StreamSelect</h3><p>~179 ms · value 130</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>PHP 8.6 Poll</h3><p>skipped on 8.5.4</p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="4">
                <p class="dw-takeaway mt-6">~3.6× wall improvement. Same emptiness from Thanks.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>11 / async</span></footer>
    </x-slidewire::slide>

    {{-- 13 · POLLING ≠ VALUE --}}
    {{-- @notes
        CALLBACK (~40s)
        Faster orchestration amplifies the objective function.
        PollerInterface sits under the scheduler — not an event loop, not Amp.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Editorial callback</p>
            <h2 class="dw-heading">Polling is not<br><span class="dw-accent">value</span> either</h2>
            <x-slidewire::fragment :index="0">
                <p class="dw-lead mt-8">A better poller can execute<br>useless work faster.</p>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-8">Performance amplifies whatever<br>the system is optimizing.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>12 / amplify</span></footer>
    </x-slidewire::slide>

    {{-- 14 · UNHAPPY PATH --}}
    {{-- @notes
        TERMINALS (~60s)
        CompleteEvent / ErrorEvent / CostLedger.
        Demo: thanks-agent:run --simulate-error
        Abandon/cancel still open — CancellationTicket stub only.
        Symfony AI #2363 is external lesson, not a dependency claim.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Terminal lifecycle</p>
            <h2 class="dw-heading-slide">The unhappy path must be billed</h2>
            <div class="mt-3 dw-embed" style="height:min(260px,36vh)">
                <x-slidewire::diagram>
flowchart TB
  Exec[Execute] --> Success[Success]
  Exec --> Failure[Failure]
  Exec --> Abandon[Abandon]
  Success --> Complete[CompleteEvent]
  Failure --> Error[ErrorEvent]
  Abandon --> Hole[Accounting hole]
  Complete --> Ledger[CostLedger]
  Error --> Ledger
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-2">Happy-path-only FinOps lies.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>13 / terminals</span></footer>
    </x-slidewire::slide>

    {{-- 15 · WHAT CHANGED + ROADMAP --}}
    {{-- @notes
        FLOW DELTA (~70s)
        Shipped vs roadmap. Be precise. Demo meters via App MetricsSubscriber today;
        Flow COMPLETE/ERROR + CostLedgerSubscriber are promoted primitives.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What changed in {!! $flow !!}</p>
            <h2 class="dw-heading-slide">Shipped · next</h2>
            <div class="mt-6 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-ok">
                        <h3>Promoted</h3>
                        <p style="margin:0;font-size:clamp(16px,1.6vw,22px);line-height:1.5;">
                            CostLedger · ValueTag<br>
                            Complete / Error<br>
                            SupervisionBudget<br>
                            PollerInterface · Duration
                        </p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel">
                        <h3>Roadmap</h3>
                        <p style="margin:0;font-size:clamp(16px,1.6vw,22px);line-height:1.5;">
                            Cancel · abandon terminal<br>
                            True DAG joins<br>
                            Decision-queue metrics<br>
                            CodeMap · AgentRouter
                        </p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-6">Value and cost belong in the runtime vocabulary.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>14 / flow-delta</span></footer>
    </x-slidewire::slide>

    {{-- 16 · CLOSING --}}
    {{-- @notes
        CLOSE (~50s)
        We know how to make agents run. Harder: which work should exist.
        Not a seventh agent — a better definition of value.
        End on Flow, not the joke.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Closing</p>
            <h2 class="dw-heading">We already know how<br>to make agents run.</h2>
            <x-slidewire::fragment :index="0">
                <p class="dw-lead mt-8">The harder problem is deciding<br>which work should exist at all.</p>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-8">The next optimization is not another agent.<br>It is a better definition of <span class="dw-accent">value</span>.</p>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="2">
                <div class="mt-10 dw-flow">
                    <div class="dw-node">Activity</div>
                    <div class="dw-arrow">→</div>
                    <div class="dw-node">{!! $value !!}</div>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
