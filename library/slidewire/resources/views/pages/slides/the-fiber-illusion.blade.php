{{-- The Fiber Illusion — RxJS × Darkwood Flow | /slides/the-fiber-illusion --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $flow = '<span class="dw-accent">Flow</span>';
        $rxjs = '<span class="dw-accent">RxJS</span>';
        $ssd = '<span class="dw-accent">StreamSelectDriver</span>';
    @endphp

    {{-- 1 · TITLE --}}
    {{-- @notes Thesis upfront: Fibers ≠ overlapping I/O. Mention Darkwood Flow, RxJS study, stream_select. Fallback title if needed: “Fibers Aren’t Enough…”. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · PHP · {!! $flow !!} · {!! $rxjs !!} · stream_select</p>
            <h1 class="dw-title">The Fiber<br>Illusion</h1>
            <p class="dw-lead">What {!! $rxjs !!} taught us about<br><span class="dw-accent">real async pipelines</span> in PHP</p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Fibers</div>
                <div class="dw-arrow">≠</div>
                <div class="dw-node">Overlapping I/O</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · INITIAL ASSUMPTION --}}
    {{-- @notes Flow already looked async: Fibers, Drivers, MaxIpStrategy, await(). Ask: what exactly was “async”? Key line: multiple Fibers ≠ overlapping I/O. ~60s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The initial assumption</p>
            <h2 class="dw-heading-slide">{!! $flow !!} already looked asynchronous.</h2>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:12px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">Fibers</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">Drivers</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">MaxIpStrategy</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">await()</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="4">
                <p class="dw-question mt-8">But what exactly was “async”?</p>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="5">
                <p class="dw-takeaway mt-4">Multiple Fibers do not automatically mean overlapping I/O.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>01 / assumption</span></footer>
    </x-slidewire::slide>

    {{-- 3 · WHY STUDY RXJS --}}
    {{-- @notes Not for pipe() syntax. Not to clone Observables. Lifecycle: compose → subscribe → track → complete → teardown. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Why study {!! $rxjs !!}?</p>
            <h2 class="dw-heading-slide">Not for <code>pipe()</code>. Not to clone Observables.</h2>
            <p class="dw-lead" style="font-size:clamp(1.05rem,2vw,1.35rem);">Interesting because of the execution lifecycle.</p>
            <div class="mt-4 dw-embed" style="height:min(320px,40vh)">
                <x-slidewire::diagram>
flowchart LR
  A[subscribe] --> B[values]
  B --> C[track work]
  C --> D[completion]
  D --> E[errors / cancel]
  E --> F[teardown]
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-2">Architectural mirror — not a port.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>02 / why-rxjs</span></footer>
    </x-slidewire::slide>

    {{-- 4 · WHAT RXJS TEACHES --}}
    {{-- @notes Walk the representative pipeline. Emphasize: pipe composes, subscribe starts, mergeMap(4) bounds, completion waits, finalize cleans. Steal guarantees. ~90s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What {!! $rxjs !!} really teaches</p>
            <h2 class="dw-heading-slide">Lifecycle under the operators</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-4 dw-code" style="font-size:15px;">from(urls).pipe(
  mergeMap(fetchImage, 4),
  map(hashBinary),
  retry(2),
  finalize(cleanup),
);</pre>
            </x-slidewire::fragment>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3><code>pipe()</code></h3><p>Composes only</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3><code>subscribe()</code></h3><p>Starts execution</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3><code>mergeMap(..., 4)</code></h3><p>Bounds active work</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-card"><h3><code>finalize()</code></h3><p>Cleanup on terminal paths</p></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>03 / rxjs-pipeline</span></footer>
    </x-slidewire::slide>

    {{-- 5 · FLOW EXISTING MODEL --}}
    {{-- @notes Introduce Flow vocabulary quickly. Ip = packet. Strategy admits. Driver schedules. await() barriers. Collector is app-side (demo ResultCollector). ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">{!! $flow !!} vocabulary</p>
            <h2 class="dw-heading-slide">Packets through stages</h2>
            <div class="mt-4 dw-flow">
                <div class="dw-node">Fetch</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Hash</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Save</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Collector</div>
            </div>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>Ip</h3><p>Instruction packet</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>Job</h3><p>Stage work</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>IpStrategy</h3><p>Admission control</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>MaxIpStrategy</h3><p>Bounded concurrency</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-card"><h3>Driver</h3><p>Schedule &amp; wait</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><div class="dw-card"><h3>await()</h3><p>Completion barrier</p></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>04 / flow-model</span></footer>
    </x-slidewire::slide>

    {{-- 6 · FIBER ILLUSION — CENTRAL --}}
    {{-- @notes CENTRAL SLIDE. Pause. file_get_contents inside Fiber still blocks the process. Contrast three labels. ~90s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The Fiber illusion</p>
            <h2 class="dw-heading-slide">A Fiber can suspend PHP.<br>It cannot unblock a socket.</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-6 dw-code" style="font-size:18px;">file_get_contents($url); // still blocks the process</pre>
            </x-slidewire::fragment>
            <div class="mt-6 dw-split">
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok">
                        <h3>Concurrent PHP</h3>
                        <p style="margin:0;">Many Fibers scheduled</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="2">
                    <div class="dw-split-panel is-ok">
                        <h3>Overlapping I/O</h3>
                        <p style="margin:0;">Non-blocking + <code>stream_select</code></p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <div class="mt-4 dw-split-panel is-warn" style="max-width:720px;margin:0 auto;">
                    <h3>Fake async</h3>
                    <p style="margin:0;">Blocking calls hidden inside Fibers</p>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>05 / fiber-illusion</span></footer>
    </x-slidewire::slide>

    {{-- 7 · MISSING STACK --}}
    {{-- @notes Show the PHP-native async stack. Async is a runtime contract, not a naming convention. Why StreamSelectDriver exists. ~60s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The missing stack</p>
            <h2 class="dw-heading-slide">Async is a runtime contract</h2>
            <div class="mt-4 dw-embed" style="height:min(380px,46vh)">
                <x-slidewire::diagram>
flowchart TB
  S[PHP streams]
  NB[non-blocking mode]
  SEL[stream_select]
  FIB[Fiber suspension]
  RES[readiness resume]
  DRV[Driver]
  FL[Flow]
  S --> NB --> SEL --> FIB --> RES --> DRV --> FL
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-2">Not a naming convention. A yield-on-readiness story.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>06 / stack</span></footer>
    </x-slidewire::slide>

    {{-- 8 · CONCRETE EXPERIMENT --}}
    {{-- @notes Demo: content/rxjs-flow. Real sockets, local fixture server, one deliberate 404, soft DTO errors, traces. No invented speedup numbers. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The concrete experiment</p>
            <h2 class="dw-heading-slide">Real sockets. Real concurrency.</h2>
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
            <x-slidewire::fragment :index="0">
                <pre class="mt-6 dw-code" style="font-size:16px;">php bin/console app:fetch-images --concurrency=4 --timeout=5</pre>
            </x-slidewire::fragment>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="1"><div class="dw-chip">Local fixture server</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">One deliberate 404</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">Per-item success / failure</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-chip"><code>content/rxjs-flow</code></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>07 / experiment</span></footer>
    </x-slidewire::slide>

    {{-- 9 · STREAMSELECTDRIVER --}}
    {{-- @notes Core APIs: waitReadable / waitWritable. Driver schedules; Job owns HTTP; IpStrategy admits; Collector aggregates (demo). Driver is NOT an HTTP client. ~90s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What we shipped</p>
            <h2 class="dw-heading-slide">{!! $ssd !!}</h2>
            <x-slidewire::fragment :index="0">
                <pre class="mt-4 dw-code" style="font-size:16px;">$driver->waitReadable($stream, $timeout);
$driver->waitWritable($stream, $timeout);</pre>
            </x-slidewire::fragment>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>Driver</h3><p>Schedule · wait · resume · deadlines</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>Job</h3><p>HTTP / protocol / application</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>IpStrategy</h3><p>Admission &amp; concurrency</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-card"><h3>Collector</h3><p>Aggregate results</p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="5">
                <p class="dw-question mt-6">The Driver is not an HTTP client.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>08 / stream-select-driver</span></footer>
    </x-slidewire::slide>

    {{-- 10 · CONCURRENCY=4 --}}
    {{-- @notes Key sentence: a concurrency limit is only meaningful if the runtime can say exactly what it limits. Walk layers → invariant. Flow’s mergeMap(4). ~90s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What concurrency=4 means</p>
            <h2 class="dw-heading" style="font-size:clamp(1.5rem,3.2vw,2.4rem);">A concurrency limit is only meaningful<br>if the runtime can say <span class="dw-accent">exactly what it limits</span>.</h2>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:8px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">Queued packets</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">Admitted jobs</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">Active Fibers</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">Open sockets</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-chip">In-flight requests</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="5">
                <pre class="mt-6 dw-code" style="font-size:14px;">MaxIpStrategy(4)
+ non-blocking fetch job
+ StreamSelectDriver
→ ≤ 4 fetch packets admitted
→ ≤ 4 fetch Fibers active
→ ≤ 4 sockets waiting on stream_select</pre>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>09 / concurrency</span></footer>
    </x-slidewire::slide>

    {{-- 11 · ERRORS / COMPLETION / CLEANUP --}}
    {{-- @notes Soft per-item vs fail-fast. await() = no queued, no active, no pending waits. Cleanup on every terminal path = Flow’s finalize discipline. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Errors · completion · cleanup</p>
            <h2 class="dw-heading-slide">Teardown is part of the run</h2>
            <div class="mt-6 dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-ok">
                        <h3>Batch</h3>
                        <p style="margin:0;">Collect per-item errors<br><span class="dw-muted">(demo: soft DTO errors)</span></p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-warn">
                        <h3>Critical</h3>
                        <p style="margin:0;">Fail fast via <code>errorJob</code></p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <ul class="dw-list mt-6" style="font-size:18px;">
                    <li><code>await()</code> → no queued packets, no active jobs, no pending waits</li>
                    <li>Cleanup on success, error, timeout, cancellation</li>
                </ul>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="3">
                <p class="dw-takeaway mt-4">{!! $flow !!}’s version of teardown / <code>finalize</code> discipline.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>10 / teardown</span></footer>
    </x-slidewire::slide>

    {{-- 12 · WHAT FLOW KEEPS --}}
    {{-- @notes Steal the guarantees. Keep list short — lifecycle discipline, not the type system. ~60s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">What {!! $flow !!} keeps from {!! $rxjs !!}</p>
            <h2 class="dw-heading-slide">Steal the guarantees</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>Lifecycle discipline</h3><p>Start → track → complete → tear down</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>Lazy compose / explicit run</h3><p><code>FlowFactory</code> then <code>await()</code></p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>Bounded concurrency</h3><p><code>MaxIpStrategy</code> + yielding jobs</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>Deterministic completion</h3><p>Wait for active work</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-card"><h3>Cleanup</h3><p>Every terminal path</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><div class="dw-card"><h3>Per-run isolation</h3><p>+ observability</p></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>11 / keep</span></footer>
    </x-slidewire::slide>

    {{-- 13 · WHAT FLOW REJECTS --}}
    {{-- @notes Steal guarantees, not the API. Explicit rejects — Observable zoo, Subjects, HTTP-in-Driver, dual engines. ~60s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">What {!! $flow !!} rejects</p>
            <h2 class="dw-heading-slide">Steal the guarantees,<br>not the API</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-split-panel is-warn" style="margin:0;"><h3>Observable / Observer / Subscriber</h3><p style="margin:0;">As public app types</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-split-panel is-warn" style="margin:0;"><h3>Subjects · operator zoo</h3><p style="margin:0;">JS method names in PHP</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-split-panel is-warn" style="margin:0;"><h3>Custom Promise type</h3><p style="margin:0;">Not required for pipelines</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-split-panel is-warn" style="margin:0;"><h3>HTTP inside Drivers</h3><p style="margin:0;">Or a second demo engine</p></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>12 / reject</span></footer>
    </x-slidewire::slide>

    {{-- 14 · WHERE NEXT --}}
    {{-- @notes Honest next steps only — no fake benchmarks. Multi-connection overlap test is next acceptance, not a claimed result. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Where {!! $flow !!} goes next</p>
            <h2 class="dw-heading-slide">Prove the waits further</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>01 · Multi-connection benchmark</h3><p>Next acceptance test — not measured yet</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>02 · Cancellation tokens</h3><p>For StreamSelectDriver waits</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>03 · Richer result API</h3><p>If collectors become painful</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>04 · Production examples</h3><p>Beyond the teaching fetch Job</p></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>13 / next</span></footer>
    </x-slidewire::slide>

    {{-- 15 · CLOSING --}}
    {{-- @notes Close on the article’s final line. Leave silence after the question. CTA: run demo, read article. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Closing</p>
            <h2 class="dw-heading" style="font-size:clamp(1.6rem,3.5vw,2.6rem);">When a job waits on I/O,<br>does the runtime actually wait with it—</h2>
            <x-slidewire::fragment :index="0">
                <h2 class="dw-heading mt-4" style="font-size:clamp(1.6rem,3.5vw,2.6rem);"><span class="dw-accent">or does it only pretend?</span></h2>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <div class="mt-10 dw-flow">
                    <div class="dw-node">Jobs yield</div>
                    <div class="dw-arrow">→</div>
                    <div class="dw-node">Drivers wait</div>
                    <div class="dw-arrow">→</div>
                    <div class="dw-node">I/O overlaps</div>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 16 · RESOURCES --}}
    {{-- @notes Point to article, demo, Flow source, RxJS checkout used for study. ~30s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Resources</p>
            <h2 class="dw-heading-slide">Go deeper</h2>
            <ul class="dw-list mt-8">
                <li>Article — <em>The Fiber Illusion</em></li>
                <li><code>content/rxjs-flow</code> — <code>app:fetch-images</code></li>
                <li><code>darkwood/flow</code> — <code>StreamSelectDriver</code></li>
                <li>RxJS 8.0.0-alpha.14 — lifecycle study source</li>
            </ul>
            <x-slidewire::fragment :index="0">
                <p class="dw-note mt-8">Steal the guarantees. Keep jobs, packets, Drivers.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>14 / resources</span></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
