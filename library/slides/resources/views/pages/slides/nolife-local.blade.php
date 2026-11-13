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
Darkwood · Symfony · PHP FFI · Flow
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
When AI Becomes a Library
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
The Nolife Local Experiment<br><span class="dw-accent">Depth Anything 3 + Symfony + PHP FFI + Darkwood Flow</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<style>
    .nl-table { width: 100%; border-collapse: collapse; margin-top: 24px; }
    .nl-table th, .nl-table td { padding: 10px 14px; text-align: right; border-bottom: var(--dw-border-width) solid var(--dw-line); }
    .nl-table th:first-child, .nl-table td:first-child { text-align: left; }
    .nl-table th { letter-spacing: .08em; text-transform: uppercase; }
    .nl-table tr.is-hot td { font-weight: 700; }
    .nl-table tr.is-warn td { font-weight: 700; }
    .nl-mini { line-height: 1.5; white-space: pre-wrap; margin: 0; }
    .nl-stat { text-align: center; padding: 22px 18px; }
    .nl-stat strong { display: block; line-height: 1; }
    .nl-stat span { display: block; margin-top: 8px; }
    .nl-shot { border: var(--dw-border-width) solid var(--dw-line); border-radius: 8px; max-height: min(42vh, 380px); width: auto; max-width: 100%; object-fit: contain; }
    .nl-shot-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 20px; align-items: start; }
    .nl-note { margin-top: 14px; }
    .dw-heading-slide { max-width: 980px; line-height: 1.05; font-weight: 700; }
    .dw-wrap--top { justify-content: flex-start; padding-top: 56px; }
</style>
@endverbatim
@verbatim
<section class="dw-wrap">



        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
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
The default reflex
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
We use LLMs for everything.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-flow dw-flow--vertical" style="max-width:360px;margin-top:28px">
                <div class="dw-node">problem</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">large model</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">prompt</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">text</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | reflex" />
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
The question
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What if the task already has a <span class="dw-accent">specialized model</span>?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-flow dw-flow--vertical" style="max-width:420px;margin-top:32px">
                <div class="dw-node">problem</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">specialized model</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">structured computation</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | question" />
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
            <p class="dw-kicker">Depth is not language</p>
            <div class="dw-split" style="margin-top:20px">
                <div class="dw-split-panel">
                    <h3>General LLM</h3>
                    <p class="nl-mini">"The mountains are far away."</p>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Depth Anything 3</h3>
                    <p class="nl-mini">504 × 280<br>depth values</p>
                    <p class="nl-note">relative units · not meters</p>
                </div>
            </div>
            <img src="/images/nolife-local/04-depth-mountains.png" alt="Turbo colormap depth" class="nl-shot mt-6" style="max-height:28vh">
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | depth" />
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
            <p class="dw-kicker">Depth Anything 3</p>
            <div class="dw-flow dw-flow--vertical" style="max-width:480px;margin-top:16px">
                <div class="dw-node">RGB image</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Depth Anything 3</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-grid dw-grid-2" style="gap:10px">
                    <div class="dw-chip">depth</div>
                    <div class="dw-chip">confidence</div>
                    <div class="dw-chip">camera</div>
                    <div class="dw-chip">3D / GLB</div>
                </div>
            </div>
            <p class="dw-takeaway mt-6">DA3-BASE · relative depth · <span class="dw-accent">not meters</span></p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | da3" />
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
            <p class="dw-kicker">Research → runtime</p>
            <div class="dw-flow dw-flow--vertical" style="max-width:520px;margin-top:20px">
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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | runtime" />
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
            <p class="dw-kicker">What “specialized” means</p>
            <div class="dw-split" style="margin-top:24px">
                <div class="dw-split-panel is-warn">
                    <h3>Not this project</h3>
                    <div class="dw-vs-list" style="margin-top:0">
                        <div class="dw-vs-item is-no">fine-tuning</div>
                        <div class="dw-vs-item is-no">LoRA</div>
                        <div class="dw-vs-item is-no">training from scratch</div>
                    </div>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>This project</h3>
                    <div class="dw-vs-list" style="margin-top:0">
                        <div class="dw-vs-item is-yes">purpose-trained model</div>
                        <div class="dw-vs-item is-yes">local native runtime</div>
                        <div class="dw-vs-item is-yes">quantized representation</div>
                    </div>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | specialized" />
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
The unusual constraint
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
No Python service.<br>No cloud AI API.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-flow dw-flow--vertical" style="max-width:400px;margin-top:28px">
                <div class="dw-node">Symfony</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">PHP FFI</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">native model</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | php" />
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
Architecture
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Symfony hosts the boundary.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-embed" style="max-width:100%">
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  B["Browser"]
  S["Symfony"]
  F["Darkwood Flow"]
  N["NativeDepthBridge"]
  FFI["PHP FFI"]
  L["libdepthanything"]
  G["ggml / Metal"]
  D["Depth Anything 3"]
  B --&gt; S --&gt; F --&gt; N --&gt; FFI --&gt; L --&gt; G --&gt; D</pre>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | architecture" />
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
            <p class="dw-kicker">Ownership</p>
            <div class="dw-split" style="margin-top:16px">
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
            <p class="dw-takeaway mt-4">PHP does <em>not</em> perform matrix multiplication.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | ownership" />
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
            <p class="dw-kicker">PHP FFI</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_10_0'
$ffi->da_capi_depth_dense(
    $ctx, $imagePath, …
);
SW_CODE_10_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="dw-flow dw-flow--vertical" style="max-width:380px;margin-top:20px">
                <div class="dw-node">native pointer</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">PHP array</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">da_capi_free_floats</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | ffi" />
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
            <p class="dw-kicker">Application pipeline</p>
            <div class="dw-flow dw-flow--vertical" style="max-width:320px;margin-top:12px">
                <div class="dw-node" style="min-height:52px">Upload</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node" style="min-height:52px">Validate → Infer → Colormap</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node" style="min-height:52px">GLB → Result</div>
            </div>
            <img src="/images/nolife-local/01-upload-page.png" alt="Upload UI" class="nl-shot mt-4" style="max-height:32vh;float:right;margin-left:24px">
            <p class="dw-takeaway" style="clear:both">Darkwood Flow · explicit steps · per-step timings</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | pipeline" />
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
Observability
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Flow did not make inference faster.<br>It made the pipeline <span class="dw-accent">visible</span>.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <img src="/images/nolife-local/05-flow-step-timings.png" alt="Flow step timings" class="nl-shot mt-4">
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | flow" />
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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | anomaly" />
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
Before
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The hidden second inference
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-split" style="margin-top:24px">
                <div class="dw-split-panel is-warn">

@endverbatim
@php
$snippet = <<<'SW_CODE_14_0'
image
 ├── inference → depth
 └── inference → GLB
SW_CODE_14_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel">
                    <p class="nl-mini">da_capi_depth_dense
da_capi_export_glb
→ depth_pose_native() × 2</p>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="15 | double pass" />
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
After
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
One inference, several outputs
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_15_0'
image
  ↓
one inference
  ↓
native result
 ├── depth
 ├── confidence
 ├── camera
 └── GLB
SW_CODE_15_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="nl-note">da_capi_export_glb_cached · ABI v11</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="16 | fix" />
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
            <p class="dw-kicker">Measured impact</p>
            <div class="dw-grid dw-grid-2" style="margin-top:20px">
                <div class="dw-card nl-stat is-warn">
                    <strong>~5.26 s</strong>
                    <span>2 native inferences</span>
                </div>
                <div class="dw-card nl-stat is-ok">
                    <strong>~3.27 s</strong>
                    <span>1 native inference · GLB ~19 ms</span>
                </div>
            </div>
            <img src="/images/nolife-local/04-original-vs-depth.png" alt="Original vs depth" class="nl-shot mt-4" style="max-height:30vh">
            <p class="nl-note">504×336 · q4_k · warm · Apple M4 / Metal</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="17 | impact" />
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
Measurement discipline
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Different tensor size ≠ regression
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <table class="nl-table">
                <thead><tr><th>Source</th><th>Processed</th><th>Infer median</th></tr></thead>
                <tbody>
                    <tr><td>1280×720</td><td>504×280</td><td>~2495 ms</td></tr>
                    <tr><td>1024×680</td><td>504×336</td><td>~3.0–3.2 s</td></tr>
                </tbody>
            </table>
            <p class="dw-takeaway mt-4">Compare processed resolution — not headline numbers alone.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="18 | resolution" />
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
Quantization
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
4× smaller.<br>Not 4× faster.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-grid dw-grid-2" style="margin-top:24px">
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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="19 | quant" />
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
            <p class="dw-kicker">Local means local</p>
            <div class="dw-vs-list" style="margin-top:24px">
                <div class="dw-vs-item is-yes">No remote AI inference</div>
                <div class="dw-vs-item is-yes">No cloud API at runtime</div>
                <div class="dw-vs-item is-yes">GGUF on disk · Metal on device</div>
                <div class="dw-vs-item is-yes">Demo runtime works with networking disabled</div>
            </div>
            <img src="/images/nolife-local/06-glb-viewer.png" alt="GLB viewer" class="nl-shot mt-4" style="max-height:28vh">
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="20 | local" />
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
AI becomes infrastructure
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The model starts to look like a <span class="dw-accent">dependency</span>.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_20_0'
php bin/console app:depth-check
php bin/console app:depth-bench
php bin/console app:depth-stress
php bin/console app:depth-cleanup
SW_CODE_20_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="nl-note">Flow / PHP overhead ~114 ms · infer dominates</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="21 | infra" />
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
            <p class="dw-kicker">Development loop</p>
            <div class="dw-split" style="margin-top:20px">
                <div class="dw-split-panel is-warn">
                    <h3>Early</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_21_0'
idea
→ ask agent
→ inspect
SW_CODE_21_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Later</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_21_1'
observe → evidence
→ issue → agent
→ implement → measure
SW_CODE_21_1;
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
<x-darkwood.footer label="22 | loop" />
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
Issue-first
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The valuable part was the <span class="dw-accent">problem definition</span>.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_22_0'
Observation:
Two native inference passes.

Evidence:
Infer ≈ 2.6 s · GLB ≈ 2.5 s

Constraint:
One inference per request.

Expected:
Reuse native dense result.

Non-goal:
No Messenger / concurrency layer.
SW_CODE_22_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="23 | issue" />
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
Synthesis
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Reduce unnecessary generality.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-split" style="margin-top:20px">
                <div class="dw-split-panel">
                    <h3>Runtime</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_23_0'
general model
        ↓
specialized model
SW_CODE_23_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel">
                    <h3>Development</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_23_1'
general prompt
        ↓
well-defined issue
SW_CODE_23_1;
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
<x-darkwood.footer label="24 | synthesis" />
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
            <p class="dw-kicker">What I learned</p>
            <div class="dw-grid dw-grid-2" style="margin-top:20px;gap:12px">
                <div class="dw-chip" style="text-align:left;padding:16px 20px">PHP FFI was enough</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px">Native infer dominates cost</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px">Observability found the real bug</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px">q4_k saved size, not latency here</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px">Context &gt; code volume</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px">Evidence beats speculation</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="25 | learned" />
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
Conclusion
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The task should determine the <span class="dw-accent">model</span>, the <span class="dw-accent">architecture</span>, and the <span class="dw-accent">prompt</span>.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw-flow dw-flow--vertical" style="max-width:520px;margin-top:28px">
                <div class="dw-node">Depth Anything 3 · specialized computation</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Darkwood Flow · observable pipeline</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">Issue-first brief · specialized context</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="26 | conclusion" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Resources</p>
            <div class="mt-10 dw-grid dw-grid-2" style="gap:16px">
                <div class="dw-card">
                    <h3>Source</h3>
                    <p>github.com/matyo91/nolife-local</p>
                </div>
                <div class="dw-card">
                    <h3>Slides</h3>
                    <p>github.com/matyo91/slidewire</p>
                </div>
                <div class="dw-card" style="grid-column:1/-1">
                    <h3>Upstream</h3>
                    <p>github.com/mudler/depth-anything.cpp</p>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="@matyo91 · Darkwood" />
</x-darkwood.slide>
</x-darkwood.deck>
