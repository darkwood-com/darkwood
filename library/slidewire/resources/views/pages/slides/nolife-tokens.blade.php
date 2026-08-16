{{-- Nolife Tokens — Shrinking LLM context without destroying recoverability --}}
{{-- Source: tasks/#601bc9_darklabs-tools/nolife-tokens-reversible-context.md · Companion: content/nolife-tokens --}}
{{-- Route: /slides/nolife-tokens --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

<style>
    .nt-bar-row { display: flex; align-items: center; gap: 12px; margin: 8px 0; font-family: var(--font-mono); font-size: 16px; }
    .nt-bar-label { width: 52px; color: var(--dw-muted); font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .nt-bar-track { flex: 1; height: 18px; background: rgba(255,255,255,.06); border-radius: 4px; overflow: hidden; border: 1px solid var(--dw-line); }
    .nt-bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--dw-blue), var(--dw-cyan)); }
    .nt-bar-fill.is-opt { background: var(--dw-lime); opacity: .85; }
    .nt-bar-val { width: 64px; text-align: right; color: #eef7ff; font-weight: 700; }
    .nt-case { margin-bottom: 14px; }
    .nt-case h3 { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
    .nt-table { width: 100%; border-collapse: collapse; font-family: var(--font-mono); font-size: 17px; }
    .nt-table th, .nt-table td { padding: 10px 12px; text-align: right; border-bottom: 1px solid var(--dw-line); }
    .nt-table th:first-child, .nt-table td:first-child { text-align: left; }
    .nt-table th { color: var(--dw-cyan); font-size: 13px; letter-spacing: .06em; text-transform: uppercase; font-weight: 700; }
    .nt-table td { color: #eef7ff; }
    .nt-note { margin-top: 14px; color: var(--dw-muted); font-size: 15px; font-family: var(--font-mono); }
    .nt-mini { font-family: var(--font-mono); font-size: 15px; line-height: 1.45; color: #d7ecff; white-space: pre-wrap; }
    .nt-col-title { font-size: 15px; letter-spacing: .08em; text-transform: uppercase; color: var(--dw-cyan); font-weight: 700; margin-bottom: 10px; }
</style>

    {{-- 1 · TITLE --}}
    {{-- @notes
        Nolife Tokens. Shrinking LLM context without destroying recoverability.
        Darkwood laboratory on Symfony and Flow — measured engineering, not a product pitch.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · Symfony · Flow · context</p>
            <h1 class="dw-title">Nolife Tokens</h1>
            <p class="dw-lead">Shrinking LLM context<br><span class="dw-accent">without destroying recoverability</span></p>
            <p class="mt-10" style="color:var(--dw-muted);font-size:20px;">Symfony + Flow experiment</p>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · THE PROBLEM --}}
    {{-- @notes
        Tool dumps fifty kilobytes of stdout into the model. Logs, telemetry, diffs, trees.
        Large windows make it possible to send everything. They do not make it wise.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The problem</p>
            <div class="dw-flow dw-flow--vertical" style="margin-top:18px;max-width:420px;">
                <div class="dw-node">Tool</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">50 KB stdout</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">LLM context</div>
            </div>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px;">
                <div class="dw-chip">logs</div>
                <div class="dw-chip">JSON telemetry</div>
                <div class="dw-chip">git diffs</div>
                <div class="dw-chip">repo trees</div>
                <div class="dw-chip">compiler output</div>
                <div class="dw-chip">test output</div>
            </div>
            <p class="dw-takeaway mt-8">Large context windows make it possible to send everything.<br>They do not make it wise.</p>
        </section>
        <footer class="dw-footer"><span>01 / problem</span></footer>
    </x-slidewire::slide>

    {{-- 3 · THE HYPOTHESIS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Hypothesis</p>
            <h2 class="dw-heading-slide">Do more deterministic work<br>before the LLM.</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:14px;">
                <div class="dw-card">
                    <h3 style="font-size:20px;">Before</h3>
                    <pre class="nt-mini mt-4">Tool
→ raw output
→ LLM</pre>
                </div>
                <div class="dw-card" style="border-color:rgba(120,220,180,.35);">
                    <h3 style="font-size:20px;">After</h3>
                    <pre class="nt-mini mt-4">Tool
→ deterministic reducer
→ useful context
→ LLM</pre>
                </div>
            </div>
            <x-slidewire::fragment :index="0">
                <div class="mt-6 dw-card">
                    <p class="nt-col-title">PHP handles</p>
                    <p style="margin:0;font-family:var(--font-mono);font-size:18px;color:#d7ecff;">deduplication · classification · filtering · hashing · recovery</p>
                    <p class="dw-takeaway mt-4" style="font-size:22px;">These tasks do not require a model.</p>
                </div>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>02 / hypothesis</span></footer>
    </x-slidewire::slide>

    {{-- 4 · THE EXPERIMENT --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">content/nolife-tokens</p>
            <h2 class="dw-heading-slide">A laboratory, not a framework</h2>
            <div class="mt-4 dw-grid dw-grid-4" style="gap:8px;">
                <div class="dw-chip">PHP 8.5</div>
                <div class="dw-chip">Symfony 8.1</div>
                <div class="dw-chip">Console</div>
                <div class="dw-chip">darkwood/flow</div>
            </div>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:14px;">
                <div class="dw-card">
                    <p class="nt-col-title">Not used</p>
                    <pre class="nt-mini">No embeddings
No vector DB
No MCP
No Redis
No LangChain</pre>
                </div>
                <div class="dw-card">
                    <p class="nt-col-title">Pipeline</p>
                    <pre class="nt-mini">Load → Classify
→ Measure → Optimize
→ Measure → Evaluate</pre>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>03 / experiment</span></footer>
    </x-slidewire::slide>

    {{-- 5 · CONTENT TYPES --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Content is not all the same</p>
            <h2 class="dw-heading-slide">Different noise. Different limits.</h2>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:12px;">
                <div class="dw-card" style="padding:18px;">
                    <p class="nt-col-title">Logs</p>
                    <pre class="nt-mini" style="font-size:13px;">[INFO] started
[INFO] started
[INFO] started
[ERROR] Payment failed

↓

[INFO] started ×3
[ERROR] Payment failed</pre>
                </div>
                <div class="dw-card" style="padding:18px;">
                    <p class="nt-col-title">JSON</p>
                    <pre class="nt-mini" style="font-size:13px;">id
status
error
telemetry…

↓

id
status
error</pre>
                </div>
                <div class="dw-card" style="padding:18px;">
                    <p class="nt-col-title">Git diff</p>
                    <pre class="nt-mini" style="font-size:13px;">- security check
+ …

Changed lines are
often already
the signal.</pre>
                </div>
            </div>
            <p class="dw-takeaway mt-6">Natural compression limits differ by type.</p>
        </section>
        <footer class="dw-footer"><span>04 / content-types</span></footer>
    </x-slidewire::slide>

    {{-- 6 · FIRST BENCHMARK --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">TOKEN_PIPELINE_POC · measured</p>
            <h2 class="dw-heading-slide">Volume vs signal</h2>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:18px;align-items:start;">
                <div>
                    <div class="nt-case">
                        <h3>log · −75.4%</h3>
                        <div class="nt-bar-row"><span class="nt-bar-label">raw</span><div class="nt-bar-track"><div class="nt-bar-fill" style="width:100%"></div></div><span class="nt-bar-val">4839</span></div>
                        <div class="nt-bar-row"><span class="nt-bar-label">opt</span><div class="nt-bar-track"><div class="nt-bar-fill is-opt" style="width:24.6%"></div></div><span class="nt-bar-val">1190</span></div>
                    </div>
                    <div class="nt-case">
                        <h3>diff · −24.0%</h3>
                        <div class="nt-bar-row"><span class="nt-bar-label">raw</span><div class="nt-bar-track"><div class="nt-bar-fill" style="width:65.8%"></div></div><span class="nt-bar-val">3186</span></div>
                        <div class="nt-bar-row"><span class="nt-bar-label">opt</span><div class="nt-bar-track"><div class="nt-bar-fill is-opt" style="width:50.0%"></div></div><span class="nt-bar-val">2421</span></div>
                    </div>
                    <div class="nt-case">
                        <h3>JSON · −99.0%</h3>
                        <div class="nt-bar-row"><span class="nt-bar-label">raw</span><div class="nt-bar-track"><div class="nt-bar-fill" style="width:100%"></div></div><span class="nt-bar-val">6780</span></div>
                        <div class="nt-bar-row"><span class="nt-bar-label">opt</span><div class="nt-bar-track"><div class="nt-bar-fill is-opt" style="width:1.0%"></div></div><span class="nt-bar-val">67</span></div>
                    </div>
                </div>
                <div>
                    <table class="nt-table">
                        <thead>
                            <tr><th>Case</th><th>Raw*</th><th>Opt*</th><th>Saved</th><th>Signal</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>log</td><td>4,839</td><td>1,190</td><td>75.4%</td><td>PASS</td></tr>
                            <tr><td>diff</td><td>3,186</td><td>2,421</td><td>24.0%</td><td>PASS</td></tr>
                            <tr><td>JSON</td><td>6,780</td><td>67</td><td>99.0%</td><td>PASS</td></tr>
                        </tbody>
                    </table>
                    <p class="nt-note">* estimated tokens = bytes / 4 — not provider-billed</p>
                    <p class="dw-takeaway mt-6" style="font-size:22px;">Token reduction without signal retention is vanity.</p>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>05 / first-benchmark</span></footer>
    </x-slidewire::slide>

    {{-- 7 · LOSSY PROBLEM --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The gap</p>
            <h2 class="dw-question" style="margin-top:0;font-size:clamp(36px,5vw,64px);">REMOVE FROM CONTEXT<br><span class="dw-accent">≠</span><br>DESTROY INFORMATION</h2>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:14px;">
                <div class="dw-card">
                    <h3 style="font-size:18px;color:#ff8a70;">Bad</h3>
                    <pre class="nt-mini mt-3">raw
→ compress
→ information gone</pre>
                </div>
                <div class="dw-card" style="border-color:rgba(120,220,180,.35);">
                    <h3 style="font-size:18px;">Better</h3>
                    <pre class="nt-mini mt-3">raw
       ↘ stored omission
compress
   ↓
visible context</pre>
                </div>
            </div>
            <p class="dw-takeaway mt-6">Next milestone: <span class="dw-accent">REVERSIBLE_CONTEXT_POC</span></p>
        </section>
        <footer class="dw-footer"><span>06 / reversible-idea</span></footer>
    </x-slidewire::slide>

    {{-- 8 · REVERSIBLE REFS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Reversible references</p>
            <h2 class="dw-heading-slide">Structure stays. Bulk leaves.</h2>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:12px;">
                <div class="dw-card" style="padding:16px;">
                    <p class="nt-col-title">Original</p>
                    <pre class="nt-mini" style="font-size:13px;">{
  "id": 42,
  "status": "failed",
  "error": "…",
  "telemetry": { "…6000 tok…" }
}</pre>
                </div>
                <div class="dw-card" style="padding:16px;border-color:rgba(120,220,180,.35);">
                    <p class="nt-col-title">Optimized</p>
                    <pre class="nt-mini" style="font-size:13px;">{
  "id": 42,
  "status": "failed",
  "error": "…",
  "telemetry": "#ref:ctx_31154f"
}</pre>
                </div>
            </div>
            <div class="mt-4 dw-flow" style="margin-top:20px;justify-content:center;">
                <div class="dw-node" style="font-size:16px;min-height:auto;padding:12px 16px;">#ref:ctx_31154f</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="font-size:16px;min-height:auto;padding:12px 16px;">var/context/…json</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="font-size:16px;min-height:auto;padding:12px 16px;">exact bytes</div>
            </div>
            <p class="nt-note" style="text-align:center;">Marker cost ≈ 4 estimated tokens</p>
        </section>
        <footer class="dw-footer"><span>07 / refs</span></footer>
    </x-slidewire::slide>

    {{-- 9 · UPDATED FLOW --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Flow pipeline</p>
            <h2 class="dw-heading-slide">Two invariants</h2>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:16px;align-items:start;">
                <div class="dw-flow dw-flow--vertical" style="margin-top:0;">
                    <div class="dw-pipeline-row"><span class="dw-pipeline-label">1</span><span>Load</span></div>
                    <div class="dw-pipeline-row"><span class="dw-pipeline-label">2</span><span>Classify</span></div>
                    <div class="dw-pipeline-row"><span class="dw-pipeline-label">3</span><span>MeasureRaw</span></div>
                    <div class="dw-pipeline-row"><span class="dw-pipeline-label">4</span><span>Optimize + store refs</span></div>
                    <div class="dw-pipeline-row"><span class="dw-pipeline-label">5</span><span>MeasureVisible</span></div>
                    <div class="dw-pipeline-row"><span class="dw-pipeline-label">6</span><span>Signal check</span></div>
                    <div class="dw-pipeline-row"><span class="dw-pipeline-label">7</span><span>Recovery check</span></div>
                </div>
                <div>
                    <div class="dw-card">
                        <div class="dw-vs-list">
                            <div class="dw-vs-item is-yes">SIGNAL = PASS</div>
                            <div class="dw-vs-item is-yes">RECOVERY = PASS</div>
                        </div>
                    </div>
                    <pre class="mt-4 dw-code" style="font-size:16px;">bin/console tokens:show-ref ctx_31154f</pre>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>08 / flow</span></footer>
    </x-slidewire::slide>

    {{-- 10 · REVERSIBLE BENCHMARK --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">REVERSIBLE_CONTEXT_POC · measured</p>
            <h2 class="dw-heading-slide">Out of context ≠ gone</h2>
            <table class="nt-table mt-4">
                <thead>
                    <tr>
                        <th>Case</th>
                        <th>Raw*</th>
                        <th>Visible*</th>
                        <th>Referenced*</th>
                        <th>Saved</th>
                        <th>Refs</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>log coarse</td><td>4,839</td><td>1,204</td><td>3,671</td><td>75.1%</td><td>1</td></tr>
                    <tr><td>log fine</td><td>4,839</td><td>1,202</td><td>3,670</td><td>75.2%</td><td>3</td></tr>
                    <tr><td>JSON</td><td>6,780</td><td>146</td><td>6,191</td><td>97.8%</td><td>9</td></tr>
                    <tr><td>diff</td><td>3,186</td><td>2,434</td><td>749</td><td>23.6%</td><td>1</td></tr>
                </tbody>
            </table>
            <p class="nt-note">* estimated (bytes / 4) · SIGNAL PASS · RECOVERY PASS on all rows</p>
            <p class="dw-takeaway mt-6"><span class="dw-accent">6,191</span> estimated JSON tokens moved out of active context.<br>Not destroyed. Recoverable.</p>
        </section>
        <footer class="dw-footer"><span>09 / reversible-benchmark</span></footer>
    </x-slidewire::slide>

    {{-- 11 · COARSE VS FINE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Granularity experiment · logs</p>
            <h2 class="dw-heading-slide">Granularity has a cost</h2>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:16px;">
                <div class="dw-card">
                    <p class="nt-col-title">Coarse</p>
                    <pre class="nt-mini" style="font-size:22px;">1 ref
4 marker tokens
1,204 visible</pre>
                </div>
                <div class="dw-card">
                    <p class="nt-col-title">Fine</p>
                    <pre class="nt-mini" style="font-size:22px;">3 refs
12 marker tokens
1,202 visible</pre>
                </div>
            </div>
            <p class="dw-takeaway mt-8">3× more references for ~2 estimated tokens saved.<br>For this workload: <span class="dw-accent">coarse &gt; fine</span></p>
            <p class="nt-note">Workload-dependent — structured JSON can benefit from finer, positional refs.</p>
        </section>
        <footer class="dw-footer"><span>10 / granularity</span></footer>
    </x-slidewire::slide>

    {{-- 12 · AGENTS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">For coding agents</p>
            <h2 class="dw-heading-slide">Better context. Same agents.</h2>
            <div class="mt-4 dw-flow dw-flow--vertical" style="max-width:520px;margin-left:auto;margin-right:auto;">
                <div class="dw-node">Tool output → PHP reducer</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Useful context → LLM</div>
                <div class="dw-arrow">↓ needs more?</div>
                <div class="dw-node">#ref expansion → raw source</div>
            </div>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:8px;">
                <div class="dw-chip" style="font-size:15px;">git diff</div>
                <div class="dw-chip" style="font-size:15px;">logs</div>
                <div class="dw-chip" style="font-size:15px;">tests / JSON / trees</div>
            </div>
            <p class="dw-takeaway mt-6">Not another agent interface — better context for the ones we already use.<br><span style="color:var(--dw-muted);font-size:18px;">Next: progressive disclosure (L0 overview → expand #ref → raw).</span></p>
            <p class="mt-4" style="font-size:22px;font-weight:700;">What is the minimum context the model needs<br>to make the <span class="dw-accent">correct decision</span>?</p>
        </section>
        <footer class="dw-footer"><span>11 / agents · @matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
