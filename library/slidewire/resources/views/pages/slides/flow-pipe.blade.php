{{-- Flow Pipe - Token optimization is stream discipline | /slides/flow-pipe --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $flow = '<span class="dw-accent">Flow</span>';
        $pipe = '<span class="dw-accent">|&gt;</span>';
    @endphp

    {{-- 1 · TITLE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · PHP 8.5 · {!! $flow !!} · Symfony</p>
            <h1 class="dw-title">Flow Tokens</h1>
            <p class="dw-lead">Token optimization is<br><span class="dw-accent">stream discipline</span></p>
            <p class="dw-note">Symfony Console demo · <code>flow-pipe</code></p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Noisy context</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Pipeline</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Budgeted signal</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · THE PROBLEM --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The problem</p>
            <h2 class="dw-heading-slide">Agentic workflows consume too many tokens.</h2>
            <div class="mt-8 dw-grid dw-grid-3">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>Engine logs</h3><p>Verbose, repetitive, ANSI-colored.</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>Tool outputs</h3><p>Stack traces, debug lines, boilerplate.</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>RAG dumps</h3><p>Useful signal buried in noise.</p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <p class="dw-question mt-8">Context floods the agent - before any reasoning starts.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>01 / problem</span></footer>
    </x-slidewire::slide>

    {{-- 3 · NOT WORD SHORTENING --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Wrong instinct</p>
            <h2 class="dw-heading-slide">The issue is not word shortening.</h2>
            <div class="dw-split mt-8">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-warn">
                        <h3>Abbreviate vocabulary</h3>
                        <p style="font-size:20px;margin:0;color:var(--dw-text);">waterfall → wf<br>pipeline → pln<br>stream → strm</p>
                        <p class="dw-muted mt-4" style="margin-bottom:0;">Destroys readability. Saves almost nothing.</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok">
                        <h3>Stream discipline</h3>
                        <ul class="dw-list-compact" style="margin:0;">
                            <li>Strip noise</li>
                            <li>Chunk bounded segments</li>
                            <li>Compress duplicates</li>
                            <li>Apply a token budget</li>
                        </ul>
                    </div>
                </x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>02 / not-shortening</span></footer>
    </x-slidewire::slide>

    {{-- 4 · REAL ISSUE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Root cause</p>
            <h2 class="dw-heading-slide">Uncontrolled context.</h2>
            <div class="mt-6 dw-embed" style="height:min(320px,40vh)">
                <x-slidewire::diagram>
flowchart TB
  LOG[Engine logs]
  TOOL[Tool outputs]
  TRACE[Repeated traces]
  CHUNK[Noisy chunks]
  CTX[Agent context window]
  LOG --> CTX
  TOOL --> CTX
  TRACE --> CTX
  CHUNK --> CTX
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-4">Data enters as one giant string - not as a disciplined stream.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>03 / root-cause</span></footer>
    </x-slidewire::slide>

    {{-- 5 · THESIS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Thesis</p>
            <h2 class="dw-heading" style="font-size:clamp(2rem,4.5vw,3.2rem);">Token optimization is not word shortening.</h2>
            <x-slidewire::fragment :index="0">
                <h2 class="dw-heading mt-6" style="font-size:clamp(2rem,4.5vw,3.2rem);">Token optimization is <span class="dw-accent">stream discipline</span>.</h2>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-note mt-8">How data moves - not how words are spelled.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>04 / thesis</span></footer>
    </x-slidewire::slide>

    {{-- 6 · FLOW ANGLE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The {!! $flow !!} angle</p>
            <h2 class="dw-heading-slide">{!! $flow !!} is not only about running tasks.</h2>
            <p class="dw-lead" style="font-size:clamp(1.1rem,2vw,1.5rem);">It controls how data moves:</p>
            <div class="mt-6 dw-grid dw-grid-3" style="grid-template-columns:repeat(3,1fr);gap:12px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">as a pipe</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">as a stream</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">as chunks</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">as measurable context</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-chip">as budgeted context</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><div class="dw-chip">for humans · machines · agents</div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>05 / flow-angle</span></footer>
    </x-slidewire::slide>

    {{-- 7 · DEMO INTRO --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The demo</p>
            <h2 class="dw-heading-slide"><code>flow-pipe</code></h2>
            <p class="dw-lead">A Symfony Console project that runs a token pipeline through {!! $flow !!}.</p>
            <x-slidewire::fragment :index="0">
                <pre class="mt-6 dw-code" style="font-size:17px;">php bin/console app:flow-token-demo \
  --input=flow-engine-log --show-chunks</pre>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-note mt-4">Deterministic. Local. No external API.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>06 / demo</span></footer>
    </x-slidewire::slide>

    {{-- 8 · ARCHITECTURE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Architecture</p>
            <h2 class="dw-heading-slide">Three layers. One pipeline.</h2>
            <div class="mt-4 dw-embed" style="height:min(400px,48vh)">
                <x-slidewire::diagram>
flowchart TB
  CMD[FlowTokenDemoCommand\napp:flow-token-demo]
  SVC[TokenPipelineService]
  RUN[TokenPipelineFlowRunner]
  PAR[PipelineExpressionParser]
  REG[PipelineStepRegistry]
  CTX[PipelineContext]
  FIX[FlowFixtureProvider]
  FF[FlowFactory + FiberDriver]
  CMD --> SVC
  SVC --> PAR
  SVC --> RUN
  PAR --> REG
  RUN --> FF
  RUN --> CTX
  FIX --> CTX
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>07 / architecture</span></footer>
    </x-slidewire::slide>

    {{-- 9 · PROJECT STRUCTURE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Project structure</p>
            <h2 class="dw-heading-slide">Application · Infrastructure · Domain</h2>
            <div class="mt-4 dw-split" style="grid-template-columns:1fr 1fr 1fr;">
                <x-slidewire::fragment :index="0">
                    <div class="dw-card">
                        <h3>Command</h3>
                        <p><code>FlowTokenDemoCommand</code></p>
                        <p class="dw-muted" style="font-size:16px;">CLI I/O only</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-card">
                        <h3>Application</h3>
                        <p><code>TokenPipelineService</code></p>
                        <p class="dw-muted" style="font-size:16px;">Parse + run facade</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="2">
                    <div class="dw-card">
                        <h3>Infrastructure</h3>
                        <p><code>TokenPipelineFlowRunner</code></p>
                        <p class="dw-muted" style="font-size:16px;">Full pipeline via FlowFactory</p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <div class="mt-4 dw-card">
                    <h3>TokenFlow domain</h3>
                    <div class="mt-3 dw-flow" style="flex-wrap:wrap;justify-content:center;gap:8px;">
                        <div class="dw-chip">PipelineExpressionParser</div>
                        <div class="dw-chip">PipelineStepRegistry</div>
                        <div class="dw-chip">PipelineContext</div>
                        <div class="dw-chip">FlowFixtureProvider</div>
                        <div class="dw-chip">Step classes</div>
                    </div>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>08 / structure</span></footer>
    </x-slidewire::slide>

    {{-- 10 · PIPELINE STEPS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Pipeline steps</p>
            <h2 class="dw-heading-slide">Registered operations - not hardcoded branches.</h2>
            <div class="mt-4 dw-flow dw-flow--vertical" style="max-width:720px;margin:0 auto;gap:6px;">
                <x-slidewire::fragment :index="0"><div class="dw-pipeline-row is-active"><span class="dw-pipeline-label">source</span><span>Load fixture into stream</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-pipeline-row"><span class="dw-pipeline-label">strip_ansi</span><span>Remove escape sequences</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-pipeline-row"><span class="dw-pipeline-label">remove_noise</span><span>Drop debug / trace lines</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-pipeline-row"><span class="dw-pipeline-label">normalize_whitespace</span><span>Collapse blank runs</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-pipeline-row"><span class="dw-pipeline-label">chunk:300</span><span>Split into ~300-char segments</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><div class="dw-pipeline-row"><span class="dw-pipeline-label">compress</span><span>Deduplicate &amp; collapse repeats</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="6"><div class="dw-pipeline-row"><span class="dw-pipeline-label">budget:1000</span><span>Keep chunks until token limit</span></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="7"><div class="dw-pipeline-row is-active" style="border-color:rgba(184,255,106,.35);"><span class="dw-pipeline-label">sink</span><span>Flush output &amp; metrics</span></div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>09 / steps</span></footer>
    </x-slidewire::slide>

    {{-- 11 · PIPELINE EXPRESSION --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Pipeline expression</p>
            <h2 class="dw-heading-slide">Declarative. Shareable. Readable.</h2>
            <pre class="mt-6 dw-code" style="font-size:15px;line-height:1.5;">source |> strip_ansi |> remove_noise |> normalize_whitespace
  |> chunk:300 |> compress |> budget:1000 |> sink</pre>
            <x-slidewire::fragment :index="0">
                <p class="dw-note mt-6"><code>PipelineExpressionParser</code> splits on {!! $pipe !!} and resolves names through <code>PipelineStepRegistry</code>.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>10 / expression</span></footer>
    </x-slidewire::slide>

    {{-- 12 · THREE PIPE MEANINGS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Three {!! $pipe !!} semantics</p>
            <h2 class="dw-heading-slide">Same symbol. Three layers.</h2>
            <div class="mt-6 dw-grid dw-grid-3">
                <x-slidewire::fragment :index="0">
                    <div class="dw-card">
                        <h3>Expression DSL</h3>
                        <p>String pipeline language</p>
                        <pre class="dw-code" style="font-size:14px;margin-top:12px;padding:12px;">"source |> sink"</pre>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-card">
                        <h3>PHP 8.5 {!! $pipe !!}</h3>
                        <p>Step → ClosureJob mapping</p>
                        <pre class="dw-code" style="font-size:13px;margin-top:12px;padding:12px;">$step |> (fn ($s) => ...)</pre>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="2">
                    <div class="dw-card">
                        <h3>{!! $flow !!} fn()</h3>
                        <p>Runtime composition</p>
                        <pre class="dw-code" style="font-size:13px;margin-top:12px;padding:12px;">$flow->fn($job)</pre>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <p class="dw-takeaway mt-6">Visual rhythm of the DSL - at Flow composition time.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>11 / three-pipes</span></footer>
    </x-slidewire::slide>

    {{-- 13 · FLOW RUNTIME --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Flow runtime</p>
            <h2 class="dw-heading-slide">Full pipeline. One graph.</h2>
            <pre class="mt-4 dw-code" style="font-size:15px;">$toJob = fn ($s) => new ClosureJob(fn ($ctx) => $s->apply($ctx));

$flow = (new FlowFactory())->createFlow(
    $firstStep |> $toJob,
    ['driver' => $driver],
);

foreach ($remaining as $step) {
    $flow = $flow->fn($step |> $toJob);
}

$flow(new Ip($context));
$flow->await();</pre>
            <x-slidewire::fragment :index="0">
                <p class="dw-note mt-4"><code>TokenPipelineFlowRunner</code> - each step becomes a <code>ClosureJob</code> on <code>PipelineContext</code>.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>12 / runtime</span></footer>
    </x-slidewire::slide>

    {{-- 14 · RESULT - STAGES --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The result</p>
            <h2 class="dw-heading-slide">From flood to signal.</h2>
            <div class="mt-4 dw-embed" style="height:min(300px,38vh)">
                <x-slidewire::diagram>
flowchart LR
  N[Noisy input\n17k chars]
  C[Cleaned stream\nstrip + normalize]
  K[Chunked data\n~300 char segments]
  Z[Compressed context\ndeduped lines]
  B[Budgeted output\n≤1000 tokens]
  N --> C --> K --> Z --> B
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-note mt-4">Fixture: <code>flow-engine-log</code> · heuristic ~1 token / 4 chars</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>13 / stages</span></footer>
    </x-slidewire::slide>

    {{-- 15 · RESULT - NUMBERS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Before / after</p>
            <h2 class="dw-heading-slide"><code>flow-engine-log</code></h2>
            <div class="dw-split mt-8">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-warn">
                        <h3>Before</h3>
                        <p style="font-size:28px;margin:0;color:var(--dw-text);">4,488 <span style="font-size:18px;">est. tokens</span></p>
                        <p class="dw-muted" style="margin:8px 0 0;">17,398 characters</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok">
                        <h3>After</h3>
                        <p style="font-size:28px;margin:0;color:var(--dw-text);">88 <span style="font-size:18px;">est. tokens</span></p>
                        <p class="dw-muted" style="margin:8px 0 0;">334 characters · <strong>98% reduction</strong></p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-6">Domain vocabulary intact. Noise and repetition removed.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>14 / numbers</span></footer>
    </x-slidewire::slide>

    {{-- 16 · LEXICON CONTRAST --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Signal vs noise</p>
            <h2 class="dw-heading-slide"><code>flow-lexicon</code> resists compression.</h2>
            <div class="dw-split mt-8">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel">
                        <h3>Before</h3>
                        <p style="font-size:24px;margin:0;">371 est. tokens</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel">
                        <h3>After</h3>
                        <p style="font-size:24px;margin:0;">354 est. tokens · 4.6%</p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway mt-6">Semantic content carries signal - abbreviating words is not the strategy.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>15 / contrast</span></footer>
    </x-slidewire::slide>

    {{-- 17 · FUTURE ASYNC --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Tomorrow</p>
            <h2 class="dw-heading-slide">From CLI demo to async streams.</h2>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">Non-blocking streams</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">stream_select</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">Fibers</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">Process pipes</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-chip">PTY / TTY modes</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><div class="dw-chip">Event loop drivers</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="6"><div class="dw-chip">Async workers</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="7"><div class="dw-chip">{!! $flow !!} orchestration layer</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="8">
                <p class="dw-note mt-6">Read process stdout as it arrives - not after <code>wait()</code>.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>16 / future</span></footer>
    </x-slidewire::slide>

    {{-- 18 · TAKEAWAY --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Takeaway</p>
            <h2 class="dw-heading">Control the stream.<br>Not the spelling.</h2>
            <x-slidewire::fragment :index="0">
                <div class="mt-8 dw-flow">
                    <div class="dw-node">Expression</div>
                    <div class="dw-arrow">{!! $pipe !!}</div>
                    <div class="dw-node">{!! $flow !!}</div>
                    <div class="dw-arrow">{!! $pipe !!}</div>
                    <div class="dw-node">Budgeted context</div>
                </div>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-note mt-8">Token optimization is stream discipline.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>17 / takeaway</span></footer>
    </x-slidewire::slide>

    {{-- 19 · RESOURCES --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Resources</p>
            <h2 class="dw-heading-slide">Go deeper.</h2>
            <ul class="dw-list mt-8">
                <li><code>darkwood/content/flow-pipe</code> - Symfony demo repo</li>
                <li><code>app:flow-token-demo</code> - run the pipeline locally</li>
                <li><code>darkwood/flow</code> - orchestration package</li>
                <li>PHP.Watch - PHP 8.5 pipe operator</li>
            </ul>
            <x-slidewire::fragment :index="0">
                <p class="dw-note mt-8">Inspired by a LinkedIn post on agentic context - cited at the end of the article.</p>
            </x-slidewire::fragment>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Noisy logs</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Stream pipeline</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Agent-ready context</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
