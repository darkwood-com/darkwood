{{-- Laravel Pipeline & Darkwood Flow — execution models | /slides/laravel-pipeline-flow --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $flow = '<span class="dw-accent">Flow</span>';
        $laravel = '<span class="dw-accent">Laravel Pipeline</span>';
    @endphp

    {{-- 1 · TITLE --}}
    {{-- @notes Open with the thesis, not a framework fight. Set expectation: composition vs execution. Mention blog article + runnable demo repo. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · PHP 8.5 · Laravel · {!! $flow !!}</p>
            <h1 class="dw-title">Laravel Pipeline<br>&amp; Darkwood Flow</h1>
            <p class="dw-lead">Two execution models for<br><span class="dw-accent">composing PHP steps</span></p>
            <p class="dw-note mt-6">Symfony Console demo · <code>demo:pipeline:compare</code></p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Composition</div>
                <div class="dw-arrow">≠</div>
                <div class="dw-node">Execution</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · OVERLOADED WORD --}}
    {{-- @notes Ask audience what "pipeline" means to them. List four meanings — same composition word, different runtimes. ~60s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The overloaded word</p>
            <h2 class="dw-heading-slide">"Pipeline" means composition.<br>Not execution.</h2>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:12px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">HTTP middleware</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">Unix pipes</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">CI/CD stages</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">Dataflow / ETL</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="4">
                <p class="dw-question mt-8">Same word. Different execution contracts.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>01 / pipeline</span></footer>
    </x-slidewire::slide>

    {{-- 3 · CORE IDEA --}}
    {{-- @notes State the thesis clearly. Pause here — this is the talk's anchor. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Core idea</p>
            <h2 class="dw-heading" style="font-size:clamp(1.8rem,4vw,2.8rem);">A pipeline defines<br><span class="dw-accent">how work is composed</span>.</h2>
            <x-slidewire::fragment :index="0">
                <h2 class="dw-heading mt-6" style="font-size:clamp(1.8rem,4vw,2.8rem);">The runtime defines<br><span class="dw-accent">how that work executes</span>.</h2>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-note mt-8">Ask: "what are the steps?" and "how does it execute?"</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>02 / thesis</span></footer>
    </x-slidewire::slide>

    {{-- 4 · LARAVEL ONION --}}
    {{-- @notes Walk through array_reverse + array_reduce. ~30 lines total. Emphasize elegance. Show minimal code only. ~90s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">{!! $laravel !!}</p>
            <h2 class="dw-heading-slide">The closure onion</h2>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>array_reverse</h3><p>Pipes folded outer-first</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>array_reduce</h3><p>Nested closures</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>One passable</h3><p>One sync call stack</p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <pre class="mt-4 dw-code" style="font-size:14px;">$pipeline = array_reduce(
    array_reverse($pipes),
    $carry,
    $destination
);
return $pipeline($passable);</pre>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>03 / laravel-onion</span></footer>
    </x-slidewire::slide>

    {{-- 5 · LARAVEL EXECUTION DIAGRAM --}}
    {{-- @notes through([A,B,C]) → A(B(C(dest))). A runs first. $next = rest of chain. ~60s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Laravel execution</p>
            <h2 class="dw-heading-slide"><code>A(B(C(destination)))</code></h2>
            <div class="mt-4 dw-embed" style="height:min(340px,42vh)">
                <x-slidewire::diagram>
flowchart TB
  subgraph onion ["through A, B, C"]
    A["Pipe A\noutermost"]
    B["Pipe B"]
    C["Pipe C"]
    D["destination"]
  end
  A --> B --> C --> D
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-4"><code>$next</code> = the rest of the chain, already folded.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>04 / laravel-diagram</span></footer>
    </x-slidewire::slide>

    {{-- 6 · $NEXT --}}
    {{-- @notes Before/after/short-circuit. HTTP kernel: one request, one onion. Not a benchmark slide. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Why <code>$next</code> matters</p>
            <h2 class="dw-heading-slide">The middleware contract</h2>
            <div class="mt-6 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-ok">
                        <h3>Before</h3>
                        <p style="margin:0;">Run logic, then <code>$next($passable)</code></p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok">
                        <h3>After</h3>
                        <p style="margin:0;"><code>$response = $next($passable)</code>, then transform</p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <div class="mt-4 dw-split-panel is-warn" style="max-width:640px;margin:0 auto;">
                    <h3>Short-circuit</h3>
                    <p style="margin:0;">Return without calling <code>$next</code> → chain stops</p>
                </div>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="3">
                <p class="dw-note mt-6">Perfect for one HTTP request · one passable · one call stack</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>05 / next</span></footer>
    </x-slidewire::slide>

    {{-- 7 · DEMO SETUP --}}
    {{-- @notes Introduce demo: Fetch→Hash→Save, 5 tasks, fixed delays, no HTTP. Commands to run live if possible. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The demo</p>
            <h2 class="dw-heading-slide">Same pipeline. Two runtimes.</h2>
            <div class="mt-4 dw-flow">
                <div class="dw-node">Fetch</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Hash</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Save</div>
            </div>
            <x-slidewire::fragment :index="0">
                <div class="mt-6 dw-grid dw-grid-3" style="gap:8px;font-size:15px;">
                    <div class="dw-chip">0.8s</div>
                    <div class="dw-chip">0.3s</div>
                    <div class="dw-chip">0.6s</div>
                    <div class="dw-chip">0.2s</div>
                    <div class="dw-chip">0.5s</div>
                </div>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <pre class="mt-4 dw-code" style="font-size:15px;">php bin/console demo:pipeline:compare</pre>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="2">
                <p class="dw-note mt-4">In-memory fixture · controlled timers · not a benchmark</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>06 / demo</span></footer>
    </x-slidewire::slide>

    {{-- 8 · FIBER MISCONCEPTION --}}
    {{-- @notes Critical slide. Fibers ≠ async I/O. sleep/usleep/blocking HTTP still block. Event loop needed. FiberDriver uses time() — second precision. ~90s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Common misconception</p>
            <h2 class="dw-heading-slide">Fibers do <span class="dw-accent">not</span> make blocking code async.</h2>
            <div class="mt-6 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-warn">
                        <h3>Still blocks the thread</h3>
                        <ul class="dw-list-compact" style="margin:0;">
                            <li><code>sleep()</code> / <code>usleep()</code></li>
                            <li>Blocking HTTP clients</li>
                            <li><code>file_get_contents()</code></li>
                        </ul>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok">
                        <h3>Useful overlap needs</h3>
                        <ul class="dw-list-compact" style="margin:0;">
                            <li>Event-loop timers</li>
                            <li>Non-blocking I/O integration</li>
                            <li>Driver that schedules waits</li>
                        </ul>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-6">Demo uses <code>AmpDriver</code> + <code>Amp\delay()</code> — not raw Fibers alone.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>07 / fibers</span></footer>
    </x-slidewire::slide>

    {{-- 9 · FLOW MODEL --}}
    {{-- @notes Introduce Flow, Job, Ip, Driver, await(). Contrast with onion — nodes not closures. await() returns void! ~90s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Darkwood {!! $flow !!}</p>
            <h2 class="dw-heading-slide">Nodes · packets · driver</h2>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>Flow</h3><p>Transformation node</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>Job</h3><p><code>T1 → T2</code></p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>Ip</h3><p>Instruction packet</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>Driver</h3><p>Runtime scheduler</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-card"><h3>await()</h3><p>Completion barrier · returns void</p></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>08 / flow-model</span></footer>
    </x-slidewire::slide>

    {{-- 10 · FLOW EXECUTION DIAGRAM --}}
    {{-- @notes Multiple IPs pushed, then await. Terminal collector job. Per-node dispatchers under the hood — mention briefly only. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">{!! $flow !!} execution</p>
            <h2 class="dw-heading-slide">Five packets · one pipeline graph</h2>
            <div class="mt-4 dw-embed" style="height:min(360px,44vh)">
                <x-slidewire::diagram>
flowchart LR
  subgraph input ["Submit"]
    I1[Ip task 1]
    I2[Ip task 2]
    I5[Ip task 5]
  end
  F[FetchJob]
  H[HashJob]
  S[SaveJob]
  C[Collector]
  I1 --> F
  I2 --> F
  I5 --> F
  F --> H --> S --> C
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <pre class="mt-3 dw-code" style="font-size:13px;">foreach ($tasks as $task) { $flow(new Ip($task)); }
$flow->await();</pre>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>09 / flow-diagram</span></footer>
    </x-slidewire::slide>

    {{-- 11 · AMP DRIVER --}}
    {{-- @notes Amp\delay suspends coroutine, Revolt schedules timer. ONE OS thread. Concurrent waits ≠ parallel CPU. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">AmpDriver</p>
            <h2 class="dw-heading-slide">Event-loop scheduling · one thread</h2>
            <div class="mt-4 dw-embed" style="height:min(280px,34vh)">
                <x-slidewire::diagram>
sequenceDiagram
    participant T1 as Task1
    participant Loop as RevoltEventLoop
    participant T2 as Task2
    T1->>Loop: Amp delay 0.8s
    T2->>Loop: Amp delay 0.3s
    Note over T1,T2: timers registered
    Loop->>T2: resume 0.3s
    Loop->>T1: resume 0.8s
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <div class="mt-4 dw-grid dw-grid-2" style="gap:10px;">
                    <div class="dw-chip" style="border-color:rgba(184,255,106,.3);">✓ concurrent waits</div>
                    <div class="dw-chip" style="border-color:rgba(255,100,100,.3);">✗ parallel CPU</div>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>10 / amp</span></footer>
    </x-slidewire::slide>

    {{-- 12 · TIMELINE --}}
    {{-- @notes THE numbers slide. Stress: controlled demonstration, NOT benchmark. Sync ≈ sum, Flow ≈ max delay. ~90s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Measurements</p>
            <h2 class="dw-heading-slide">Scheduling behavior — not a benchmark</h2>
            <div class="mt-4 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-warn">
                        <h3>Sync onion</h3>
                        <p style="font-size:32px;margin:0;">~2.42 s</p>
                        <p class="dw-muted" style="margin:8px 0 0;">delays accumulate (Σ 2.4s)</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok">
                        <h3>{!! $flow !!} + Amp</h3>
                        <p style="font-size:32px;margin:0;">~0.81 s</p>
                        <p class="dw-muted" style="margin:8px 0 0;">delays overlap (max 0.8s)</p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <div class="mt-4 dw-embed" style="height:min(200px,26vh)">
                    <x-slidewire::diagram>
gantt
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
    T5f :0, 500
                    </x-slidewire::diagram>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>11 / timing</span></footer>
    </x-slidewire::slide>

    {{-- 13 · PROVES --}}
    {{-- @notes What we can claim. Show interleaved log pattern verbally or on next slide if live demo. ~60s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">What the demo proves</p>
            <h2 class="dw-heading-slide">Evidence, not hype</h2>
            <ul class="dw-list mt-6">
                <x-slidewire::fragment :index="0"><li>Independent timer waits can overlap on one thread</li></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><li>Multiple <code>Ip</code> packets traverse the same stages</li></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><li>Logs interleave — task #2 fetch starts before task #1 fetch ends</li></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><li>Identical hashes and output files (PHPUnit verified)</li></x-slidewire::fragment>
            </ul>
            <x-slidewire::fragment :index="4">
                <pre class="mt-4 dw-code" style="font-size:12px;line-height:1.4;">[FLOW][task #1][fetch] start
[FLOW][task #2][fetch] start
[FLOW][task #5][fetch] start
[FLOW][task #4][fetch] end</pre>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>12 / proves</span></footer>
    </x-slidewire::slide>

    {{-- 14 · DOES NOT PROVE --}}
    {{-- @notes Guardrails slide — essential for credibility. Do not skip. ~60s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">What it does NOT prove</p>
            <h2 class="dw-heading-slide">Stay precise</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:12px;">
                <x-slidewire::fragment :index="0"><div class="dw-card" style="border-color:rgba(255,100,100,.2);"><h3>Not universal speed</h3><p>{!! $flow !!} is not always faster</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card" style="border-color:rgba(255,100,100,.2);"><h3>Not slow Laravel</h3><p>Pipeline is not poorly designed</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card" style="border-color:rgba(255,100,100,.2);"><h3>Not parallel CPU</h3><p>Single OS thread throughout</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card" style="border-color:rgba(255,100,100,.2);"><h3>Not async HTTP</h3><p>Blocking I/O would still block</p></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>13 / limits</span></footer>
    </x-slidewire::slide>

    {{-- 15 · COMPARISON TABLE --}}
    {{-- @notes Side by side — neither wins. Sync demo loops 5 tasks sequentially; Flow submits 5 IPs — fair comparison caveat in notes. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Comparison</p>
            <h2 class="dw-heading-slide">Different problems · different tools</h2>
            <div class="mt-4" style="font-size:clamp(14px,1.6vw,17px);max-width:900px;margin:0 auto;">
                <div class="dw-split" style="grid-template-columns:1fr 1fr;gap:16px;">
                    <x-slidewire::fragment :index="0">
                        <div class="dw-card">
                            <h3>Laravel Pipeline</h3>
                            <ul class="dw-list-compact" style="margin:0;font-size:15px;">
                                <li>Simple onion (~30 LOC core)</li>
                                <li>Sync · one passable</li>
                                <li>Explicit <code>$next</code></li>
                                <li>Returns from <code>then()</code></li>
                                <li>Middleware / commands</li>
                            </ul>
                        </div>
                    </x-slidewire::fragment>
                    <x-slidewire::fragment :index="1">
                        <div class="dw-card">
                            <h3>Darkwood Flow</h3>
                            <ul class="dw-list-compact" style="margin:0;font-size:15px;">
                                <li>Nodes · drivers · dispatchers</li>
                                <li>Async-capable · many <code>Ip</code>s</li>
                                <li><code>await()</code> returns void</li>
                                <li>Terminal collector required</li>
                                <li>Overlapping wait-heavy work</li>
                            </ul>
                        </div>
                    </x-slidewire::fragment>
                </div>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-note mt-4">Sync demo: 5 sequential onions · Flow demo: 5 concurrent packets</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>14 / compare</span></footer>
    </x-slidewire::slide>

    {{-- 16 · FLOW API LESSONS --}}
    {{-- @notes Constructive improvements from Laravel DX. sink(), runAll(), tracing, bundle wiring, FiberDriver hrtime. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What {!! $flow !!} can learn</p>
            <h2 class="dw-heading-slide">Laravel got ergonomics right</h2>
            <div class="mt-4 dw-flow dw-flow--vertical" style="max-width:680px;margin:0 auto;gap:6px;">
                <x-slidewire::fragment :index="0"><div class="dw-pipeline-row"><span class="dw-pipeline-label">await()</span><span>Returns void — easy to forget</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-pipeline-row"><span class="dw-pipeline-label">results</span><span>Terminal collector, not <code>thenReturn()</code></span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-pipeline-row"><span class="dw-pipeline-label">Ip</span><span>Manual wrapping per input</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-pipeline-row"><span class="dw-pipeline-label">bundle</span><span>Symfony services config is empty</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-pipeline-row is-active" style="border-color:rgba(184,255,106,.35);"><span class="dw-pipeline-label">ideas</span><span><code>sink()</code> · <code>runAll()</code> · TraceSubscriber · <code>hrtime()</code> delays</span></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>15 / api-lessons</span></footer>
    </x-slidewire::slide>

    {{-- 17 · CHOOSING --}}
    {{-- @notes Practical decision guide. HTTP middleware → Laravel. Batch import with waits → Flow. They coexist. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Choosing the model</p>
            <h2 class="dw-heading-slide">Match runtime to workload</h2>
            <div class="mt-6 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-ok">
                        <h3>Laravel Pipeline</h3>
                        <ul class="dw-list-compact" style="margin:0;">
                            <li>One passable per call</li>
                            <li>Middleware / command intercept</li>
                            <li>Short-circuit via <code>$next</code></li>
                            <li>Sync, fast steps</li>
                        </ul>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok">
                        <h3>Darkwood Flow</h3>
                        <ul class="dw-list-compact" style="margin:0;">
                            <li>Many independent packets</li>
                            <li>Non-blocking driver waits</li>
                            <li>Per-node error handling</li>
                            <li>Runtime flexibility</li>
                        </ul>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-6">They coexist. Different layers, different models.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>16 / choose</span></footer>
    </x-slidewire::slide>

    {{-- 18 · TAKEAWAY --}}
    {{-- @notes Close strong. Return to opening question. CTA: run demo, read article. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Takeaway</p>
            <h2 class="dw-heading">Both are pipelines.<br>They are not the same machine.</h2>
            <x-slidewire::fragment :index="0">
                <p class="dw-lead mt-6" style="font-size:clamp(1.1rem,2.2vw,1.6rem);">Always ask:</p>
                <p class="dw-heading mt-2" style="font-size:clamp(1.4rem,3vw,2.2rem);"><span class="dw-accent">How does it execute?</span></p>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <div class="mt-8 dw-flow">
                    <div class="dw-node">Onion</div>
                    <div class="dw-arrow">or</div>
                    <div class="dw-node">Dataflow</div>
                    <div class="dw-arrow">—</div>
                    <div class="dw-node">composition ≠ execution</div>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 19 · RESOURCES --}}
    {{-- @notes QR or links. Article, demo repo, darkwood/flow package. ~30s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Resources</p>
            <h2 class="dw-heading-slide">Go deeper</h2>
            <ul class="dw-list mt-8">
                <li><code>darkwood/content/laravel-pipeline</code> — runnable demo</li>
                <li><code>demo:pipeline:sync</code> · <code>demo:pipeline:flow</code> · <code>demo:pipeline:compare</code></li>
                <li><code>darkwood/flow</code> — dataflow component</li>
                <li>Blog article — execution models deep dive</li>
            </ul>
            <x-slidewire::fragment :index="0">
                <p class="dw-note mt-8">Re-run timings on your machine before citing exact numbers in slides.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>17 / resources</span></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
