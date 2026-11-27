<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-title',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle pg-sub',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Darkwaar27
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Pigment
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Rotation, Color Algebra &amp; Deterministic Puzzles
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<style>
    .slidewire-frame.pg,
    .slidewire-frame:has(.pg) { overflow: hidden; }
    .slidewire-frame.pg,
    article.pg,
    .pg.dw-slide {
        --pg-bg: var(--dw-blue);
        --pg-board: var(--dw-blue);
        --pg-red: var(--dw-blue);
        --pg-yellow: var(--dw-blue);
        --pg-blue: var(--dw-blue);
        --pg-orange: var(--dw-blue);
        --pg-purple: var(--dw-blue);
        --pg-green: var(--dw-blue);
        --pg-white: var(--dw-blue);
        --pg-black: var(--dw-blue);
        --pg-muted: var(--dw-blue);
        --pg-line: var(--dw-blue);
        min-height: 100%;
        height: 100%;
        max-height: 100%;
    }
    .slidewire-frame.pg::before,
    .slidewire-frame.pg::after,
    article.pg::before,
    article.pg::after,
    .pg.dw-slide::before,
    .pg.dw-slide::after { display: none !important; content: none !important; }
    .pg .dw-wrap {
        width: min(1180px, 100%);
        min-height: 0;
        height: 100%;
        padding: 24px 0 16px;
        justify-content: center;
    }
    .pg .dw-wrap--top { justify-content: flex-start; padding-top: 18px; }
    .pg-kicker {
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
        margin: 0;
    }
    .pg-title {
        margin: 12px 0 0;
        line-height: .94;
        font-weight: 700;
    }
    .pg-line {
        margin: 0;
        line-height: 1.05;
        font-weight: 700;
    }
    .pg-sub {
        margin: 14px 0 0;
        line-height: 1.3;
    }
    .pg-take {
        margin: 18px 0 0;
        font-weight: 700;
    }
    .pg-link {
        border-bottom: var(--dw-border-width) solid var(--dw-blue);
        text-decoration: none;
    }
    .pg-row {
        display: flex;
        gap: 36px;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }
    .pg-row > * { min-width: 0; }
    .pg-board {
        display: grid;
        grid-template-columns: repeat(5, var(--pg-cell, 36px));
        grid-template-rows: repeat(5, var(--pg-cell, 36px));
        gap: 3px;
        padding: 8px;
        border: var(--dw-border-width) solid var(--pg-line);
        width: max-content;
    }
    .pg-cell {
        position: relative;
        display: grid;
        place-items: center;
    }
    .pg-cell.is-die { z-index: 2; }
    .pg-die-token {
        position: absolute;
        inset: 18%;
        display: grid;
        place-items: center;
        font-size: calc(var(--pg-cell, 36px) * .28);
        font-weight: 700;
        border: var(--dw-border-width) solid var(--dw-blue);
    }
    .pg-mark {
        position: absolute;
        inset: 10%;
        border: var(--dw-border-width) solid var(--pg-white);
        display: grid;
        place-items: center;
        font-weight: 700;
        pointer-events: none;
        z-index: 1;
    }
    .pg-compass {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 12px;
    }
    .pg-swatch {
        display: grid;
        gap: 4px;
        justify-items: center;
        min-width: 52px;
    }
    .pg-swatch b {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border: var(--dw-border-width) solid var(--pg-line);
    }
    .pg-swatch span {
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .pg-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }
    .pg-btn {
        appearance: none;
        border: var(--dw-border-width) solid var(--pg-line);
        font: inherit;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 10px 14px;
        cursor: pointer;
    }
    .pg-status {
        margin: 12px 0 0;
        min-height: 1.4em;
    }
    .pg-faces {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
        width: min(420px, 100%);
    }
    .pg-face-card {
        border: var(--dw-border-width) solid var(--pg-line);
        padding: 10px;
        display: grid;
        gap: 8px;
    }
    .pg-face-card span {
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .pg-face-card b {
        display: grid;
        place-items: center;
        min-height: 48px;
    }
    .pg-code {
        margin: 14px 0 0;
        padding: 16px 18px;
        border: var(--dw-border-width) solid var(--pg-line);
        font-family: var(--font-mono);
        font-size: clamp(13px, 1.15vw, 16px);
        line-height: 1.45;
        overflow: auto;
        white-space: pre;
    }
    .pg-mix-stage {
        display: grid;
        justify-items: center;
        gap: 16px;
        margin-top: 8px;
    }
    .pg-mix-pot {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-weight: 700;
        border: var(--dw-border-width) solid var(--dw-blue);
    }
    .pg-pipe {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-top: 22px;
    }
    .pg-pipe span {
        border: var(--dw-border-width) solid var(--pg-line);
        padding: 12px 14px;
        font-weight: 700;
        letter-spacing: .04em;
    }
    .pg-pipe i {
        font-style: normal;
        font-weight: 700;
    }
    .pg-pair {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 28px;
        width: 100%;
        margin-top: 16px;
    }
    .pg-pair h3 {
        margin: 0 0 10px;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .pg-levels {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-top: 20px;
        width: 100%;
    }
    .pg-level {
        border: var(--dw-border-width) solid var(--pg-line);
        padding: 12px 10px;
        display: grid;
        gap: 10px;
        justify-items: center;
        text-align: center;
    }
    .pg-level b {
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .pg-arch {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 22px;
        width: 100%;
    }
    .pg-arch .pg-arch-card {
        border: var(--dw-border-width) solid var(--pg-line);
        padding: 18px 16px;
        min-height: 160px;
    }
    .pg-arch code {
        display: block;
        font-family: var(--font-mono);
        font-size: 15px;
        margin-bottom: 10px;
    }
    .pg-arch p {
        margin: 0;
        line-height: 1.4;
    }
    .pg-lessons {
        display: grid;
        gap: 10px;
        margin-top: 22px;
        width: min(760px, 100%);
    }
    .pg-lessons li {
        list-style: none;
        border-left: var(--dw-border-width) solid var(--pg-yellow);
        padding: 10px 0 10px 16px;
        font-weight: 700;
    }
    .pg-hero {
        position: relative;
        z-index: 1;
        max-width: 720px;
    }
    .pg-hero-board {
        position: absolute;
        right: 4%;
        top: 14%;
    }
    .pg-footer {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 8px;
        letter-spacing: .08em;
        text-transform: uppercase;
        z-index: 2;
    }
    .pg-footer img { width: 28px; height: 28px; }
    .pg-eq {
        margin: 16px 0 0;
    }
    .pg-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }
    .pg-chip {
        border: var(--dw-border-width) solid var(--pg-line);
        padding: 8px 12px;
        font-weight: 700;
    }
    .pg-chip b { display: inline-block; width: 14px; height: 14px; margin-right: 8px; vertical-align: -2px; }
    @media (max-width: 1100px) {
        .pg-row, .pg-pair, .pg-arch, .pg-levels { gap: 12px; }
        .pg-hero-board { position: static; margin: 18px 0 0; }
        .pg-levels { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    @media (prefers-reduced-motion: reduce) {
        .pg-btn { transition: none; }
    }
</style>
        <section class="dw-wrap">
            <div class="pg-hero-board" data-pg-board="title"></div>
            <div class="pg-hero" data-pg-copy>



                <p class="pg-take" style="margin-top:28px">One die. Six faces. Twenty-five cells.</p>
                <p style="margin-top:18px"><a class="pg-link" href="https://darkwoodcom.itch.io/darkwaar27" target="_blank" rel="noopener">darkwoodcom.itch.io/darkwaar27</a></p>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="Darkwood" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-tip',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle pg-sub',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
One die, one rule
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Movement determines<br>the pigment.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Tip onto a neighbor. Rotate 90°. The down face stains the cell.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="pg-row">
                <div data-pg-fade>



                    <div class="pg-controls">
                        <button type="button" class="pg-btn is-accent" data-pg-tip="E">East</button>
                        <button type="button" class="pg-btn" data-pg-tip="S">South</button>
                        <button type="button" class="pg-btn" data-pg-tip="N">North</button>
                        <button type="button" class="pg-btn" data-pg-tip="W">West</button>
                        <button type="button" class="pg-btn" data-pg-reset="tip">Reset</button>
                    </div>
                    <p class="pg-status" data-pg-status="tip">Tip East — yellow lands.</p>
                </div>
                <div>
                    <div data-pg-board="tip"></div>
                    <div class="pg-compass" data-pg-compass="tip"></div>
                </div>
            </div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">east tip</span>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | tip" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-die',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The six faces
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Orientation is the resource.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="pg-row" style="margin-top:18px;align-items:flex-start">
                <div>
                    <div class="pg-faces" data-pg-faces></div>
                    <p class="pg-status" data-pg-status="die">Initial orientation. Tip any direction.</p>
                    <div class="pg-controls">
                        <button type="button" class="pg-btn" data-pg-roll="N">N</button>
                        <button type="button" class="pg-btn" data-pg-roll="E">E</button>
                        <button type="button" class="pg-btn" data-pg-roll="S">S</button>
                        <button type="button" class="pg-btn" data-pg-roll="W">W</button>
                        <button type="button" class="pg-btn" data-pg-reset="die">Reset</button>
                    </div>
                </div>
                <div>
                    <div class="pg-compass" data-pg-compass="die"></div>
                    <div class="pg-chip-row" style="margin-top:22px">
                        <span class="pg-chip"><b></b>Null ↔ Black</span>
                        <span class="pg-chip"><b></b>Red ↔ Blue</span>
                        <span class="pg-chip"><b></b>Yellow ↔ White</span>
                    </div>
                    <p class="pg-sub" style="max-width:420px">Opposites stay fixed. Every tip is a permutation of six slots.</p>
                </div>
            </div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">roll east</span>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | faces" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-perm',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
  'data-pg-fade' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
  'data-pg-fade' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Rotation is a permutation
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
No physics. Just a table.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <p class="pg-eq" data-pg-fade>East → D ← E · U ← W · E ← U · W ← D</p>
            <p class="pg-sub" data-pg-fade>North and South unchanged.</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_3_0'
"E":
    return {"D": e, "U": w, "N": n, "S": s, "E": u, "W": d}
SW_CODE_3_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-pg-fade' => '',
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="pg-take" data-pg-fade>The next state is computed, then drawn.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | permutation" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-mix',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Color algebra
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
next_stain = mix(existing, face)
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="pg-row">
                <div>


                    <div class="pg-controls">
                        <button type="button" class="pg-btn" data-pg-mix="0">R + Y</button>
                        <button type="button" class="pg-btn" data-pg-mix="1">R + B</button>
                        <button type="button" class="pg-btn is-accent" data-pg-mix="2">Y + B</button>
                        <button type="button" class="pg-btn" data-pg-mix="3">White</button>
                        <button type="button" class="pg-btn" data-pg-mix="4">Black</button>
                        <button type="button" class="pg-btn" data-pg-mix="5">Null</button>
                    </div>
                    <p class="pg-status" data-pg-mix-label>Yellow + Blue → Green</p>
                </div>
                <div class="pg-mix-stage">
                    <div class="pg-mix-pot" data-pg-mix-result>G</div>
                    <div class="pg-chip-row">
                        <span class="pg-chip"><b></b>Orange</span>
                        <span class="pg-chip"><b></b>Purple</span>
                        <span class="pg-chip"><b></b>Green</span>
                    </div>
                </div>
            </div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">rb</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">yb</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">white</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">black</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">null</span>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | mix" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-pipeline',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The state transition
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Accept, or spend nothing.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="pg-pipe">
                <span data-pg-pipe>Input</span><i>→</i>
                <span data-pg-pipe>Validate</span><i>→</i>
                <span data-pg-pipe>Roll</span><i>→</i>
                <span data-pg-pipe>Mix</span><i>→</i>
                <span data-pg-pipe>Update</span><i>→</i>
                <span data-pg-pipe>Victory?</span>
            </div>

@endverbatim
@php
$snippet = <<<'SW_CODE_5_0'
if not _floor(current, to):
    return {state, events: {accepted: false}}
# roll → mix → spent += 1 → phase
SW_CODE_5_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="pg-take">Off-board tips are refused without consuming a turn.</p>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">pipeline</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">validate</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">roll</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">mix</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">update</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">victory</span>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | apply" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-state',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
More than pathfinding
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Same position ≠ same state.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <p class="pg-eq">State = Position × Orientation × Pigments × Moves</p>
            <div class="pg-pair">
                <div>
                    <h3 data-pg-fade>Path A · E S W N E</h3>
                    <div data-pg-board="state"></div>
                </div>
                <div>
                    <h3 data-pg-fade>Path B · E S E N W</h3>
                    <div data-pg-board="state"></div>
                </div>
            </div>
            <p class="pg-take">Two greens. Different leftovers. Different die facing.</p>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">end</span>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | state" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-green',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle pg-sub',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Level 2 · Return
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Solving Green
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Start (0,0). Mark Green at (1,0). Limit 5.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="pg-row">
                <div>



                    <p class="pg-eq">E → S → W → N → E</p>
                    <div class="pg-controls">
                        <button type="button" class="pg-btn" data-pg-prev="green">Prev</button>
                        <button type="button" class="pg-btn is-accent" data-pg-next="green">Next</button>
                        <button type="button" class="pg-btn" data-pg-reset="green">Replay</button>
                    </div>
                    <p class="pg-status"><span data-pg-step>0 | 5</span> · <span data-pg-status="green">Start at (0,0).</span></p>
                    <p class="pg-take">Yellow + Blue = Green</p>
                </div>
                <div>
                    <div data-pg-board="green"></div>
                    <div class="pg-compass" data-pg-compass="green"></div>
                </div>
            </div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">1</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">2</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">3</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">4</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">5</span>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | green" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-leave',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
  'data-pg-fade' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
  'data-pg-fade' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Levels 2 → 3
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A solution is the whole board.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="pg-pair">
                <div data-pg-fade>
                    <h3>E S W N E · wins L3</h3>
                    <div data-pg-board="leave-a"></div>
                    <p class="pg-status">Green at (1,0) · Yellow at (0,0)</p>
                </div>
                <div data-pg-fade>
                    <h3>E S E N W · fails L3</h3>
                    <div data-pg-board="leave-b"></div>
                    <p class="pg-status">Green yes · start not yellow</p>
                </div>
            </div>
            <p class="pg-take" data-pg-fade>Both green level 2. Only one preserves the start.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | leave" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-levels',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Five levels, five constraints
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Same machine. Steeper asks.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="pg-levels">
                <div class="pg-level" data-pg-level>
                    <b>1 · First Face</b>
                    <div data-pg-mini="0"></div>
                    <span>Paint</span>
                </div>
                <div class="pg-level" data-pg-level>
                    <b>2 · Return</b>
                    <div data-pg-mini="1"></div>
                    <span>Mix</span>
                </div>
                <div class="pg-level" data-pg-level>
                    <b>3 · Leave</b>
                    <div data-pg-mini="2"></div>
                    <span>Preserve</span>
                </div>
                <div class="pg-level" data-pg-level>
                    <b>4 · Other Pair</b>
                    <div data-pg-mini="3"></div>
                    <span>Combine</span>
                </div>
                <div class="pg-level" data-pg-level>
                    <b>5 · White</b>
                    <div data-pg-mini="4"></div>
                    <span>Clear</span>
                </div>
            </div>
            <p class="pg-sub" style="margin-top:20px">Purple lives in the mix table — unused by these five boards.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | levels" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-arch',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
  'data-pg-fade' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
  'data-pg-fade' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Small Godot architecture
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Rules first. Scene second.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="pg-arch">
                <div class="pg-arch-card" data-pg-fade>
                    <code>rules.gd</code>
                    <p>Deterministic transitions: roll, mix, apply, levels, replay checks.</p>
                </div>
                <div class="pg-arch-card" data-pg-fade>
                    <code>main.gd</code>
                    <p>Input, drawing, tween, undo history. Projects state — never invents faces.</p>
                </div>
                <div class="pg-arch-card" data-pg-fade>
                    <code>main.tscn</code>
                    <p>One Control. No ECS, no physics bodies, no large framework.</p>
                </div>
            </div>
            <p class="pg-take" data-pg-fade>Small games benefit from small architectures.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | architecture" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-render',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
  'data-pg-fade' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
  'data-pg-fade' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Rendering the rules
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Feedback is comprehension.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="pg-row">
                <div>


                    <div class="pg-chip-row" data-pg-fade>
                        <span class="pg-chip">Pseudo-3D die</span>
                        <span class="pg-chip">Face glyphs</span>
                        <span class="pg-chip">Pigment tween</span>
                        <span class="pg-chip">Target rings</span>
                        <span class="pg-chip">Roll arc</span>
                        <span class="pg-chip">Orientation compass</span>
                    </div>
                    <p class="pg-sub" data-pg-fade style="max-width:520px">If facing is unreadable, the mix feels arbitrary. Animation that names a rule is interface, not decoration.</p>
                </div>
                <div data-pg-fade>
                    <div data-pg-board="render"></div>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | view" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-undo',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle pg-sub',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Determinism &amp; undo
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
S₀ → S₁ → S₂<br>then S₂ → S₁
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Snapshot before accept. Deterministic transition. Known-solution replay.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="pg-row">
                <div>



                    <div class="pg-controls">
                        <button type="button" class="pg-btn is-accent" data-pg-undo-tip>Tip East</button>
                        <button type="button" class="pg-btn" data-pg-undo-back>Undo</button>
                        <button type="button" class="pg-btn" data-pg-reset="undo">Reset</button>
                    </div>
                    <p class="pg-status" data-pg-status="undo">S₀ · tip East, then Undo.</p>
                    <p class="pg-take">Deterministic — not purely functional.</p>
                </div>
                <div data-pg-board="undo"></div>
            </div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">tip</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">undo</span>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | undo" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-lessons',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-line',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Engineering lessons
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What the model implies
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <ul class="pg-lessons">
                <li data-pg-lesson>Orientation is a resource.</li>
                <li data-pg-lesson>Movement modifies future actions.</li>
                <li data-pg-lesson>Small transition tables create complex decisions.</li>
                <li data-pg-lesson>Deterministic systems replay and debug cleanly.</li>
                <li data-pg-lesson>Animation should expose rules.</li>
                <li data-pg-lesson>Small games benefit from small architectures.</li>
            </ul>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">1</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">2</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">3</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">4</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">5</span>
</div>
            <div class="slidewire-fragment" data-fragment="data-fragment">
    <span class="sr-only">6</span>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | lessons" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-close',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline pg-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pg-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle pg-sub',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Play Pigment
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
One die.<br>Six faces.<br>Twenty-five cells.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Tip carefully. Leave the board you meant.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="pg-row">
                <div data-pg-fade>



                    <p style="margin-top:24px"><a class="pg-link" href="https://darkwoodcom.itch.io/darkwaar27" target="_blank" rel="noopener">https://darkwoodcom.itch.io/darkwaar27</a></p>
                </div>
                <div data-pg-fade>
                    <div data-pg-board="close"></div>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="Darkwood · Darkwaar27" />
</x-darkwood.slide>
</x-darkwood.deck>
