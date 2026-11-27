<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'title',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header dw20-copy',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw20-sub',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Darkwaar20
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
From noise<br>to a playable world
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Deterministic generation as gameplay
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<style>
    .slidewire-frame:has(.dw20) { overflow: hidden; }
    article.dw20 { min-height: 100%; height: 100%; max-height: 100%; }
    .dw20 .dw-wrap { width: min(1180px, 100%); min-height: 0; height: 100%; padding: 28px 0 18px; justify-content: center; }
    .dw20-kicker { font-weight: 700; letter-spacing: .16em; text-transform: uppercase; }
    .dw20-title { margin: 0; line-height: .92; font-weight: 700; }
    .dw20-sub { margin: 16px 0 0; }
    .dw20-line { margin: 8px 0 0; line-height: 1.05; font-weight: 700; }
    .dw20 [data-board], .dw20 [data-patch] { display: grid; gap: 3px; }
    .dw20-cell { position: relative; width: var(--cell, 28px); height: var(--cell, 28px); display: grid; grid-template-columns: 1fr 1fr; }
    .dw20-cell i { display: block; }
    .dw20-cell.is-exit::before { content: ""; position: absolute; inset: 27%; z-index: 1; }
    .dw20-cell.is-player::after { content: ""; position: absolute; inset: 30%; border-radius: 50%; z-index: 2; }
    .dw20-cell.is-reach { outline: var(--dw-border-width) solid var(--dw-blue); outline-offset: -3px; }
    .dw20-row { display: flex; align-items: center; justify-content: space-between; gap: 28px; }
    .dw20-facts { display: grid; gap: 14px; }
    .dw20-fact { border: var(--dw-border-width) solid var(--dw-line); padding: 16px 18px; min-width: 180px; }
    .dw20-fact b { display: block; line-height: 1; }
    .dw20-fact span { letter-spacing: .12em; text-transform: uppercase; }
    .dw20-pair { display: flex; justify-content: center; align-items: center; gap: 28px; }
    .dw20-side { transition: box-shadow .3s linear; }
    .dw20-caption { position: relative; min-height: 3.2rem; margin-top: 14px; }
    .dw20-caption .slidewire-fragment { position: absolute; left: 0; right: 0; top: 0; margin: 0; pointer-events: none; opacity: 0 !important; }
    .dw20-caption .slidewire-fragment-visible:not(:has(~ .slidewire-fragment-visible)) { opacity: 1 !important; }
    .dw20-caption p, .dw20-note { margin: 0; font-weight: 700; }
    .dw20-cross { display: grid; grid-template-areas: ". n ." "w c e" ". s ."; gap: 10px; justify-content: center; margin-top: 12px; }
    .dw20-cross b { display: grid; place-items: center; width: 92px; height: 72px; border: var(--dw-border-width) solid var(--dw-line); }
    .dw20-cross [data-role="n"] { grid-area: n; } .dw20-cross [data-role="w"] { grid-area: w; }
    .dw20-cross [data-role="c"] { grid-area: c; } .dw20-cross [data-role="e"] { grid-area: e; }
    .dw20-cross [data-role="s"] { grid-area: s; }
    .dw20-rungs { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); margin: 22px auto 0; width: min(520px, 100%); }
    .dw20-rungs span { text-align: center; }
    .dw20-rungs [data-token] { position: absolute; top: -14px; left: 12%; width: 14px; height: 14px; margin-left: -7px; border-radius: 50%; }
    .dw20-split3 { display: grid; grid-template-columns: repeat(3, max-content); gap: 28px; justify-content: center; margin-top: 16px; }
    .dw20-split3 h3 { margin: 0 0 8px; text-align: center; letter-spacing: .14em; }
    .dw20-rule { display: block; letter-spacing: 0; font-weight: 400; margin-top: 4px; }
    .dw20-beats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 22px; }
    .dw20-beats div { border: var(--dw-border-width) solid var(--dw-line); padding: 14px 10px; text-align: center; }
    .dw20-beats b { display: block; margin-bottom: 8px; }
    .dw20-beats span { display: block; line-height: 1.4; }
    .dw20-scale { position: relative; margin: 36px 8px 0; height: 84px; }
    .dw20-scale-bar { position: absolute; left: 0; right: 0; top: 28px; height: 2px; }
    .dw20-scale > span { position: absolute; top: 0; transform: translateX(-50%); }
    .dw20-scale [data-pip] { position: absolute; top: 46px; left: 0; transform: translateX(-50%); font-style: normal; font-size: 14px; font-weight: 700; white-space: nowrap; }
    .dw20-scale [data-pip]::before { content: ""; display: block; width: 10px; height: 10px; margin: 0 auto 4px; }
    .dw20-zones { display: grid; grid-template-columns: 1fr 1fr 1fr; margin-top: 28px; text-align: center; letter-spacing: .14em; }
    .dw20-eq { line-height: 1.45; margin: 0; }
    .dw20-center { text-align: center; }
    .dw20-links { display: flex; gap: 18px; margin-top: 22px; }
    .dw20-links a { border-bottom: var(--dw-border-width) solid var(--dw-blue); }
    .dw20-funnel { display: grid; gap: 10px; margin-top: 18px; }
    .dw20-funnel [data-row] { display: flex; gap: 6px; justify-content: center; }
    .dw20-block { width: 28px; height: 18px; }
    .dw20-hero-board { position: absolute; right: 7%; top: 16%; }
    .dw20-copy { position: relative; z-index: 1; max-width: 760px; }
    @media (max-width: 1100px) {
        .dw20-row, .dw20-pair { gap: 16px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .dw20-side { transition: none; }
    }
</style>
        <section class="dw-wrap">
            <div class="dw20-hero-board" data-board></div>
            <div class="dw20-copy">



            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'facts',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Seed 20020
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Start with noise.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="dw20-row">
                <div>


                    <div class="dw20-facts" style="margin-top:22px">
                        <p class="dw20-fact" data-fact><b>63</b><span>cells</span></p>
                        <p class="dw20-fact" data-fact><b>4</b><span>channels</span></p>
                        <p class="dw20-fact" data-fact><b>1</b><span>seed</span></p>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'determinism',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Same opening field
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The seed is fixed. The choices are not.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw20-pair" style="margin-top:18px">
                <div class="dw20-side" data-side="a" data-board></div>
                <div class="dw20-side" data-side="b" data-board></div>
            </div>
            <div class="dw20-caption">
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>same seed</p>
</div>
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>different constraints</p>
</div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'local',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
One cell, four neighbors
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Average, then snap.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw20-cross">
                <b data-flow data-role="n" data-dx="0" data-dy="40">100</b>
                <b data-flow data-role="w" data-dx="50" data-dy="0">160</b>
                <b data-role="c">140</b>
                <b data-flow data-role="e" data-dx="-50" data-dy="0">90</b>
                <b data-flow data-role="s" data-dx="0" data-dy="-40">180</b>
            </div>
            <div class="dw20-rungs">
                <span data-rung>0</span>
                <span data-rung>85</span>
                <span data-rung>170</span>
                <span data-rung>255</span>
                <i data-token></i>
            </div>
            <div class="dw20-caption">
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>average(cell, north, south, east, west)</p>
</div>
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>0 · 85 · 170 · 255</p>
</div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'regions',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Quantization
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Shared values become regions.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw20-center">


            <div class="dw20-board" data-board style="justify-content:center;margin-top:18px"></div>
            <div class="dw20-caption">
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Nearby values lose their differences.</p>
</div>
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Structure appears by removing possibilities.</p>
</div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'constraints',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Same patch
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Three edits, not three labels.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw20-split3">
                <div>
                    <h3>LIFE<span class="dw20-rule">+48 with support, else +16</span></h3>
                    <div data-patch="life"></div>
                </div>
                <div>
                    <h3>ORDER<span class="dw20-rule">neighbors ×3, majority wins</span></h3>
                    <div data-patch="order"></div>
                </div>
                <div>
                    <h3>VOID<span class="dw20-rule">−120 if unsupported, else −64</span></h3>
                    <div data-patch="void"></div>
                </div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'decisions',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Five passes
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Each pass is a decision.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw20-beats">
                <div data-beat><b>01</b><span>condition</span><span>settle</span><span>inspect</span></div>
                <div data-beat><b>02</b><span>condition</span><span>settle</span><span>inspect</span></div>
                <div data-beat><b>03</b><span>condition</span><span>settle</span><span>inspect</span></div>
                <div data-beat><b>04</b><span>condition</span><span>settle</span><span>inspect</span></div>
                <div data-beat><b>05</b><span>condition</span><span>settle</span><span>inspect</span></div>
            </div>
            <p class="dw20-line" data-line style="margin-top:28px">Generating the world is the first phase of play.</p>
            <div class="dw20-caption">
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Pass 1</p>
</div>
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Pass 2</p>
</div>
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Pass 3</p>
</div>
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Pass 4</p>
</div>
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Pass 5</p>
</div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'morph',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Seed 20020
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Noise becomes terrain.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="dw20-row">
                <div>


                    <div class="dw20-caption">
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Mixed regions</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Coherent regions</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>VOID · GROUND · ENERGY</p>
</div>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'thresholds',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Same four channels
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
sum = a + b + c + d
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw20-scale">
                <span style="left:0">0</span>
                <span style="left:33.3%">340</span>
                <span style="left:66.7%">680</span>
                <span style="left:100%">1020</span>
                <div class="dw20-scale-bar"></div>
                <i data-pip><b>0</b></i>
                <i data-pip><b>0</b></i>
                <i data-pip><b>0</b></i>
            </div>
            <div class="dw20-zones">
                <span>VOID ≤ 340</span>
                <span>GROUND</span>
                <span>ENERGY ≥ 680</span>
            </div>
            <div class="dw20-caption">
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Three cells from the locked field.</p>
</div>
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>The class is a reading of the sum.</p>
</div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'objective',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
After five passes
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The route is already in the field.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="dw20-row">
                <div>



@endverbatim
@php
$snippet = <<<'SW_CODE_9_0'
core = max(a + b + 2c)
SW_CODE_9_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    <div class="dw20-caption">
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Non-void cells</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Core</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Reachable ground and energy</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Farthest ground → exit</p>
</div>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'walk',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Orthogonal steps
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Void blocks. Energy can be crossed.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="dw20-row">
                <div>


                    <div class="dw20-caption">
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>Did generation leave a viable world?</p>
</div>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sequenceA',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Seed 20020
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Sequence A
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="dw20-row">
                <div>


                    <div class="dw20-caption">
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>LIFE</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>ORDER</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>LIFE</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>VOID</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>ORDER</p>
                            <p class="dw20-sub">Core <span data-core>(3, 0)</span> · Exit <span data-exit>(6, 8)</span><br><span data-void>6</span> void · <span data-ground>54</span> ground · <span data-energy>3</span> energy</p>
</div>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sequenceB',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Same seed, restored
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Sequence B
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="dw20-row">
                <div>


                    <div class="dw20-caption">
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>VOID</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>VOID</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>ORDER</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>VOID</p>
</div>
                        <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>LIFE</p>
                            <p class="dw20-sub"><span data-void>63</span> void<br>NO WAY OUT</p>
</div>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'compare',
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw20-center">
            <p class="dw20-kicker">Seed 20020</p>
            <div class="dw20-pair" style="margin-top:16px">
                <div data-side="a" data-board></div>

@endverbatim
@php
$snippet = <<<'SW_CODE_13_0'
same seed
+
different constraints
=
different world
SW_CODE_13_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-equation' => '',
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                <div data-side="b" data-board></div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'funnel',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw20-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw20-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Constraint, not placement
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Possibilities fall away.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="dw20-funnel">
                <div data-row="0"><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i></div>
                <div data-row="1"><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i></div>
                <div data-row="2"><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i></div>
                <div data-row="3"><i class="dw20-block"></i></div>
            </div>
            <div class="dw20-caption">
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>fewer possible worlds</p>
</div>
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>stable topology</p>
</div>
                <div class="slidewire-fragment" data-fragment="data-fragment">
    <p>one world</p>
</div>
            </div>
        </section>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'close',
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide dw20" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw20-kicker">Darkwaar20</p>
            <p class="dw20-line" data-line>Noise is the space of possible worlds.</p>
            <p class="dw20-line" data-line>Five choices remove possibilities.</p>
            <p class="dw20-line" data-line>Then the player walks through what remains.</p>
            <div class="dw20-links" data-line>
                <a href="https://darkwaar.com/blog/2026-10-03-darkwaar20">Release page</a>
                <a href="https://darkwoodcom.itch.io/darkwaar20">Play in the browser</a>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>
</x-darkwood.deck>
