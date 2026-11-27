<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="cover" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--cover',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-title dw-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="cover" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Darkwood · Symfony · Flow · context
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Nolife Tokens
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Shrinking LLM context<br><span class="dw-accent">without destroying recoverability</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<style>
    .nt-bar-row { display: flex; align-items: center; gap: 12px; margin: 8px 0; }
    .nt-bar-label { width: 52px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .nt-bar-track { flex: 1; height: 18px; border-radius: 4px; overflow: hidden; border: var(--dw-border-width) solid var(--dw-line); }
    .nt-bar-fill { height: 100%; border-radius: 3px; }
    .nt-bar-fill.is-opt { opacity: 1; }
    .nt-bar-val { width: 64px; text-align: right; font-weight: 700; }
    .nt-case { margin-bottom: 14px; }
    .nt-case h3 { font-weight: 700; margin-bottom: 4px; }
    .nt-table { width: 100%; border-collapse: collapse; }
    .nt-table th, .nt-table td { padding: 10px 12px; text-align: right; border-bottom: var(--dw-border-width) solid var(--dw-line); }
    .nt-table th:first-child, .nt-table td:first-child { text-align: left; }
    .nt-table th { letter-spacing: .06em; text-transform: uppercase; font-weight: 700; }
    .nt-note { margin-top: 14px; }
    .nt-mini { line-height: 1.45; white-space: pre-wrap; }
    .nt-col-title { letter-spacing: .08em; text-transform: uppercase; font-weight: 700; margin-bottom: 10px; }
</style>
@endverbatim
@verbatim
<section class="dw-wrap">



            <p class="mt-10">Symfony + Flow experiment</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The problem</p>
            <div class="dw-flow dw-flow--vertical" style="margin-top:18px;max-width:420px">
                <div class="dw-node">Tool</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">50 KB stdout</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">LLM context</div>
            </div>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px">
                <div class="dw-chip">logs</div>
                <div class="dw-chip">JSON telemetry</div>
                <div class="dw-chip">git diffs</div>
                <div class="dw-chip">repo trees</div>
                <div class="dw-chip">compiler output</div>
                <div class="dw-chip">test output</div>
            </div>
            <p class="dw-takeaway mt-8">Large context windows make it possible to send everything.<br>They do not make it wise.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | problem" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Hypothesis
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Do more deterministic work<br>before the LLM.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-2" style="gap:14px">
                <div class="dw-card">
                    <h3>Before</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_2_0'
Tool
→ raw output
→ LLM
SW_CODE_2_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-card">
                    <h3>After</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_2_1'
Tool
→ deterministic reducer
→ useful context
→ LLM
SW_CODE_2_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="mt-6 dw-card">
                    <p class="nt-col-title">PHP handles</p>
                    <p style="margin:0">deduplication · classification · filtering · hashing · recovery</p>
                    <p class="dw-takeaway mt-4">These tasks do not require a model.</p>
                </div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | hypothesis" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
content/nolife-tokens
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A laboratory, not a framework
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-grid dw-grid-4" style="gap:8px">
                <div class="dw-chip">PHP 8.5</div>
                <div class="dw-chip">Symfony 8.1</div>
                <div class="dw-chip">Console</div>
                <div class="dw-chip">darkwood/flow</div>
            </div>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:14px">
                <div class="dw-card">
                    <p class="nt-col-title">Not used</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_3_0'
No embeddings
No vector DB
No MCP
No Redis
No LangChain
SW_CODE_3_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-card">
                    <p class="nt-col-title">Pipeline</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_3_1'
Load → Classify
→ Measure → Optimize
→ Measure → Evaluate
SW_CODE_3_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | experiment" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Content is not all the same
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Different noise. Different limits.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-grid dw-grid-3" style="gap:12px">
                <div class="dw-card" style="padding:18px">
                    <p class="nt-col-title">Logs</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_4_0'
[INFO] started
[INFO] started
[INFO] started
[ERROR] Payment failed

↓

[INFO] started ×3
[ERROR] Payment failed
SW_CODE_4_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-card" style="padding:18px">
                    <p class="nt-col-title">JSON</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_4_1'
id
status
error
telemetry…

↓

id
status
error
SW_CODE_4_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-card" style="padding:18px">
                    <p class="nt-col-title">Git diff</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_4_2'
- security check
+ …

Changed lines are
often already
the signal.
SW_CODE_4_2;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <p class="dw-takeaway mt-6">Natural compression limits differ by type.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | content-types" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
TOKEN_PIPELINE_POC · measured
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Volume vs signal
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-grid dw-grid-2" style="gap:18px;align-items:start">
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
                    <p class="dw-takeaway mt-6">Token reduction without signal retention is vanity.</p>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | first-benchmark" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-question',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The gap
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
REMOVE FROM CONTEXT<br><span class="dw-accent">≠</span><br>DESTROY INFORMATION
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-grid dw-grid-2" style="gap:14px">
                <div class="dw-card">
                    <h3>Bad</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_6_0'
raw
→ compress
→ information gone
SW_CODE_6_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-card">
                    <h3>Better</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_6_1'
raw
       ↘ stored omission
compress
   ↓
visible context
SW_CODE_6_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <p class="dw-takeaway mt-6">Next milestone: <span class="dw-accent">REVERSIBLE_CONTEXT_POC</span></p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | reversible-idea" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Reversible references
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Structure stays. Bulk leaves.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-grid dw-grid-2" style="gap:12px">
                <div class="dw-card" style="padding:16px">
                    <p class="nt-col-title">Original</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_7_0'
{
  "id": 42,
  "status": "failed",
  "error": "…",
  "telemetry": { "…6000 tok…" }
}
SW_CODE_7_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-card" style="padding:16px">
                    <p class="nt-col-title">Optimized</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_7_1'
{
  "id": 42,
  "status": "failed",
  "error": "…",
  "telemetry": "#ref:ctx_31154f"
}
SW_CODE_7_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <div class="mt-4 dw-flow" style="margin-top:20px;justify-content:center">
                <div class="dw-node" style="min-height:auto;padding:12px 16px">#ref:ctx_31154f</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:auto;padding:12px 16px">var/context/…json</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node" style="min-height:auto;padding:12px 16px">exact bytes</div>
            </div>
            <p class="nt-note" style="text-align:center">Marker cost ≈ 4 estimated tokens</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | refs" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Flow pipeline
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Two invariants
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-grid dw-grid-2" style="gap:16px;align-items:start">
                <div class="dw-flow dw-flow--vertical" style="margin-top:0">
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

@endverbatim
@php
$snippet = <<<'SW_CODE_8_0'
bin/console tokens:show-ref ctx_31154f
SW_CODE_8_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | flow" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
REVERSIBLE_CONTEXT_POC · measured
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Out of context ≠ gone
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | reversible-benchmark" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Granularity experiment · logs
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Granularity has a cost
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-2" style="gap:16px">
                <div class="dw-card">
                    <p class="nt-col-title">Coarse</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_10_0'
1 ref
4 marker tokens
1,204 visible
SW_CODE_10_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-card">
                    <p class="nt-col-title">Fine</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_10_1'
3 refs
12 marker tokens
1,202 visible
SW_CODE_10_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <p class="dw-takeaway mt-8">3× more references for ~2 estimated tokens saved.<br>For this workload: <span class="dw-accent">coarse &gt; fine</span></p>
            <p class="nt-note">Workload-dependent — structured JSON can benefit from finer, positional refs.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | granularity" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
For coding agents
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Better context. Same agents.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-flow dw-flow--vertical" style="max-width:520px;margin-left:auto;margin-right:auto">
                <div class="dw-node">Tool output → PHP reducer</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Useful context → LLM</div>
                <div class="dw-arrow">↓ needs more?</div>
                <div class="dw-node">#ref expansion → raw source</div>
            </div>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:8px">
                <div class="dw-chip">git diff</div>
                <div class="dw-chip">logs</div>
                <div class="dw-chip">tests / JSON / trees</div>
            </div>
            <p class="dw-takeaway mt-6">Not another agent interface — better context for the ones we already use.<br><span>Next: progressive disclosure (L0 overview → expand #ref → raw).</span></p>
            <p class="mt-4" style="font-weight:700">What is the minimum context the model needs<br>to make the <span class="dw-accent">correct decision</span>?</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | agents · @matyo91" />
</x-darkwood.slide>
</x-darkwood.deck>
