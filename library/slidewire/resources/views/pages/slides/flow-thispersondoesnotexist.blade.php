{{-- Flow · ThisPersonDoesNotExist — YouTube deck (8–12 min) | /slides/flow-thispersondoesnotexist --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $flow = '<span class="dw-accent">Flow</span>';
        $ip = '<span class="dw-accent">Ip</span>';
    @endphp

    {{-- 1 · TITLE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · PHP · Orchestration</p>
            <h1 class="dw-title">😶‍🌫️ This Person Does Not Exist</h1>
            <p class="dw-lead">From Synchronous PHP<br>to Asynchronous Orchestration with {!! $flow !!}</p>
            <p class="dw-note">A minimal Symfony experiment</p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">HTTP request</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Image bytes</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">File saved</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · THE PROBLEM --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The problem</p>
            <h2 class="dw-heading-slide">Download 10 images. Respect a rate limit. Save each file.</h2>
            <div class="mt-8 dw-grid dw-grid-5" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">Image 1</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">Image 2</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">Image 3</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">…</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-chip">Image 10</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="5">
                <p class="dw-note mt-8">Same pipeline for every image. No dependency between them.</p>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="6">
                <p class="dw-question">Why are we waiting?</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>01 / problem</span></footer>
    </x-slidewire::slide>

    {{-- 3 · SYNCHRONOUS LOOP --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The synchronous loop</p>
            <h2 class="dw-heading-slide">One image at a time. The process blocks on every step.</h2>
            <div class="mt-6" style="display:grid;gap:14px;">
                <x-slidewire::fragment :index="0">
                    <div class="dw-pipeline-row is-active">
                        <span class="dw-pipeline-label">Image 1</span>
                        <span>Fetch</span><span class="dw-arrow">→</span>
                        <span>Save</span><span class="dw-arrow">→</span>
                        <span class="dw-pipeline-step">Sleep</span>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-pipeline-row is-muted">
                        <span class="dw-pipeline-label">Image 2</span>
                        <span class="dw-pipeline-step">waits…</span>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="2">
                    <div class="dw-pipeline-row is-muted">
                        <span class="dw-pipeline-label">Image 3</span>
                        <span class="dw-pipeline-step">waits…</span>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <p class="dw-takeaway" style="border-color:rgba(255,120,100,.35);">Everything waits.</p>
            </x-slidewire::fragment>
            <pre class="mt-6 dw-code" style="font-size:17px;">for (...) { fetch(); save(); sleep(); }</pre>
        </section>
        <footer class="dw-footer"><span>02 / sync-loop</span></footer>
    </x-slidewire::slide>

    {{-- 4 · WHAT IS ACTUALLY HAPPENING --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">What is actually happening?</p>
            <h2 class="dw-heading-slide">A hidden pipeline, repeated N times.</h2>
            <div class="dw-flow dw-flow--vertical" style="max-width:520px;">
                <x-slidewire::fragment :index="0"><div class="dw-node">Fetch</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-arrow">↓</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-node">Save</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-arrow">↓</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-node">Report</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="5">
                <p class="dw-takeaway">Each image is an <strong>independent work unit</strong>.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>03 / pipeline</span></footer>
    </x-slidewire::slide>

    {{-- 5 · HIDDEN OPPORTUNITY --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The hidden opportunity</p>
            <h2 class="dw-heading-slide">Independent units. Same pipeline.</h2>
            <div class="mt-6 dw-embed" style="height:min(340px,42vh)">
                <x-slidewire::diagram>
flowchart LR
  I1[Image 1]
  I2[Image 2]
  I3[Image 3]
  P[Pipeline\nFetch → Save → Report]
  I1 --> P
  I2 --> P
  I3 --> P
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-question" style="margin-top:24px;">Why not run them together?</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>04 / opportunity</span></footer>
    </x-slidewire::slide>

    {{-- 6 · INTRODUCING FLOW --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Introducing {!! $flow !!}</p>
            <h2 class="dw-heading-slide">Work units enter an orchestrator.</h2>
            <div class="dw-flow dw-flow--vertical" style="max-width:640px;margin-left:auto;margin-right:auto;">
                <x-slidewire::fragment :index="0">
                    <div class="dw-grid dw-grid-3" style="gap:10px;">
                        <div class="dw-chip">{!! $ip !!}(1)</div>
                        <div class="dw-chip">{!! $ip !!}(2)</div>
                        <div class="dw-chip">{!! $ip !!}(3)</div>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-arrow">↓</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-node" style="border-color:rgba(89,215,255,.5);">Flow</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-arrow">↓</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-node">Pipeline</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="5">
                <p class="dw-note mt-6" style="text-align:center;">An {!! $ip !!} represents one work unit — a payload traveling through the pipeline.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>05 / flow</span></footer>
    </x-slidewire::slide>

    {{-- 7 · THE PIPELINE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The pipeline</p>
            <h2 class="dw-heading-slide">Describe steps once. Every {!! $ip !!} follows the same path.</h2>
            <div class="dw-flow dw-flow--vertical" style="max-width:480px;">
                <x-slidewire::fragment :index="0"><div class="dw-node">{!! $ip !!}</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-arrow">↓</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-node">Fetch Image</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-arrow">↓</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-node">Save Image</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><div class="dw-arrow">↓</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="6"><div class="dw-node">Report</div></x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>06 / pipeline-steps</span></footer>
    </x-slidewire::slide>

    {{-- 8 · THE MIGRATION --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The migration</p>
            <h2 class="dw-heading-slide">Suspend the work unit — not the whole process.</h2>
            <div class="dw-split">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-warn">
                        <h3>Before · blocking</h3>
                        <pre class="dw-code" style="margin:0;padding:18px;font-size:22px;">sleep(1);</pre>
                        <p class="dw-muted" style="font-size:18px;margin:0;">Entire process frozen.</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok">
                        <h3>After · cooperative</h3>
                        <pre class="dw-code" style="margin:0;padding:18px;font-size:20px;">$driver->delay($delay);</pre>
                        <p class="dw-muted" style="font-size:18px;margin:0;">Only this fiber suspends.</p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <p class="dw-takeaway">Rate limiting per unit. Progress for everyone else.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>07 / migration</span></footer>
    </x-slidewire::slide>

    {{-- 9 · FIBERS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Fibers · PHP 8.1</p>
            <h2 class="dw-heading-slide">Waiting becomes productive.</h2>
            <div class="dw-fiber-lane">
                <x-slidewire::fragment :index="0">
                    <div class="dw-fiber-row">
                        <strong>Fiber A</strong>
                        <span>delay — suspended</span>
                        <em>waiting</em>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-fiber-row">
                        <strong>Fiber B</strong>
                        <span>downloading image</span>
                        <em>working</em>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="2">
                    <div class="dw-fiber-row">
                        <strong>Fiber C</strong>
                        <span>saving to disk</span>
                        <em>working</em>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="3">
                    <div class="dw-fiber-row" style="border-color:rgba(184,255,106,.35);">
                        <strong>Fiber A</strong>
                        <span>resumed — fetch starts</span>
                        <em>resume</em>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="4">
                <p class="dw-note mt-6"><code>FiberDriver</code> delegates execution. {!! $flow !!} orchestrates the pipeline.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>08 / fibers</span></footer>
    </x-slidewire::slide>

    {{-- 10 · NEW EXECUTION MODEL --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The new execution model</p>
            <h2 class="dw-heading-slide">Enqueue work. Await completion.</h2>
            <pre class="mt-4 dw-code" style="font-size:18px;">for (...) {
    $flow(new Ip(...));
}
$flow->await();</pre>
            <div class="mt-6 dw-embed" style="height:min(280px,36vh)">
                <x-slidewire::diagram>
flowchart TB
  E1[enqueue Ip]
  E2[enqueue Ip]
  E3[enqueue Ip]
  S[Flow scheduler]
  P[pipeline execution]
  A[await]
  E1 --> S
  E2 --> S
  E3 --> S
  S --> P
  P --> A
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-note mt-4">The <code>for</code> loop produces work. {!! $flow !!} schedules it.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>09 / execution</span></footer>
    </x-slidewire::slide>

    {{-- 11 · WHAT FLOW IS NOT --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Clarification</p>
            <h2 class="dw-heading-slide">What {!! $flow !!} is — and is not.</h2>
            <div class="dw-vs-list">
                <x-slidewire::fragment :index="0"><div class="dw-vs-item is-no"><span>✕</span> Event Loop</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-vs-item is-no"><span>✕</span> Fiber implementation</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-vs-item is-no"><span>✕</span> Runtime</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-vs-item is-no"><span>✕</span> ReactPHP / Amp replacement</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4">
                    <div class="dw-vs-item is-yes"><span>✓</span> Orchestration model</div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="5">
                <div class="mt-6 dw-grid dw-grid-4" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;">
                    <div class="dw-chip">FiberDriver</div>
                    <div class="dw-chip">AmpDriver</div>
                    <div class="dw-chip">ReactDriver</div>
                    <div class="dw-chip">SwooleDriver</div>
                </div>
                <p class="dw-note mt-4">Describe the pipeline once. Choose the driver for execution.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>10 / what-flow-is</span></footer>
    </x-slidewire::slide>

    {{-- 12 · FUTURE APPLICATIONS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Beyond the POC</p>
            <h2 class="dw-heading-slide">Same orchestration model. Different domains.</h2>
            <div class="mt-6 dw-grid dw-grid-3">
                <x-slidewire::fragment :index="0">
                    <div class="dw-card">
                        <h3>YouTube</h3>
                        <div class="dw-flow dw-flow--vertical" style="margin-top:14px;gap:8px;">
                            <div class="dw-chip" style="border-radius:8px;">Scrape</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px;">Transcript</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px;">Publish</div>
                        </div>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-card">
                        <h3>Video</h3>
                        <div class="dw-flow dw-flow--vertical" style="margin-top:14px;gap:8px;">
                            <div class="dw-chip" style="border-radius:8px;">Scene gen</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px;">Encode</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px;">Persist</div>
                        </div>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="2">
                    <div class="dw-card">
                        <h3>AI Agents</h3>
                        <div class="dw-flow dw-flow--vertical" style="margin-top:14px;gap:8px;">
                            <div class="dw-chip" style="border-radius:8px;">Read</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px;">Transform</div>
                            <div class="dw-arrow">↓</div>
                            <div class="dw-chip" style="border-radius:8px;">Act</div>
                        </div>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <p class="dw-takeaway" style="margin-top:20px;">Independent units. Composable steps. One orchestrator.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>11 / applications</span></footer>
    </x-slidewire::slide>

    {{-- 13 · FINAL TAKEAWAY --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Takeaway</p>
            <h2 class="dw-heading-slide">Not about performance.<br>About orchestration.</h2>
            <div class="dw-split" style="margin-top:28px;">
                <x-slidewire::fragment :index="0">
                    <div class="dw-split-panel is-warn">
                        <h3>Before</h3>
                        <p style="font-size:22px;line-height:1.4;color:var(--dw-text);margin:0;">Execute task A<br>then B<br>then C</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-split-panel is-ok">
                        <h3>After</h3>
                        <ul class="dw-list-compact" style="margin:0;">
                            <li>Describe work units</li>
                            <li>Describe a pipeline</li>
                            <li>Let the orchestrator schedule</li>
                        </ul>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <div class="mt-8 dw-flow" style="margin-top:32px;">
                    <div class="dw-node">{!! $ip !!}</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-node">{!! $flow !!}</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-node">Fetch</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-node">Save</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-node">await()</div>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>12 / takeaway</span></footer>
    </x-slidewire::slide>

    {{-- 14 · RESOURCES --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Resources</p>
            <h2 class="dw-heading-slide">Go deeper.</h2>
            <ul class="dw-list mt-8">
                <li><code>flow-thispersondoesnotexist</code> — POC repo · commits <code>fae47e8</code> → <code>c61c929</code></li>
                <li><code>darkwood/flow</code> — orchestration package · <code>examples/flow.php</code></li>
                <li>Frédéric Bouchery — <em>The Evolution of Async PHP</em></li>
            </ul>
            <a class="dw-demo-url" href="https://f2r.github.io/fr/asynchrone.html" target="_blank" rel="noopener">f2r.github.io/fr/asynchrone.html</a>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Sync loop</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">{!! $flow !!} pipeline</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Darkwood scale</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
