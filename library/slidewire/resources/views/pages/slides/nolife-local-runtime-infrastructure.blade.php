<x-slidewire::deck theme="black" transition="fade" show-progress="true" show-controls="true" show-fullscreen-button="true">
    @php
        $NL = 'http://127.0.0.1:8002';
        $brand = '<span class="dw-accent">NoLife Models</span>';
    @endphp

    {{-- 00 — Title --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood / local inference infrastructure</p>
            <h1 class="dw-title">{!! $brand !!}</h1>
            <p class="dw-lead">Exploring Local AI Runtime Infrastructure</p>
            <p class="dw-note mt-8">Symfony 8 · Symfony UX · Ollama · models.dev · benchmarks · exports</p>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 01 — Thesis --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Thesis</p>
            <h2 class="dw-heading">We are moving from “chatbot APIs” to <span class="dw-accent">AI runtime infrastructure</span>.</h2>
            <p class="dw-lead">Catalogs, local engines, evaluation harnesses, and exportable traces — the same primitives ops expects from any serious runtime.</p>
        </section>
        <footer class="dw-footer"><span>01 / thesis</span></footer>
    </x-slidewire::slide>

    {{-- 02 — Agenda --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Agenda</p>
            <h2 class="dw-heading-slide">Five arcs — one pipeline.</h2>
            <div class="mt-12 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Catalogs</h3><p>models.dev · modalities · cost · context.</p></div>
                <div class="dw-card"><h3>Runtimes</h3><p>Ollama as HTTP surface · future adapters.</p></div>
                <div class="dw-card"><h3>NoLife Models</h3><p>Compare · benchmark · export.</p></div>
                <div class="dw-card"><h3>Kronk</h3><p>Embedded inference — not “better Ollama”.</p></div>
                <div class="dw-card"><h3>Governance</h3><p>Ports, traces, reproducibility.</p></div>
                <div class="dw-card"><h3>Outlook</h3><p>Orchestration &amp; embedded edge.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>02 / agenda</span></footer>
    </x-slidewire::slide>

    {{-- 03 — models.dev --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Part 1 — Catalog plane</p>
            <h2 class="dw-heading"><span class="dw-accent">models.dev</span> is a contract surface for the model economy.</h2>
            <p class="dw-lead">Structured metadata: modalities, limits, pricing tiers, reasoning flags — a shared vocabulary before any `POST /generate`.</p>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Provider</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Model record</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Tooling &amp; SDKs</div>
            </div>
        </section>
        <footer class="dw-footer"><span>03 / models.dev</span></footer>
    </x-slidewire::slide>

    {{-- 04 — modles --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Comparator plane</p>
            <h2 class="dw-heading"><span class="dw-accent">modles</span> — local browser lab for rows from the catalog.</h2>
            <p class="dw-lead">Filter, sort by $/M, context, capabilities — engineer-grade spreadsheet over the same facts NoLife surfaces in Symfony.</p>
        </section>
        <footer class="dw-footer"><span>04 / modles</span></footer>
    </x-slidewire::slide>

    {{-- 05 — Docker analogy --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Analogy — shipping vs inference</p>
            <h2 class="dw-heading-slide">Docker ecosystem ↔ AI runtime ecosystem</h2>
            <div class="mt-10 dw-map">
                <div><strong>Docker Hub</strong><span>models.dev</span></div>
                <div><strong>Images</strong><span>Model artifacts (GGUF, weights)</span></div>
                <div><strong>dockerd</strong><span>Ollama / Kronk / vLLM…</span></div>
                <div><strong>Kubernetes</strong><span>Agent &amp; batch orchestration</span></div>
                <div><strong>Observability</strong><span>Traces · exports · benchmarks</span></div>
                <div><strong>containerd / OCI</strong><span><code class="text-[var(--dw-cyan)]">LocalModelRuntimeInterface</code></span></div>
            </div>
        </section>
        <footer class="dw-footer"><span>05 / analogy</span></footer>
    </x-slidewire::slide>

    {{-- 06 — Ollama --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Part 2 — Process-local HTTP runtime</p>
            <h2 class="dw-heading"><span class="dw-accent">Ollama</span> exposes models as <span class="dw-accent">local HTTP services</span>.</h2>
            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Inventory</h3><p><code>/api/tags</code> — what’s pulled, which quant.</p></div>
                <div class="dw-card"><h3>Inference</h3><p><code>/api/generate</code> — non-stream MVP in NoLife.</p></div>
                <div class="dw-card"><h3>Metadata</h3><p>Load time, eval counts, wall clock — rudimentary SRE signals.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>06 / ollama</span></footer>
    </x-slidewire::slide>

    {{-- 07 — DEMO catalog --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Live — NoLife Models</p>
            <h2 class="dw-heading-slide">Catalog explorer</h2>
            <p class="dw-lead">Merged models.dev API + JSON overrides · live filters · modalities · $/M · context windows.</p>
            <x-nolife-demo-frame url="{{ $NL }}/en/embed/catalog" title="NoLife catalog" class="mt-4" />
        </section>
        <footer class="dw-footer"><span>07 / demo · catalog</span></footer>
    </x-slidewire::slide>

    {{-- 08 — DEMO ollama --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Live — NoLife Models</p>
            <h2 class="dw-heading-slide">Installed local models</h2>
            <p class="dw-lead">Runtime discovery against your daemon — tags, quant, what’s actually callable.</p>
            <x-nolife-demo-frame url="{{ $NL }}/en/embed/ollama" title="NoLife Ollama inventory" class="mt-4" />
        </section>
        <footer class="dw-footer"><span>08 / demo · ollama</span></footer>
    </x-slidewire::slide>

    {{-- 09 — NoLife intro --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Part 3 — Reference app</p>
            <h2 class="dw-heading">{!! $brand !!} — <span class="dw-accent">Symfony 8</span> + <span class="dw-accent">Symfony UX</span></h2>
            <p class="dw-lead">DDD / hexagonal core: catalog ports, <code class="text-[var(--dw-cyan)]">LocalModelRuntimeInterface</code>, exporters, session-backed results.</p>
            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-card"><h3>UX Live</h3><p>Catalog explorer (Live Component) — filters without round-trips.</p></div>
                <div class="dw-card"><h3>Turbo</h3><p>Fast morphing between compare / results.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>09 / nolife stack</span></footer>
    </x-slidewire::slide>

    {{-- 10 — Architecture --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Hexagonal slice</p>
            <h2 class="dw-heading-slide">Application ↔ ports ↔ adapters</h2>
            <div class="mt-8 dw-embed dw-embed-demo" style="height:min(420px,50vh);max-width:100%">
                <x-slidewire::diagram>
flowchart TB
  subgraph UI["UserInterface"]
    TW["Twig + Forms"]
    LC["LiveComponent"]
  end
  subgraph APP["Application"]
    RC["Compare handler"]
    RB["Benchmark handler"]
  end
  subgraph DOM["Domain"]
    LR(("LocalModelRuntime"))
    CR(("CatalogRepository"))
    EX(("Exporter"))
  end
  subgraph INF["Infrastructure"]
    OL["Ollama runtime"]
    MD["models.dev cache"]
    SE["Session + files"]
  end
  TW --> RC
  LC --> RC
  RC --> LR
  RC --> CR
  RB --> LR
  RB --> CR
  OL -.-> LR
  MD -.-> CR
  SE -.-> EX
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>10 / architecture</span></footer>
    </x-slidewire::slide>

    {{-- 11 — Port snippet --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Runtime port</p>
            <h2 class="dw-heading-slide"><code class="text-[var(--dw-cyan)]">LocalModelRuntimeInterface</code></h2>
            <pre class="mt-8 dw-code">interface LocalModelRuntimeInterface
{
    /** @return list&lt;LocalModel&gt; */
    public function listLocalModels(): array;

    public function generate(GeneratePromptCommand $command): ModelInferenceResult;
}</pre>
            <p class="dw-note mt-6">Today: Ollama. Tomorrow: OpenAI-compatible, LM Studio, vLLM — same contract.</p>
        </section>
        <footer class="dw-footer"><span>11 / port</span></footer>
    </x-slidewire::slide>

    {{-- 12 — DEMO compare --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Live — NoLife Models</p>
            <h2 class="dw-heading-slide">Compare two models</h2>
            <p class="dw-lead">Same prompt · two tags · wall clock + token-ish metrics — engineering comparison, not vibes.</p>
            <x-nolife-demo-frame url="{{ $NL }}/en/embed/compare" title="NoLife compare" class="mt-4" />
        </section>
        <footer class="dw-footer"><span>12 / demo · compare</span></footer>
    </x-slidewire::slide>

    {{-- 13 — Compare concepts --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Evaluation plane</p>
            <h2 class="dw-heading">Latency is cheap to measure. <span class="dw-accent">Quality is expensive.</span></h2>
            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Metrics</h3><p>Duration, load, eval counts — reproducible numbers.</p></div>
                <div class="dw-card"><h3>Reasoning</h3><p>Side-by-side text for human triage.</p></div>
                <div class="dw-card"><h3>Next</h3><p>Structured rubrics · model grading pipelines.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>13 / compare concepts</span></footer>
    </x-slidewire::slide>

    {{-- 14 — DEMO benchmark --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Live — NoLife Models</p>
            <h2 class="dw-heading-slide">Benchmark suite</h2>
            <p class="dw-lead">Micro-cases (EN/FR) · deterministic prompts · matrix results — orchestration over the same runtime port.</p>
            <x-nolife-demo-frame url="{{ $NL }}/en/embed/benchmark" title="NoLife benchmark" class="mt-4" />
        </section>
        <footer class="dw-footer"><span>14 / demo · benchmark</span></footer>
    </x-slidewire::slide>

    {{-- 15 — Benchmark flow --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Orchestration</p>
            <h2 class="dw-heading-slide">Benchmark flow</h2>
            <div class="mt-8 dw-embed dw-embed-demo" style="height:min(400px,48vh)">
                <x-slidewire::diagram>
flowchart LR
  S[Suite definition] --> M[Model A / Model B]
  M --> P[Prompt cases]
  P --> R[Runtime generate]
  R --> T[Timing + text capture]
  T --> X[Matrix + exports]
                </x-slidewire::diagram>
            </div>
            <p class="dw-note mt-6">Repeatability beats one-off screenshots.</p>
        </section>
        <footer class="dw-footer"><span>15 / benchmark flow</span></footer>
    </x-slidewire::slide>

    {{-- 16 — DEMO exports --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Live — NoLife Models</p>
            <h2 class="dw-heading-slide">Exports</h2>
            <p class="dw-lead">JSON · CSV · Markdown — stable schema/version fields for downstream tooling.</p>
            <x-nolife-demo-frame url="{{ $NL }}/en/embed/exports" title="NoLife exports" class="mt-4" />
        </section>
        <footer class="dw-footer"><span>16 / demo · exports</span></footer>
    </x-slidewire::slide>

    {{-- 17 — Observability --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Governance</p>
            <h2 class="dw-heading">Inference without artifacts is <span class="dw-accent">faith</span>, not engineering.</h2>
            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-card"><h3>Audit trail</h3><p>Exports + session IDs — what ran, when, with which tags.</p></div>
                <div class="dw-card"><h3>Runtime governance</h3><p>Policies on top of the same port: quotas, routing, kill switches.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>17 / observability</span></footer>
    </x-slidewire::slide>

    {{-- 18 — Kronk positioning --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Part 4 — Embedded inference</p>
            <h2 class="dw-heading"><span class="dw-accent">Kronk</span> is not “better Ollama”.</h2>
            <p class="dw-lead">Ollama: models as <strong>local HTTP services</strong>. Kronk: push inference <strong>into the application process</strong> — GGUF via llama.cpp, streaming, OpenAI-compatible ergonomics where it fits.</p>
            <p class="dw-note mt-8">Yzma layers orchestration; the story is <span class="dw-accent">co-location of model + app</span> for latency, packaging, and offline-first products.</p>
        </section>
        <footer class="dw-footer"><span>18 / kronk</span></footer>
    </x-slidewire::slide>

    {{-- 19 — Kronk stack --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Embedded stack</p>
            <h2 class="dw-heading-slide">Primitives</h2>
            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-card"><h3>GGUF</h3><p>Single-file weights — ship like any binary asset.</p></div>
                <div class="dw-card"><h3>llama.cpp</h3><p>CPU/GPU inference core — predictable foot-print.</p></div>
                <div class="dw-card"><h3>Streaming</h3><p>Token deltas — UX + back-pressure aware servers.</p></div>
                <div class="dw-card"><h3>OpenAI-shaped APIs</h3><p>Interchange clients — ecosystem leverage without cloud lock-in.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>19 / embedded primitives</span></footer>
    </x-slidewire::slide>

    {{-- 20 — Pipeline --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">End-to-end</p>
            <h2 class="dw-heading-slide">Catalog → runtime → evaluation → export</h2>
            <div class="mt-8 dw-embed dw-embed-demo" style="height:min(360px,44vh)">
                <x-slidewire::diagram>
flowchart LR
  C[Catalog plane] --> R[Runtime plane]
  R --> E[Evaluation plane]
  E --> O[Observability plane]
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>20 / pipeline</span></footer>
    </x-slidewire::slide>

    {{-- 21 — Future --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Part 5 — Outlook</p>
            <h2 class="dw-heading">Multi-runtime clusters · policy-aware routing · <span class="dw-accent">edge bundles</span> with embedded weights.</h2>
            <p class="dw-lead">The same governance patterns (ports, traces, exports) port from Symfony apps to agents to on-device runtimes.</p>
        </section>
        <footer class="dw-footer"><span>21 / future</span></footer>
    </x-slidewire::slide>

    {{-- 22 — Outro --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Closing</p>
            <h2 class="dw-heading">We are moving from chatbot APIs to <span class="dw-accent">AI runtime infrastructure</span>.</h2>
            <p class="dw-lead">{!! $brand !!} is a thin, opinionated slice through that stack — catalogs, local engines, benchmarks, exports — built as a real Symfony product surface.</p>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Catalog</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Runtime port</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Evaluation</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Artifacts</div>
            </div>
        </section>
        <footer class="dw-footer"><span>22 / outro</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>
</x-slidewire::deck>
