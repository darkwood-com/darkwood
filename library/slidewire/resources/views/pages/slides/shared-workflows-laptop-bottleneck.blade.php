{{-- The Laptop Is the Last Bottleneck — Framework X × Darkwood Flow | /slides/shared-workflows-laptop-bottleneck --}}
{{-- Source: tasks/#1357fb_shared-workflows/article.md --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $fx = '<span class="dw-accent">Framework X</span>';
        $flow = '<span class="dw-accent">Darkwood Flow</span>';
    @endphp

    {{-- 1 · TITLE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · PHP · agents · workflows</p>
            <h1 class="dw-title">The Laptop Is<br>the Last Bottleneck</h1>
            <p class="dw-lead">From coding agents to<br><span class="dw-accent">shared, observable workflows</span></p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Agent</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Workflow</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Shared runtime</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · ONE AGENT WAS FINE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The original pain</p>
            <h2 class="dw-heading-slide">One coding agent was manageable.</h2>
            <div class="mt-8 dw-flow">
                <div class="dw-node">Ask</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Diff</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Tests you remember</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Ship</div>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-10">The invisible checklist still fits in one head.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>01 / one agent</span></footer>
    </x-slidewire::slide>

    {{-- 3 · FIVE AGENTS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The breaking point</p>
            <h2 class="dw-heading-slide">Several agents. One human checklist.</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:12px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">API agent</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">Docs agent</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">Security agent</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">You = scheduler</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="4">
                <p class="dw-question mt-8">Did this run include static analysis?<br>What did yesterday’s failure teach?</p>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="5">
                <p class="dw-takeaway mt-4">Agents need not be incapable — the process is still personal.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>02 / human orchestrator</span></footer>
    </x-slidewire::slide>

    {{-- 4 · LAPTOP BOTTLENECK --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Not a hardware complaint</p>
            <h2 class="dw-heading-slide">The laptop is a silo.</h2>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>Identity</h3><p>Credentials · access</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>Memory</h3><p>Prompts · history · lessons</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>Conventions</h3><p>Scripts · skills · habits</p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <div class="mt-8 dw-flow">
                    <div class="dw-node">Runtime</div>
                    <div class="dw-arrow">+</div>
                    <div class="dw-node">Knowledge silo</div>
                </div>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="4">
                <p class="dw-takeaway mt-6">When the laptop leaves, the process leaves.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>03 / laptop</span></footer>
    </x-slidewire::slide>

    {{-- 5 · HIDDEN → EXPLICIT --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The missing layer</p>
            <h2 class="dw-heading-slide">Not a smarter agent — an explicit workflow.</h2>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:16px;">
                <div class="dw-card">
                    <h3>Implicit</h3>
                    <pre class="dw-code" style="font-size:13px;margin-top:8px;">habit in one head
“I always run tests…”</pre>
                </div>
                <div class="dw-card">
                    <h3>Explicit</h3>
                    <div class="mt-3 dw-flow" style="flex-wrap:wrap;">
                        <div class="dw-node" style="font-size:12px;">validate</div>
                        <div class="dw-arrow">↓</div>
                        <div class="dw-node" style="font-size:12px;">tests</div>
                        <div class="dw-arrow">↓</div>
                        <div class="dw-node" style="font-size:12px;">security</div>
                        <div class="dw-arrow">↓</div>
                        <div class="dw-node" style="font-size:12px;">review</div>
                    </div>
                </div>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-6">Named steps · inputs/outputs · failure modes · trace</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>04 / explicit workflow</span></footer>
    </x-slidewire::slide>

    {{-- 6 · FRAMEWORK X --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Runtime layer</p>
            <h2 class="dw-heading-slide">{!! $fx !!}: async HTTP</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:12px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>HttpServerRunner</h3><p>Long-lived ReactPHP CLI</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>SapiRunner</h3><p>Same app behind FPM</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>Handlers</h3><p>Response · Promise · Generator · Fiber</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-card"><h3>Exposes</h3><p>Workflows over HTTP</p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="4">
                <p class="dw-takeaway mt-6">Not a scheduler · queue · sandbox · agent platform</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>05 / framework-x</span></footer>
    </x-slidewire::slide>

    {{-- 7 · FLOW --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Orchestration layer</p>
            <h2 class="dw-heading-slide">{!! $flow !!}: jobs over Ips</h2>
            <div class="mt-4 dw-flow">
                <div class="dw-node">Job</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Ip</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Driver</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">await()</div>
            </div>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-card"><h3>FlowFactory</h3><p>Define the chain</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-card"><h3>MaxIpStrategy</h3><p>Ip-level concurrency</p></div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-card"><h3>errorJob</h3><p>Failure hooks</p></div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="3">
                <p class="dw-takeaway mt-6">{!! $fx !!} receives the request. {!! $flow !!} coordinates the work.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>06 / flow</span></footer>
    </x-slidewire::slide>

    {{-- 8 · PROTOTYPE ARCHITECTURE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">framework-x-flow prototype</p>
            <h2 class="dw-heading-slide">Request → workflow → trace</h2>
            <div class="mt-4 dw-embed" style="height:min(340px,48vh)">
                <x-slidewire::diagram>
flowchart TD
  A[HTTP request] --> B[Framework X route]
  B --> C[Flow workflow]
  C --> D[Steps]
  D --> E[Trace + retrospective]
  E --> F[JSON + var/runs]
                </x-slidewire::diagram>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-lead mt-2" style="font-size:1rem;">validate → analyse → quality → docs → review → retrospective</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>07 / prototype</span></footer>
    </x-slidewire::slide>

    {{-- 9 · SEQUENTIAL VS CONCURRENT --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Async experiment</p>
            <h2 class="dw-heading-slide">Sequential vs concurrent Ips</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:14px;">
                <div class="dw-card">
                    <h3>MaxIpStrategy(1)</h3>
                    <p>One in flight → serial delays</p>
                    <p class="dw-accent mt-2" style="font-size:1.4rem;">~3× delay budget</p>
                </div>
                <div class="dw-card">
                    <h3>MaxIpStrategy(N)</h3>
                    <p>N in flight → overlapping delays</p>
                    <p class="dw-accent mt-2" style="font-size:1.4rem;">~1× delay budget</p>
                </div>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-6">Local timings only — illustrative, not a benchmark.<br>Blocking PHP still stalls the loop.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>08 / concurrency</span></footer>
    </x-slidewire::slide>

    {{-- 10 · RETROSPECTIVE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Learning without mutation</p>
            <h2 class="dw-heading-slide">Propose ≠ rewrite</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:14px;">
                <x-slidewire::fragment :index="0">
                    <div class="dw-card">
                        <h3>Retrospective outputs</h3>
                        <p>slow steps · failures · lessons · suggestions</p>
                    </div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-card">
                        <h3>Does not</h3>
                        <p>silently edit workflow code</p>
                    </div>
                </x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="2">
                <pre class="mt-6 dw-code" style="font-size:14px;">{
  "proposal": "Add a dependency audit step before final review."
}</pre>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="3">
                <p class="dw-takeaway mt-4">Reviewable self-improvement beats uncontrolled self-modification.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>09 / retrospective</span></footer>
    </x-slidewire::slide>

    {{-- 11 · DOES NOT PROVE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Honesty slide</p>
            <h2 class="dw-heading-slide">What this prototype does not prove</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:10px;">
                <x-slidewire::fragment :index="0"><div class="dw-chip">No secure cloud sandbox</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="1"><div class="dw-chip">No distributed scheduler</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="2"><div class="dw-chip">No credential broker</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="3"><div class="dw-chip">No multi-tenant isolation</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="4"><div class="dw-chip">No autonomous self-rewrite</div></x-slidewire::fragment>
                <x-slidewire::fragment :index="5"><div class="dw-chip">Local ≠ team workflow</div></x-slidewire::fragment>
            </div>
            <x-slidewire::fragment :index="6">
                <p class="dw-takeaway mt-8">Cloud agent execution is a separate infrastructure problem.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>10 / limits</span></footer>
    </x-slidewire::slide>

    {{-- 12 · CONCLUSION --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Durable layer</p>
            <h2 class="dw-heading-slide">The workflow remains.</h2>
            <div class="mt-8 dw-flow">
                <div class="dw-node">Model</div>
                <div class="dw-arrow">changes</div>
                <div class="dw-node">Agent</div>
                <div class="dw-arrow">changes</div>
                <div class="dw-node dw-accent" style="border-color:var(--dw-lime);">Workflow</div>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-lead mt-10">The useful artifact is no longer the private prompt.<br><span class="dw-accent">It is the process you can run, inspect, and improve.</span></p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
