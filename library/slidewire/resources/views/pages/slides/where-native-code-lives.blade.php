{{-- Where Should Native Code Live? - Phalcon, Symfony, TrueAsync, Flow | /slides/where-native-code-lives --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $flow = '<span class="dw-accent">Flow</span>';
        $ta = '<span class="dw-accent">TrueAsync</span>';
    @endphp

    {{-- 1 · TITLE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · PHP architecture · 2026</p>
            <h1 class="dw-title">Where Should Native Code Live?</h1>
            <p class="dw-lead">Phalcon, Symfony, {!! $ta !!} and {!! $flow !!}<br>at different layers of the stack</p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Framework</div>
                <div class="dw-arrow">·</div>
                <div class="dw-node">Application</div>
                <div class="dw-arrow">·</div>
                <div class="dw-node">Runtime</div>
                <div class="dw-arrow">·</div>
                <div class="dw-node">Orchestration</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · CONTEXT --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The context</p>
            <h2 class="dw-heading-slide">A client architecture POC - a familiar dilemma.</h2>
            <div class="mt-10 dw-split">
                <div class="dw-split-panel is-warn">
                    <h3>The visible question</h3>
                    <p style="margin:0;">Should we migrate from Phalcon to Symfony?</p>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>The deeper question</h3>
                    <p style="margin:0;">Where should performance, boundaries, and workflows live?</p>
                </div>
            </div>
            <p class="dw-note mt-8">High-traffic PHP platform · proven performance · growing workflow complexity · maintenance pressure.</p>
        </section>
        <footer class="dw-footer"><span>01 / context</span></footer>
    </x-slidewire::slide>

    {{-- 3 · FALSE DEBATE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The false debate</p>
            <h2 class="dw-heading">Phalcon vs Symfony vs {!! $ta !!}</h2>
            <p class="dw-lead">They are not direct competitors.</p>
            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-chip">Different layers</div>
                <div class="dw-chip">Different constraints</div>
                <div class="dw-chip">Different tradeoffs</div>
            </div>
            <p class="dw-question mt-10">Framework choice is only part of the architecture.</p>
        </section>
        <footer class="dw-footer"><span>02 / false-debate</span></footer>
    </x-slidewire::slide>

    {{-- 4 · THREE LAYERS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Three native layers</p>
            <h2 class="dw-heading-slide">Each technology optimizes a different concern.</h2>
            <div class="mt-10 dw-map">
                <div><strong>Phalcon</strong><span>Native framework layer - MVC, router, ORM, cache</span></div>
                <div><strong>Symfony</strong><span>Application structure - DI, security, ecosystem, boundaries</span></div>
                <div><strong>TrueAsync</strong><span>Native runtime - coroutines, async I/O, execution</span></div>
            </div>
        </section>
        <footer class="dw-footer"><span>03 / layers</span></footer>
    </x-slidewire::slide>

    {{-- 5 · PHALCON --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Phalcon</p>
            <h2 class="dw-heading">Phalcon was not a mistake.</h2>
            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-card"><h3>C extension</h3><p>Zephir/C · low overhead on the MVC path.</p></div>
                <div class="dw-card"><h3>Read-heavy fit</h3><p>Predictable latency · monolith APIs · cache-friendly.</p></div>
                <div class="dw-card"><h3>Still maintained</h3><p>v5.x active · releases in 2026 · ecosystem backers.</p></div>
                <div class="dw-card"><h3>Valid choice</h3><p>When MVC performance and team expertise still dominate.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>04 / phalcon</span></footer>
    </x-slidewire::slide>

    {{-- 6 · OPTIMIZATION TARGET --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The shift</p>
            <h2 class="dw-heading-slide">The optimization target changed.</h2>
            <div class="dw-split mt-8">
                <div class="dw-split-panel">
                    <h3>Before</h3>
                    <ul class="dw-list-compact" style="margin:0;">
                        <li>MVC bootstrap</li>
                        <li>Dispatch cost</li>
                        <li>Framework overhead</li>
                    </ul>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Now</h3>
                    <ul class="dw-list-compact" style="margin:0;">
                        <li>I/O in workers</li>
                        <li>Multi-step workflows</li>
                        <li>Domain boundaries</li>
                        <li>Interop &amp; hiring</li>
                    </ul>
                </div>
            </div>
            <p class="dw-note mt-8">Obsolete? Wrong question. <strong>What optimization matters now?</strong></p>
        </section>
        <footer class="dw-footer"><span>05 / optimization</span></footer>
    </x-slidewire::slide>

    {{-- 7 · WHERE NATIVE CODE LIVES --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Architecture</p>
            <h2 class="dw-heading-slide">Where should native code live?</h2>
            <div class="mt-6 dw-embed" style="height:min(420px,52vh)">
                <x-slidewire::diagram>
flowchart TB
  subgraph orch [Orchestration layer]
    W[Workflows and pipelines]
  end
  subgraph app [Application layer]
    S[Symfony structure\nboundaries DI ecosystem]
  end
  subgraph fw [Framework layer]
    P[Phalcon native MVC]
  end
  subgraph rt [Runtime layer]
    T[TrueAsync ext-async\noptional execution]
  end
  Q[Where does complexity live?]
  Q --> orch
  Q --> app
  Q --> fw
  Q --> rt
                </x-slidewire::diagram>
            </div>
            <p class="dw-note mt-4">Four independent decisions - not one migration checkbox.</p>
        </section>
        <footer class="dw-footer"><span>06 / where-native</span></footer>
    </x-slidewire::slide>

    {{-- 8 · BOUNDARIES --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Boundaries</p>
            <h2 class="dw-heading">Boundaries before frameworks.</h2>
            <p class="dw-lead">Many migrations fail because the container changes - not the domain.</p>
            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-chip">Bounded contexts</div>
                <div class="dw-chip">Domain vs application services</div>
                <div class="dw-chip">CQRS / read models</div>
                <div class="dw-chip">Eventual consistency</div>
            </div>
            <p class="dw-takeaway mt-8">Redraw boundaries first. Framework second.</p>
        </section>
        <footer class="dw-footer"><span>07 / boundaries</span></footer>
    </x-slidewire::slide>

    {{-- 9 · PEFT --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Archetype · PEFT</p>
            <h2 class="dw-heading-slide">Read-heavy editorial platform pattern.</h2>
            <div class="mt-6 dw-embed" style="height:min(340px,42vh)">
                <x-slidewire::diagram>
flowchart LR
  IN[Ingestion] --> PUB[Publication]
  PUB --> STORE[(Canonical store)]
  PUB --> INV[Invalidation]
  INV --> RM[Read models]
  RM --> API[Read APIs]
                </x-slidewire::diagram>
            </div>
            <div class="mt-6 dw-grid dw-grid-3">
                <div class="dw-chip">Event-driven</div>
                <div class="dw-chip">Derived views</div>
                <div class="dw-chip">Cache-heavy read path</div>
            </div>
        </section>
        <footer class="dw-footer"><span>08 / peft</span></footer>
    </x-slidewire::slide>

    {{-- 10 · ORCHESTRATION --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Orchestration</p>
            <h2 class="dw-heading">Frameworks excel at request → response.</h2>
            <p class="dw-lead">Mature systems exceed that cycle.</p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Validate</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Persist</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Invalidate</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Rebuild</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Emit</div>
            </div>
            <p class="dw-note mt-8">Multi-step workflows need a first-class architectural place.</p>
        </section>
        <footer class="dw-footer"><span>09 / orchestration</span></footer>
    </x-slidewire::slide>

    {{-- 11 · FLOW --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">{!! $flow !!}</p>
            <h2 class="dw-heading-slide">Same workflow. Interchangeable execution.</h2>
            <p class="dw-lead" style="font-size:clamp(1rem,1.8vw,1.35rem);"><code>DriverInterface</code> - business logic stable, execution model changes.</p>
            <div class="mt-6 dw-grid dw-grid-3">
                <div class="dw-chip">Fiber <span class="dw-muted">default</span></div>
                <div class="dw-chip">Amp</div>
                <div class="dw-chip">ReactPHP</div>
                <div class="dw-chip">Swoole</div>
                <div class="dw-chip">TrueAsync <span class="dw-muted">experimental</span></div>
            </div>
            <pre class="mt-6 dw-code" style="font-size:clamp(0.75rem,1.2vw,0.95rem);">$flow = (new Flow(job: new Validate(), driver: $driver))
    ->fn(new Persist())
    ->fn(new RebuildReadModel());</pre>
            <p class="dw-note mt-4">{!! $flow !!} explores orchestration as an architectural primitive - not a framework replacement.</p>
        </section>
        <footer class="dw-footer"><span>10 / flow</span></footer>
    </x-slidewire::slide>

    {{-- 12 · FOUR PATHS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Paths</p>
            <h2 class="dw-heading-slide">Four credible outcomes - none is default.</h2>
            <div class="mt-10 dw-map">
                <div><strong>01</strong><span>Stay on Phalcon · modernize incrementally</span></div>
                <div><strong>02</strong><span>Migrate to Symfony · ecosystem &amp; structure</span></div>
                <div><strong>03</strong><span>Hybrid · coexistence with clear API contracts</span></div>
                <div><strong>04</strong><span>Runtime async · independent of web framework</span></div>
            </div>
        </section>
        <footer class="dw-footer"><span>11 / paths</span></footer>
    </x-slidewire::slide>

    {{-- 13 · WHAT NOT TO DO --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Anti-patterns</p>
            <h2 class="dw-heading">What not to do.</h2>
            <ul class="mt-10 dw-list">
                <li>Migrate by reflex - when SLOs and team fit are fine</li>
                <li>Put async everywhere - {!! $ta !!} is experimental, opt-in only</li>
                <li>Change framework without redrawing boundaries</li>
                <li>Confuse framework migration with workflow design</li>
                <li>Turn {!! $flow !!} into a framework for every feature</li>
            </ul>
        </section>
        <footer class="dw-footer"><span>12 / anti-patterns</span></footer>
    </x-slidewire::slide>

    {{-- 14 · CONCLUSION --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Conclusion</p>
            <h2 class="dw-heading">Framework choice still matters.</h2>
            <p class="dw-lead mt-8">The deeper questions:</p>
            <div class="mt-8 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Workflows</h3><p>Where do multi-step pipelines live?</p></div>
                <div class="dw-card"><h3>Boundaries</h3><p>Where does domain end and coordination begin?</p></div>
                <div class="dw-card"><h3>Orchestration</h3><p>Who owns execution strategy?</p></div>
            </div>
            <p class="dw-takeaway mt-10">Native code distributes across the stack.</p>
        </section>
        <footer class="dw-footer"><span>13 / conclusion</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
