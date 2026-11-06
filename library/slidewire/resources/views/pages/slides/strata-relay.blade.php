<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-title',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Darkwood engineering
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Strata Relay
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<style>
            .slidewire-frame.sr, .slidewire-frame:has(.sr) { overflow: hidden; }
            article.sr, .sr.dw-slide {
                --sr-bg: var(--dw-blue);
                --sr-panel: var(--dw-blue);
                --sr-text: var(--dw-blue);
                --sr-muted: var(--dw-blue);
                --sr-cyan: var(--dw-blue);
                --sr-amber: var(--dw-blue);
                --sr-stone: var(--dw-blue);
                --sr-line: var(--dw-blue);
                min-height: 100%; height: 100%; max-height: 100%;
            }
            .slidewire-frame.sr::before, .slidewire-frame.sr::after,
            article.sr::before, article.sr::after, .sr.dw-slide::before, .sr.dw-slide::after { display:none !important; content:none !important; }
            .sr .dw-wrap { width:min(1180px,100%); min-height:0; height:100%; padding:28px 0 20px; justify-content:center; }
            .sr .sr-top { justify-content:flex-start; padding-top:20px; }
            .sr-kicker { margin:0; font-weight:700; letter-spacing:.16em; text-transform:uppercase; }
            .sr-title { margin:12px 0 0; max-width:760px; line-height:.94; font-weight:700; }
            .sr-heading { margin:9px 0 0; max-width:1000px; line-height:1; font-weight:700; }
            .sr-lead { margin:20px 0 0; max-width:760px; line-height:1.28; }
            .sr-rule { width:190px; height:3px; margin:22px 0; }
            .sr-grid-2 { display:grid; grid-template-columns:1.1fr .9fr; gap:50px; align-items:center; width:100%; }
            .sr-photo { position:relative; justify-self:end; width:min(355px,31vw); max-height:720px; overflow:hidden; border:var(--dw-border-width) solid var(--sr-line); }
            .sr-photo img { display:block; width:100%; height:100%; object-fit:cover; object-position:top; }
            .sr-photo::after { content:''; position:absolute; inset:0; pointer-events:none; }
            .sr-list { margin:28px 0 0; padding:0; list-style:none; display:grid; gap:16px; }
            .sr-list li { padding-left:22px; border-left:var(--dw-border-width) solid var(--sr-cyan); line-height:1.25; }
            .sr-stage { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-top:30px; }
            .sr-column { position:relative; height:270px; display:flex; align-items:flex-end; justify-content:center; }
            .sr-block { width:86%; min-height:50px; border:var(--dw-border-width) solid var(--dw-blue); }
            .sr-block.h1 { height:62px; } .sr-block.h3 { height:142px; } .sr-block.h5 { height:226px; }
            .sr-column label { position:absolute; bottom:-34px; }
            .sr-bound { margin:54px auto 0; display:flex; justify-content:center; gap:54px; }
            .sr-cross { display:grid; grid-template-columns:repeat(3,86px); grid-template-rows:repeat(3,86px); gap:8px; justify-content:center; margin-top:26px; }
            .sr-cell { display:grid; place-items:center; border:var(--dw-border-width) solid var(--sr-line); font-family:var(--font-mono); font-size:18px; }
            .sr-cross .c2 { grid-column:2; grid-row:1; } .sr-cross .c4 { grid-column:1; grid-row:2; }
            .sr-cross .c5 { grid-column:2; grid-row:2; } .sr-cross .c6 { grid-column:3; grid-row:2; }
            .sr-cross .c8 { grid-column:2; grid-row:3; }
            .sr-cycle { display:flex; align-items:center; justify-content:center; gap:18px; margin-top:34px; }
            .sr-level { min-width:160px; padding:18px; text-align:center; border:var(--dw-border-width) solid var(--sr-line); }
            .sr-level b { display:block; }
            .sr-pipeline { display:grid; grid-template-columns:1fr 44px 1fr 44px 1fr 44px 1fr; gap:10px; align-items:center; margin-top:44px; }
            .sr-step { min-height:150px; display:grid; align-content:center; gap:8px; padding:18px; border-top:var(--dw-border-width) solid var(--sr-cyan); }
            .sr-step:nth-of-type(5) { border-top-color:var(--sr-amber); } .sr-step span { line-height:1.3; }
            .sr-arrow { text-align:center; }
            .sr-proof { margin:30px auto 0; max-width:880px; text-align:center; }
            .sr-compare { display:grid; grid-template-columns:1fr 1fr; gap:42px; margin-top:38px; }
            .sr-measure { padding:10px 0 18px; border-bottom:var(--dw-border-width) solid var(--sr-line); }
            .sr-measure b { display:block; }
            .sr-bar { height:18px; margin-top:12px; overflow:hidden; }
            .sr-bar i { display:block; height:100%; }
            .sr-code { margin:30px 0 0; padding:24px 28px; border:var(--dw-border-width) solid var(--sr-line); font-family:var(--font-mono); font-size:clamp(14px,1.25vw,18px); line-height:1.5; white-space:pre-wrap; }
            .sr-architecture { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-top:34px; }
            .sr-layer { min-height:170px; padding:24px; border-top:var(--dw-border-width) solid var(--sr-cyan); }
            .sr-layer:nth-child(2) { border-top-color:var(--sr-amber); }
            .sr-layer b { display:block; } .sr-layer p { line-height:1.35; }
            .sr-metrics { display:grid; grid-template-columns:repeat(3,1fr); gap:30px; margin-top:38px; }
            .sr-metric { padding:24px 0; border-top:var(--dw-border-width) solid var(--sr-cyan); }
            .sr-metric:nth-child(2n) { border-top-color:var(--sr-amber); }
            .sr-metric strong { display:block; line-height:1; white-space:nowrap; }
            .sr-metric span { display:block; margin-top:10px; }
            .sr-message { display:grid; grid-template-columns:1fr 210px 1fr; gap:28px; align-items:center; margin-top:38px; }
            .sr-app { min-height:210px; display:grid; align-content:center; padding:28px; border:var(--dw-border-width) solid var(--sr-line); } .sr-app p { line-height:1.35; }
            .sr-contract { text-align:center; line-height:1.45; }
            .sr-ids { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-top:38px; }
            .sr-id { min-height:180px; padding:24px; border-left:var(--dw-border-width) solid var(--sr-cyan); }
            .sr-id:nth-child(2) { border-left-color:var(--sr-amber); }
            .sr-id code { font-size:20px; } .sr-id p { line-height:1.35; }
            .sr-principles { display:grid; grid-template-columns:repeat(2,1fr); gap:24px 42px; margin-top:38px; }
            .sr-principle { padding:16px 0; border-bottom:var(--dw-border-width) solid var(--sr-line); }
            .sr-principle b { display:block; } .sr-principle p { margin:7px 0 0; }
            .sr-cta { display:inline-block; margin-top:30px; text-decoration:none; border-bottom:var(--dw-border-width) solid var(--dw-blue); }
            .sr-mosaic { display:grid; grid-template-columns:repeat(5,42px); gap:8px; transform:rotate(-8deg); }
            .sr-mosaic span { width:42px; height:42px; border:var(--dw-border-width) solid var(--sr-line); }
            .sr-footer { position:absolute; right:26px; bottom:18px; letter-spacing:.08em; }
            @media (max-height:700px) { .sr .dw-wrap{padding:14px 0 10px}.sr-photo{max-height:610px}.sr-stage{margin-top:16px}.sr-column{height:220px}.sr-block.h5{height:180px}.sr-metrics{margin-top:22px}.sr-metric{padding:14px 0} }
        </style>
        <section class="dw-wrap">
            <div class="sr-grid-2">
                <div>


                    <div class="sr-rule"></div>
                    <p class="sr-lead">Procedural terrain, deterministic mutations and interoperable messages with Symfony 8.2</p>
                </div>
                <figure class="sr-photo"><img src="/images/strata-relay/cover.png" alt="Strata Relay isometric terrain and crystal anchors"></figure>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The game concept
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The player moves the terrain
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="sr-grid-2">
                <div>


                    <ul class="sr-list">
                        <li><strong>Tap one column</strong> to pulse a local cross</li>
                        <li><strong>Read the new contour</strong> before spending the next move</li>
                        <li><strong>Connect both crystals</strong> through middle-height terrain</li>
                    </ul>
                </div>
                <figure class="sr-photo"><img src="/images/strata-relay/screenshot-1.png" alt="Initial Strata Relay board"></figure>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
A compact isometric world
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
25 columns inside a 5 × 5 × 5 bound
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap sr-top">


            <div class="sr-stage" aria-label="Five example terrain columns at heights one, three and five">
                <div class="sr-column"><div class="sr-block h1"></div><label>height 1</label></div>
                <div class="sr-column"><div class="sr-block h3 resonant"></div><label>height 3</label></div>
                <div class="sr-column"><div class="sr-block h5"></div><label>height 5</label></div>
                <div class="sr-column"><div class="sr-block h3 resonant"></div><label>height 3</label></div>
                <div class="sr-column"><div class="sr-block h1"></div><label>height 1</label></div>
            </div>
            <div class="sr-bound"><span><strong>5 × 5</strong><br>interactive heightfield</span><span><strong>125</strong><br>logical voxel positions</span><span><strong>1 camera</strong><br>fixed portrait view</span></div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Terrain mutations
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
One pulse changes at most five columns
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sr-grid-2" style="margin-top:28px">
                <div>
                    <div class="sr-cross" aria-label="Local cross affected by one terrain pulse">
                        <div class="sr-cell hit c2">+1</div><div class="sr-cell hit c4">+1</div><div class="sr-cell hit center c5">tap</div><div class="sr-cell hit c6">+1</div><div class="sr-cell hit c8">+1</div>
                    </div>
                    <p class="sr-proof">Only mutable cells inside the board advance.</p>
                </div>
                <div>
                    <div class="sr-cycle">
                        <div class="sr-level"><b>1</b><span>low</span></div><div class="sr-cycle-mark">›</div>
                        <div class="sr-level"><b>3</b><span>resonant</span></div><div class="sr-cycle-mark">›</div>
                        <div class="sr-level"><b>5</b><span>high</span></div>
                    </div>
                    <p class="sr-proof">The wrap from high to low makes each pulse reversible.</p>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Procedural generation
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Solvability comes from construction
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sr-pipeline">
                <div class="sr-step"><b>Seed + stage</b><span>One deterministic input recreates the same puzzle.</span></div><div class="sr-arrow">›</div>
                <div class="sr-step"><b>Valid route</b><span>A middle-height path links source and target.</span></div><div class="sr-arrow">›</div>
                <div class="sr-step"><b>Local scramble</b><span>Known pulses add decoys and disruption.</span></div><div class="sr-arrow">›</div>
                <div class="sr-step"><b>Witness replay</b><span>The same public rule verifies a route to victory.</span></div>
            </div>
            <p class="sr-proof">The witness proves solvability. It does not claim the shortest or only solution.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Small rules, many situations
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Source compactness and delivery size differ
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sr-compare">
                <div>
                    <div class="sr-measure"><b>22,202 bytes</b><span>runtime GDScript</span><div class="sr-bar"><i style="width:18%"></i></div></div>
                    <div class="sr-measure"><b>0 imported</b><span>gameplay art or audio assets</span><div class="sr-bar"><i style="width:3%"></i></div></div>
                </div>
                <div>
                    <div class="sr-measure"><b>39.5 MB</b><span>raw WebAssembly runtime</span><div class="sr-bar is-amber"><i style="width:100%"></i></div></div>
                    <div class="sr-measure"><b>Unmeasured</b><span>isolated game-only runtime memory</span><div class="sr-bar is-amber"><i style="width:0%"></i></div></div>
                </div>
            </div>
            <p class="sr-proof">Procedural generation reduces authored level data. It does not automatically reduce every other metric.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Godot implementation
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
State first, animation second
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap sr-top">



@endverbatim
@php
$snippet = <<<'SW_CODE_6_0'
for changed in affected:
    var before := int(next["levels"][index_of(changed)])
    var after := (before + 1) % 3
    next["levels"][index_of(changed)] = after
    events.append({"cell": changed, "before": before, "after": after})
SW_CODE_6_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="sr-architecture">
                <div class="sr-layer"><b>Rules</b><p>Generate, mutate and evaluate the authoritative board.</p></div>
                <div class="sr-layer"><b>Events</b><p>Describe the local columns that changed during one turn.</p></div>
                <div class="sr-layer"><b>View</b><p>Interpolate heights and redraw the derived signal path.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Performance and validation
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Measured results from the completed game
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sr-metrics">
                <div class="sr-metric"><strong>557</strong><span>rule assertions<br>zero failures</span></div>
                <div class="sr-metric"><strong>5,000</strong><span>generated puzzles<br>witness replay passed</span></div>
                <div class="sr-metric"><strong>80.564 µs</strong><span>average generation<br>measured host</span></div>
                <div class="sr-metric"><strong>12.346 µs</strong><span>average local update<br>including state copy</span></div>
                <div class="sr-metric"><strong>686</strong><span>runtime GDScript lines</span></div>
                <div class="sr-metric"><strong>25</strong><span>cells examined at most<br>during connectivity</span></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle sr-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
A visible consequence
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Connectivity turns terrain into the objective
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Middle-height columns carry the cyan signal. Victory arrives when one connected component reaches both crystal anchors.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="sr-grid-2">
                <div>



                    <p class="sr-proof" style="text-align:left;margin-left:0">The board evolves locally. The signal is derived from the resulting state.</p>
                </div>
                <figure class="sr-photo"><img src="/images/strata-relay/screenshot-2.png" alt="Completed Strata Relay board with a connected cyan path"></figure>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Symfony 8.2 cross-application messaging
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A contract can cross the boundary
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sr-message">
                <div class="sr-app"><b>Application A</b><p>Owns its local simulation and publishes one accepted fact.</p></div>
                <div class="sr-contract">game.session<br>completed.v1</div>
                <div class="sr-app"><b>Application B</b><p>Maps the stable type to its own schema and behavior.</p></div>
            </div>
            <p class="sr-proof">Hypothetical example. Strata Relay remains offline and does not emit this event.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Message identity
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Three identifiers answer three questions
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sr-ids">
                <div class="sr-id"><code>message_id</code><p>Which logical event is this, across delivery attempts?</p></div>
                <div class="sr-id"><code>correlation_id</code><p>Which wider workflow groups this event with related messages?</p></div>
                <div class="sr-id"><code>causation_id</code><p>Which earlier command or event produced this one?</p></div>
            </div>

@endverbatim
@php
$snippet = <<<'SW_CODE_10_0'
#[AsMessage(serializedTypeName: 'game.session.completed.v1')]
final readonly class GameSessionCompleted { /* explicit versioned fields */ }
SW_CODE_10_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | 12" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sr" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sr-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sr-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Conclusion
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Small rules, explicit boundaries
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="sr-grid-2">
                <div>


                    <div class="sr-principles">
                        <div class="sr-principle"><b>Deterministic worlds</b><p>A seed reconstructs the same playable state.</p></div>
                        <div class="sr-principle"><b>Local consequences</b><p>One action changes a bounded, visible neighborhood.</p></div>
                        <div class="sr-principle"><b>Derived signals</b><p>Connectivity follows the terrain that actually exists.</p></div>
                        <div class="sr-principle"><b>Versioned contracts</b><p>Applications exchange facts without sharing internals.</p></div>
                    </div>
                    <a class="sr-cta" href="https://darkwaar.com/games/strata-relay">darkwaar.com/games/strata-relay</a>
                </div>
                <div class="sr-mosaic" aria-label="Abstract five by five terrain mosaic">
                    <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | 12" />
</x-darkwood.slide>
</x-darkwood.deck>
