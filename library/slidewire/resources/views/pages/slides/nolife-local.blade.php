{{-- When AI Becomes a Library — The Nolife Local Experiment --}}
{{-- Source: tasks/#d8eb3d_nolife-local/article/article.md --}}
{{-- Route: /slides/nolife-local --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default"
    show-progress="true" show-controls="true" show-fullscreen-button="true">

<style>
    .nl-table { width: 100%; border-collapse: collapse; font-family: var(--font-mono); font-size: 18px; margin-top: 24px; }
    .nl-table th, .nl-table td { padding: 10px 14px; text-align: right; border-bottom: 1px solid var(--dw-line); }
    .nl-table th:first-child, .nl-table td:first-child { text-align: left; }
    .nl-table th { color: var(--dw-cyan); font-size: 12px; letter-spacing: .08em; text-transform: uppercase; }
    .nl-table td { color: #eef7ff; }
    .nl-table tr.is-hot td { color: var(--dw-lime); font-weight: 700; }
    .nl-table tr.is-warn td { color: #ffb4a8; font-weight: 700; }
    .nl-mini { font-family: var(--font-mono); font-size: 16px; line-height: 1.5; color: #d7ecff; white-space: pre-wrap; margin: 0; }
    .nl-stat { text-align: center; padding: 22px 18px; }
    .nl-stat strong { display: block; font-size: clamp(36px, 4vw, 52px); color: var(--dw-text); line-height: 1; }
    .nl-stat span { display: block; margin-top: 8px; color: var(--dw-muted); font-size: 17px; }
    .nl-shot { border: 1px solid var(--dw-line); border-radius: 8px; max-height: min(42vh, 380px); width: auto; max-width: 100%; object-fit: contain; box-shadow: 0 22px 64px rgba(0,0,0,.35); }
    .nl-shot-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 20px; align-items: start; }
    .nl-note { margin-top: 14px; color: var(--dw-muted); font-size: 15px; font-family: var(--font-mono); }
    .dw-heading-slide { max-width: 980px; font-size: clamp(38px, 4.2vw, 68px); line-height: 1.05; font-weight: 700; }
    .dw-accent { color: var(--dw-cyan); }
    .dw-wrap--top { justify-content: flex-start; padding-top: 56px; }
</style>

    {{-- 01 · TITLE --}}
    {{-- @notes
        OPEN (~30s). When AI Becomes a Library — Nolife Local Experiment.
        Depth Anything 3 in Symfony via PHP FFI and Darkwood Flow.
        Measured on Apple M4 / Metal. Not a product pitch — a lab report.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · Symfony · PHP FFI · Flow</p>
            <h1 class="dw-title">When AI Becomes a Library</h1>
            <p class="dw-lead">The Nolife Local Experiment<br><span class="dw-accent">Depth Anything 3 + Symfony + PHP FFI + Darkwood Flow</span></p>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 02 · DEFAULT REFLEX --}}
    {{-- @notes
        Default AI reflex: every problem → large model → prompt → text.
        Works often enough that we stop asking if it's the right abstraction.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The default reflex</p>
            <h2 class="dw-heading-slide">We use LLMs for everything.</h2>
            <div class="dw-flow dw-flow--vertical" style="max-width:360px;margin-top:28px;">
                <div class="dw-node">problem</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">large model</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">prompt</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">text</div>
            </div>
        </section>
        <footer class="dw-footer"><span>02 / reflex</span></footer>
    </x-slidewire::slide>

    {{-- 03 · THE QUESTION --}}
    {{-- @notes
        Pivot: what if the task already has a specialized model?
        Output shape changes — structured computation, not language.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The question</p>
            <h2 class="dw-heading-slide">What if the task already has a <span class="dw-accent">specialized model</span>?</h2>
            <div class="dw-flow dw-flow--vertical" style="max-width:420px;margin-top:32px;">
                <div class="dw-node">problem</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node" style="border-color:rgba(184,255,106,.4);">specialized model</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">structured computation</div>
            </div>
        </section>
        <footer class="dw-footer"><span>03 / question</span></footer>
    </x-slidewire::slide>

    {{-- 04 · DEPTH IS NOT LANGUAGE --}}
    {{-- @notes
        Strong contrast slide. LLM gives prose; DA3 gives 504×280 floats.
        DA3-BASE: relative depth, not meters.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Depth is not language</p>
            <div class="dw-split" style="margin-top:20px;">
                <div class="dw-split-panel">
                    <h3>General LLM</h3>
                    <p class="nl-mini" style="font-size:22px;color:var(--dw-muted);">"The mountains are far away."</p>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Depth Anything 3</h3>
                    <p class="nl-mini" style="font-size:28px;color:var(--dw-lime);">504 × 280<br>depth values</p>
                    <p class="nl-note">relative units · not meters</p>
                </div>
            </div>
            <img src="/images/nolife-local/04-depth-mountains.png" alt="Turbo colormap depth" class="nl-shot mt-6" style="max-height:28vh;">
        </section>
        <footer class="dw-footer"><span>04 / depth</span></footer>
    </x-slidewire::slide>

    {{-- 05 · DEPTH ANYTHING 3 --}}
    {{-- @notes
        One dense inference: depth, confidence, camera, GLB path.
        DA3-BASE via depth-anything.cpp + ggml. No cloud API at inference.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Depth Anything 3</p>
            <div class="dw-flow dw-flow--vertical" style="max-width:480px;margin-top:16px;">
                <div class="dw-node">RGB image</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Depth Anything 3</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-grid dw-grid-2" style="gap:10px;">
                    <div class="dw-chip">depth</div>
                    <div class="dw-chip">confidence</div>
                    <div class="dw-chip">camera</div>
                    <div class="dw-chip">3D / GLB</div>
                </div>
            </div>
            <p class="dw-takeaway mt-6" style="font-size:20px;">DA3-BASE · relative depth · <span class="dw-accent">not meters</span></p>
        </section>
        <footer class="dw-footer"><span>05 / da3</span></footer>
    </x-slidewire::slide>

    {{-- 06 · RESEARCH → RUNTIME --}}
    {{-- @notes
        PyTorch checkpoint → GGUF (once) → ggml → Metal → outputs.
        We did not train the model. Quantization is runtime choice, not fine-tuning.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Research → runtime</p>
            <div class="dw-flow dw-flow--vertical" style="max-width:520px;margin-top:20px;">
                <div class="dw-node">PyTorch checkpoint</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">GGUF</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">ggml</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Metal</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Depth · Camera · GLB</div>
            </div>
        </section>
        <footer class="dw-footer"><span>06 / runtime</span></footer>
    </x-slidewire::slide>

    {{-- 07 · WHAT SPECIALIZED MEANS --}}
    {{-- @notes
        NOT fine-tuning / LoRA / training. YES purpose-trained model + local runtime + quantization.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What “specialized” means</p>
            <div class="dw-split" style="margin-top:24px;">
                <div class="dw-split-panel is-warn">
                    <h3>Not this project</h3>
                    <div class="dw-vs-list" style="margin-top:0;">
                        <div class="dw-vs-item is-no">fine-tuning</div>
                        <div class="dw-vs-item is-no">LoRA</div>
                        <div class="dw-vs-item is-no">training from scratch</div>
                    </div>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>This project</h3>
                    <div class="dw-vs-list" style="margin-top:0;">
                        <div class="dw-vs-item is-yes">purpose-trained model</div>
                        <div class="dw-vs-item is-yes">local native runtime</div>
                        <div class="dw-vs-item is-yes">quantized representation</div>
                    </div>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>07 / specialized</span></footer>
    </x-slidewire::slide>

    {{-- 08 · PHP CONSTRAINT --}}
    {{-- @notes
        Hook: no Python service, no cloud AI API. Symfony → FFI → native model.
        Scope: rejected Messenger, DDD, pure PHP inference — problem defines scope.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The unusual constraint</p>
            <h2 class="dw-heading-slide">No Python service.<br>No cloud AI API.</h2>
            <div class="dw-flow dw-flow--vertical" style="max-width:400px;margin-top:28px;">
                <div class="dw-node">Symfony</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">PHP FFI</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">native model</div>
            </div>
        </section>
        <footer class="dw-footer"><span>08 / php</span></footer>
    </x-slidewire::slide>

    {{-- 09 · ARCHITECTURE --}}
    {{-- @notes
        Full stack diagram. PHP does not run ViT kernels. Flow sits above FFI bridge.
        Walk top to bottom slowly.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Architecture</p>
            <h2 class="dw-heading-slide">Symfony hosts the boundary.</h2>
            <div class="mt-6 dw-embed" style="height:min(440px,52vh);max-width:100%;">
                <x-slidewire::diagram>
flowchart TB
  B["Browser"]
  S["Symfony"]
  F["Darkwood Flow"]
  N["NativeDepthBridge"]
  FFI["PHP FFI"]
  L["libdepthanything"]
  G["ggml / Metal"]
  D["Depth Anything 3"]
  B --> S --> F --> N --> FFI --> L --> G --> D
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>09 / architecture</span></footer>
    </x-slidewire::slide>

    {{-- 10 · PHP VS NATIVE --}}
    {{-- @notes
        Ownership split. PHP: HTTP, Flow, GD, CLI. Native: ViT, DPT, Metal, GLB.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Ownership</p>
            <div class="dw-split" style="margin-top:16px;">
                <div class="dw-split-panel">
                    <h3>PHP owns</h3>
                    <p class="nl-mini">HTTP · validation · Flow
orchestration · GD colormap
Twig · CLI · observability</p>
                </div>
                <div class="dw-split-panel">
                    <h3>Native owns</h3>
                    <p class="nl-mini">ViT · DPT · depth
confidence · camera
Metal · GLB export</p>
                </div>
            </div>
            <p class="dw-takeaway mt-4" style="font-size:20px;">PHP does <em>not</em> perform matrix multiplication.</p>
        </section>
        <footer class="dw-footer"><span>10 / ownership</span></footer>
    </x-slidewire::slide>

    {{-- 11 · FFI --}}
    {{-- @notes
        Small real boundary. native pointer → PHP array → free.
        Segfault lesson: indexed reads, not large casts. Stress test validated lifetime.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">PHP FFI</p>
            <pre class="dw-code mt-4" style="font-size:17px;">$ffi->da_capi_depth_dense(
    $ctx, $imagePath, …
);</pre>
            <div class="dw-flow dw-flow--vertical" style="max-width:380px;margin-top:20px;">
                <div class="dw-node" style="font-size:18px;">native pointer</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node" style="font-size:18px;">PHP array</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node" style="font-size:18px;">da_capi_free_floats</div>
            </div>
        </section>
        <footer class="dw-footer"><span>11 / ffi</span></footer>
    </x-slidewire::slide>

    {{-- 12 · PIPELINE --}}
    {{-- @notes
        Five Flow steps. Show upload UI screenshot. Darkwood Flow introduced for observability.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Application pipeline</p>
            <div class="dw-flow dw-flow--vertical" style="max-width:320px;margin-top:12px;">
                <div class="dw-node" style="min-height:52px;font-size:18px;">Upload</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node" style="min-height:52px;font-size:18px;">Validate → Infer → Colormap</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node" style="min-height:52px;font-size:18px;">GLB → Result</div>
            </div>
            <img src="/images/nolife-local/01-upload-page.png" alt="Upload UI" class="nl-shot mt-4" style="max-height:32vh;float:right;margin-left:24px;">
            <p class="dw-takeaway" style="font-size:18px;clear:both;">Darkwood Flow · explicit steps · per-step timings</p>
        </section>
        <footer class="dw-footer"><span>12 / pipeline</span></footer>
    </x-slidewire::slide>

    {{-- 13 · WHY FLOW --}}
    {{-- @notes
        Flow did not make inference faster. It made the pipeline visible.
        Show timing screenshot from result page.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Observability</p>
            <h2 class="dw-heading-slide">Flow did not make inference faster.<br>It made the pipeline <span class="dw-accent">visible</span>.</h2>
            <img src="/images/nolife-local/05-flow-step-timings.png" alt="Flow step timings" class="nl-shot mt-4">
        </section>
        <footer class="dw-footer"><span>13 / flow</span></footer>
    </x-slidewire::slide>

    {{-- 14 · BENCHMARK ANOMALY --}}
    {{-- @notes
        Pre-optimization warm timings. ExportScene ~2535 ms — another full inference.
        Apple M4, warm run after Flow integration.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The benchmark that did not make sense</p>
            <table class="nl-table">
                <thead><tr><th>Step</th><th>ms</th></tr></thead>
                <tbody>
                    <tr><td>Validate</td><td>~3</td></tr>
                    <tr><td>InferDepth</td><td>~2639</td></tr>
                    <tr><td>Colormap</td><td>~86</td></tr>
                    <tr class="is-warn"><td>ExportScene</td><td>~2535</td></tr>
                    <tr class="is-hot"><td>Total</td><td>~5260</td></tr>
                </tbody>
            </table>
            <p class="dw-question">Why is ExportScene another 2.5 seconds?</p>
        </section>
        <footer class="dw-footer"><span>14 / anomaly</span></footer>
    </x-slidewire::slide>

    {{-- 15 · HIDDEN SECOND INFERENCE --}}
    {{-- @notes
        Before diagram. Two native passes. Architecture bug dressed as performance issue.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Before</p>
            <h2 class="dw-heading-slide">The hidden second inference</h2>
            <div class="dw-split" style="margin-top:24px;">
                <div class="dw-split-panel is-warn">
                    <pre class="nl-mini">image
 ├── inference → depth
 └── inference → GLB</pre>
                </div>
                <div class="dw-split-panel">
                    <p class="nl-mini">da_capi_depth_dense
da_capi_export_glb
→ depth_pose_native() × 2</p>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>15 / double pass</span></footer>
    </x-slidewire::slide>

    {{-- 16 · THE FIX --}}
    {{-- @notes
        After: one inference, export_glb_cached. Small code change; identifying duplication was the work.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">After</p>
            <h2 class="dw-heading-slide">One inference, several outputs</h2>
            <pre class="dw-code mt-4 nl-mini">image
  ↓
one inference
  ↓
native result
 ├── depth
 ├── confidence
 ├── camera
 └── GLB</pre>
            <p class="nl-note">da_capi_export_glb_cached · ABI v11</p>
        </section>
        <footer class="dw-footer"><span>16 / fix</span></footer>
    </x-slidewire::slide>

    {{-- 17 · BEFORE / AFTER --}}
    {{-- @notes
        Measured: 5260 → 3274 ms. 2 → 1 native inference. GLB ~19 ms cached.
        mountains.jpg 504×336 processed. Apple M4 / Metal.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Measured impact</p>
            <div class="dw-grid dw-grid-2" style="margin-top:20px;">
                <div class="dw-card nl-stat is-warn" style="border-color:rgba(255,120,100,.35);">
                    <strong>~5.26 s</strong>
                    <span>2 native inferences</span>
                </div>
                <div class="dw-card nl-stat is-ok" style="border-color:rgba(184,255,106,.35);">
                    <strong>~3.27 s</strong>
                    <span>1 native inference · GLB ~19 ms</span>
                </div>
            </div>
            <img src="/images/nolife-local/04-original-vs-depth.png" alt="Original vs depth" class="nl-shot mt-4" style="max-height:30vh;">
            <p class="nl-note">504×336 · q4_k · warm · Apple M4 / Metal</p>
        </section>
        <footer class="dw-footer"><span>17 / impact</span></footer>
    </x-slidewire::slide>

    {{-- 18 · RESOLUTION TRAP --}}
    {{-- @notes
        2495 ms vs ~3 s is not regression — different processed tensor sizes.
        Measurement discipline before optimization prompts.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Measurement discipline</p>
            <h2 class="dw-heading-slide">Different tensor size ≠ regression</h2>
            <table class="nl-table">
                <thead><tr><th>Source</th><th>Processed</th><th>Infer median</th></tr></thead>
                <tbody>
                    <tr><td>1280×720</td><td>504×280</td><td>~2495 ms</td></tr>
                    <tr><td>1024×680</td><td>504×336</td><td>~3.0–3.2 s</td></tr>
                </tbody>
            </table>
            <p class="dw-takeaway mt-4" style="font-size:20px;">Compare processed resolution — not headline numbers alone.</p>
        </section>
        <footer class="dw-footer"><span>18 / resolution</span></footer>
    </x-slidewire::slide>

    {{-- 19 · Q4_K VS F32 --}}
    {{-- @notes
        4× smaller on disk, not 4× faster at 504×280 warm. Apple M4 / Metal.
        Do not claim accuracy winner without ground truth.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Quantization</p>
            <h2 class="dw-heading-slide">4× smaller.<br>Not 4× faster.</h2>
            <div class="dw-grid dw-grid-2" style="margin-top:24px;">
                <div class="dw-card nl-stat">
                    <strong>q4_k</strong>
                    <span>~99 MB · ~2495 ms</span>
                </div>
                <div class="dw-card nl-stat">
                    <strong>f32</strong>
                    <span>~393 MB · ~2495 ms</span>
                </div>
            </div>
            <p class="nl-note">Warm infer-only · 504×280 · Apple M4 / Metal</p>
        </section>
        <footer class="dw-footer"><span>19 / quant</span></footer>
    </x-slidewire::slide>

    {{-- 20 · LOCAL --}}
    {{-- @notes
        No remote AI inference. GGUF on disk. Demo runtime offline after vendoring model-viewer.
        Install still needs network once.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Local means local</p>
            <div class="dw-vs-list" style="margin-top:24px;">
                <div class="dw-vs-item is-yes">No remote AI inference</div>
                <div class="dw-vs-item is-yes">No cloud API at runtime</div>
                <div class="dw-vs-item is-yes">GGUF on disk · Metal on device</div>
                <div class="dw-vs-item is-yes">Demo runtime works with networking disabled</div>
            </div>
            <img src="/images/nolife-local/06-glb-viewer.png" alt="GLB viewer" class="nl-shot mt-4" style="max-height:28vh;">
        </section>
        <footer class="dw-footer"><span>20 / local</span></footer>
    </x-slidewire::slide>

    {{-- 21 · INFRASTRUCTURE --}}
    {{-- @notes
        CLI diagnostics: depth-check, bench, stress, cleanup. Model as dependency.
        Flow/PHP overhead ~114 ms in same-run measurement.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">AI becomes infrastructure</p>
            <h2 class="dw-heading-slide">The model starts to look like a <span class="dw-accent">dependency</span>.</h2>
            <pre class="dw-code mt-4" style="font-size:16px;">php bin/console app:depth-check
php bin/console app:depth-bench
php bin/console app:depth-stress
php bin/console app:depth-cleanup</pre>
            <p class="nl-note">Flow / PHP overhead ~114 ms · infer dominates</p>
        </section>
        <footer class="dw-footer"><span>21 / infra</span></footer>
    </x-slidewire::slide>

    {{-- 22 · DEV LOOP --}}
    {{-- @notes
        Workflow evolved: idea→agent→inspect became observe→evidence→issue→measure.
        Human chooses problem and scope; agent implements within brief.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Development loop</p>
            <div class="dw-split" style="margin-top:20px;">
                <div class="dw-split-panel is-warn">
                    <h3>Early</h3>
                    <pre class="nl-mini">idea
→ ask agent
→ inspect</pre>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Later</h3>
                    <pre class="nl-mini">observe → evidence
→ issue → agent
→ implement → measure</pre>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>22 / loop</span></footer>
    </x-slidewire::slide>

    {{-- 23 · ISSUE-FIRST --}}
    {{-- @notes
        Real brief from double-inference investigation. Not "optimize PHP" — reuse native result.
        Context: timings, constraints, non-goals. Problem definition > code volume.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Issue-first</p>
            <h2 class="dw-heading-slide">The valuable part was the <span class="dw-accent">problem definition</span>.</h2>
            <pre class="dw-code mt-4 nl-mini" style="font-size:14px;">Observation:
Two native inference passes.

Evidence:
Infer ≈ 2.6 s · GLB ≈ 2.5 s

Constraint:
One inference per request.

Expected:
Reuse native dense result.

Non-goal:
No Messenger / concurrency layer.</pre>
        </section>
        <footer class="dw-footer"><span>23 / issue</span></footer>
    </x-slidewire::slide>

    {{-- 24 · SYNTHESIS --}}
    {{-- @notes
        Parallel: specialize model at runtime, specialize context for agent.
        Interpretation from project, not universal law.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Synthesis</p>
            <h2 class="dw-heading-slide">Reduce unnecessary generality.</h2>
            <div class="dw-split" style="margin-top:20px;">
                <div class="dw-split-panel">
                    <h3>Runtime</h3>
                    <pre class="nl-mini">general model
        ↓
specialized model</pre>
                </div>
                <div class="dw-split-panel">
                    <h3>Development</h3>
                    <pre class="nl-mini">general prompt
        ↓
well-defined issue</pre>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>24 / synthesis</span></footer>
    </x-slidewire::slide>

    {{-- 25 · LEARNED --}}
    {{-- @notes
        Article-supported findings only. FFI enough; observability found bottleneck;
        quantization helped size not latency here; context > code volume.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What I learned</p>
            <div class="dw-grid dw-grid-2" style="margin-top:20px;gap:12px;">
                <div class="dw-chip" style="text-align:left;padding:16px 20px;">PHP FFI was enough</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px;">Native infer dominates cost</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px;">Observability found the real bug</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px;">q4_k saved size, not latency here</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px;">Context &gt; code volume</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px;">Evidence beats speculation</div>
            </div>
        </section>
        <footer class="dw-footer"><span>25 / learned</span></footer>
    </x-slidewire::slide>

    {{-- 26 · CONCLUSION --}}
    {{-- @notes
        Close: task determines model, architecture, and prompt.
        Three layers: DA3, Flow, issue-first briefs. Not anti-LLM.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Conclusion</p>
            <h2 class="dw-heading-slide">The task should determine the <span class="dw-accent">model</span>, the <span class="dw-accent">architecture</span>, and the <span class="dw-accent">prompt</span>.</h2>
            <div class="dw-flow dw-flow--vertical" style="max-width:520px;margin-top:28px;">
                <div class="dw-node" style="font-size:17px;">Depth Anything 3 · specialized computation</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node" style="font-size:17px;">Darkwood Flow · observable pipeline</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node" style="font-size:17px;">Issue-first brief · specialized context</div>
            </div>
        </section>
        <footer class="dw-footer"><span>26 / conclusion</span></footer>
    </x-slidewire::slide>

    {{-- 27 · RESOURCES --}}
    {{-- @notes
        Links only. github.com/matyo91/nolife-local and slidewire. mudler/depth-anything.cpp upstream.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Resources</p>
            <div class="mt-10 dw-grid dw-grid-2" style="gap:16px;">
                <div class="dw-card">
                    <h3 style="font-size:22px;">Source</h3>
                    <p style="font-family:var(--font-mono);font-size:17px;color:var(--dw-cyan);">github.com/matyo91/nolife-local</p>
                </div>
                <div class="dw-card">
                    <h3 style="font-size:22px;">Slides</h3>
                    <p style="font-family:var(--font-mono);font-size:17px;color:var(--dw-cyan);">github.com/matyo91/slidewire</p>
                </div>
                <div class="dw-card" style="grid-column:1/-1;">
                    <h3 style="font-size:22px;">Upstream</h3>
                    <p style="font-family:var(--font-mono);font-size:17px;color:var(--dw-cyan);">github.com/mudler/depth-anything.cpp</p>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91 · Darkwood</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
