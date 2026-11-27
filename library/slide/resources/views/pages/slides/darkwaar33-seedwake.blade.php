<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-title',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header sw-copy',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-title',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Darkwaar 33
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Seedwake
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<style>
            .slidewire-frame.sw,
            .slidewire-frame:has(.sw) { overflow: hidden; }
            article.sw,
            .sw.dw-slide {
                --sw-ink: #0c0d12;
                --sw-bone: #e9e4d8;
                --sw-amber: #f2a541;
                --sw-cyan: #5fd3e6;
                --sw-crimson: #e0465a;
                --sw-wall: #2a2d36;
                --sw-panel: #141720;
                --sw-muted: #8d8a84;
                --sw-line: rgba(233, 228, 216, .16);
                min-height: 100%;
                height: 100%;
                max-height: 100%;
            }
            .slidewire-frame.sw::before,
            .slidewire-frame.sw::after,
            article.sw::before,
            article.sw::after,
            .sw.dw-slide::before,
            .sw.dw-slide::after { display: none !important; content: none !important; }
            .sw .dw-wrap {
                width: min(1180px, 100%);
                min-height: 0;
                height: 100%;
                padding: 24px 0 16px;
                justify-content: center;
            }
            .sw .dw-wrap--top { justify-content: flex-start; padding-top: 16px; }
            .sw-kicker { margin: 0; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
            .sw-title { margin: 10px 0 0; line-height: .92; font-weight: 700; letter-spacing: .015em; }
            .sw-heading { margin: 8px 0 0; line-height: 1.02; font-weight: 700; letter-spacing: .01em; }
            .sw-subtitle { margin: 18px 0 0; line-height: 1.25; }
            .sw-tagline { margin: 20px 0 0; font-style: italic; }
            .sw-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 36px; align-items: center; width: 100%; }
            .sw-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; width: 100%; }
            .sw-copy { max-width: 590px; }
            .sw-copy p { line-height: 1.35; }
            .sw-photo { position: relative; width: min(510px, 42vw); justify-self: end; border: var(--dw-border-width) solid var(--sw-line); overflow: hidden; }
            .sw-photo img { display: block; width: 100%; height: auto; }
            .sw-photo.is-portrait { width: min(370px, 31vw); max-height: min(690px, 74vh); }
            .sw-photo.is-portrait img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
            .sw-photo::after { content: ''; position: absolute; inset: 0; pointer-events: none; }
            .sw-rule { height: 1px; margin: 20px 0; }
            .sw-model { border: var(--dw-border-width) solid var(--sw-line); padding: 26px 30px; min-height: 178px; display: grid; align-content: center; }
            .sw-model h3 { margin: 0 0 20px; letter-spacing: .15em; text-transform: uppercase; }
            .sw-model-flow { display: flex; align-items: center; justify-content: center; gap: 14px; font-weight: 700; }
            .sw-node { border: var(--dw-border-width) solid var(--sw-line); padding: 14px 18px; text-align: center; }
            .sw-caption { margin: 16px 0 0; line-height: 1.35; }
            .sw-board-shell { position: relative; width: min(350px, 31vw); margin: 0 auto; }
            .sw-board { display: grid; grid-template-columns: repeat(7, 1fr); aspect-ratio: 1; border: var(--dw-border-width) solid var(--sw-line); padding: 7px; gap: 3px; }
            .sw-cell { position: relative; display: grid; place-items: center; border: var(--dw-border-width) solid var(--dw-blue); font-size: clamp(12px, 1.45vw, 21px); font-weight: 700; }
            .sw-cell.is-stair { outline: var(--dw-border-width) double var(--dw-blue); outline-offset: -7px; }
            .sw-cell.is-sentry { font-size: clamp(16px, 1.8vw, 26px); }
            .sw-cell.is-lancer { transform: none; }
            .sw-board-label { margin: 12px 0 0; text-align: center; font-size: 14px; letter-spacing: .1em; text-transform: uppercase; }
            .sw-board-route { position: absolute; inset: 7px; width: calc(100% - 14px); height: calc(100% - 14px); pointer-events: none; overflow: visible; }
            .sw-board-route polyline { stroke-width: var(--dw-border-width); stroke-linecap: round; stroke-linejoin: round; }
            .sw-split-mark { position: absolute; left: 37%; top: 54%; width: 36px; height: 36px; display: grid; place-items: center; border: var(--dw-border-width) solid var(--sw-cyan); border-left-color: var(--sw-amber); border-radius: 50%; transform: translate(-50%, -50%); }
            .sw-eq { display: grid; grid-template-columns: 1fr auto 1fr auto 1fr; align-items: center; gap: 14px; width: 100%; margin-top: 34px; }
            .sw-eq .sw-node { min-height: 126px; display: grid; align-content: center; gap: 8px; }
            .sw-eq small { font-weight: 400; }
            .sw-code { margin: 0; padding: 18px 20px; border: var(--dw-border-width) solid var(--sw-line); font-family: var(--font-mono); font-size: clamp(13px, 1.25vw, 17px); line-height: 1.45; white-space: pre; overflow: hidden; }
            .sw-proof { margin: 24px 0 0; padding-left: 18px; border-left: var(--dw-border-width) solid var(--sw-amber); }
            .sw-stream-tree { display: grid; grid-template-columns: 160px 1fr; gap: 18px 34px; align-items: center; margin-top: 24px; }
            .sw-seed-root { grid-row: 1 / 4; align-self: stretch; display: grid; place-items: center; border: var(--dw-border-width) solid var(--dw-blue); font-weight: 700; }
            .sw-floor-row { display: grid; grid-template-columns: 108px repeat(3, 1fr); gap: 10px; align-items: center; }
            .sw-stream { padding: 13px 10px; border: var(--dw-border-width) solid var(--sw-line); text-align: center; }
            .sw-arch { position: relative; display: grid; grid-template-columns: 1fr 1.2fr 1fr; gap: 44px 70px; align-items: center; margin-top: 30px; }
            .sw-file { position: relative; z-index: 2; padding: 22px; border: var(--dw-border-width) solid var(--sw-line); min-height: 112px; }
            .sw-file code { font-size: clamp(17px, 1.6vw, 22px); font-weight: 700; }
            .sw-file p { margin: 8px 0 0; line-height: 1.3; }
            .sw-file.is-rules { grid-column: 2; grid-row: 1 / 3; }
            .sw-file.is-main { grid-column: 1; grid-row: 1 / 3; }
            .sw-file.is-view { grid-column: 3; grid-row: 1; }
            .sw-file.is-codec { grid-column: 3; grid-row: 2; }
            .sw-connector { position: absolute; z-index: 1; height: 2px; transform-origin: left center; }
            .sw-connector.one { left: 29%; top: 50%; width: 20%; }
            .sw-connector.two { left: 61%; top: 27%; width: 18%; }
            .sw-connector.three { left: 61%; top: 73%; width: 18%; }
            .sw-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 9px; margin: 22px 0; }
            .sw-action { width: 54px; height: 54px; display: grid; place-items: center; border: var(--dw-border-width) solid var(--sw-line); font-weight: 700; }
            .sw-action.is-wait { width: 78px; }
            .sw-replay-result { padding: 24px; border: var(--dw-border-width) solid var(--dw-blue); text-align: center; }
            .sw-code-string { margin: 26px 0 8px; font-family: var(--font-mono); font-size: clamp(22px, 3vw, 42px); line-height: 1.25; word-break: break-all; }
            .sw-fields { display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; margin-top: 28px; }
            .sw-field { padding: 16px 10px; border-top: var(--dw-border-width) solid var(--sw-cyan); text-align: center; }
            .sw-field:nth-child(2n) { border-top-color: var(--sw-amber); }
            .sw-field b { display: block; }
            .sw-field span { display: block; margin-top: 5px; }
            .sw-validation { display: grid; grid-template-columns: repeat(6, 1fr); align-items: center; gap: 8px; margin-top: 36px; }
            .sw-check { min-height: 98px; display: grid; place-items: center; border: var(--dw-border-width) solid var(--sw-line); text-align: center; font-weight: 700; }
            .sw-check::after { content: '↓'; position: absolute; display: none; }
            .sw-gate { margin-top: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
            .sw-gate div { padding: 16px; text-align: center; border: var(--dw-border-width) solid var(--sw-line); font-weight: 700; }
            .sw-callouts { display: grid; gap: 14px; }
            .sw-callout { padding: 16px 18px; border-left: var(--dw-border-width) solid var(--sw-cyan); }
            .sw-callout small { display: block; margin-top: 5px; }
            .sw-timeline { display: grid; grid-template-columns: 110px repeat(6, 1fr); gap: 7px; margin-top: 30px; align-items: stretch; }
            .sw-timeline > div { min-height: 58px; display: grid; place-items: center; border: var(--dw-border-width) solid var(--sw-line); font-weight: 700; }
            .sw-timeline .label { justify-items: start; padding-left: 16px; }
            .sw-timeline .turn { min-height: 35px; }
            .sw-timeline .split { position: relative; }
            .sw-timeline .split::after { content: 'FIRST SPLIT'; position: absolute; top: calc(100% + 13px); letter-spacing: .12em; white-space: nowrap; }
            .sw-compare { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 22px; }
            .sw-compare h3 { margin: 0 0 18px; }
            .sw-compare ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
            .sw-compare li { padding: 10px 0; border-bottom: var(--dw-border-width) solid var(--sw-line); }
            .sw-limit { margin-top: 22px; text-align: center; }
            .sw-close-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; margin: 28px 0; }
            .sw-close-steps div { min-height: 118px; border-top: var(--dw-border-width) solid var(--sw-amber); padding: 20px; }
            .sw-close-steps div:nth-child(2) { border-top-color: var(--sw-cyan); }
            .sw-close-steps b { display: block; margin-bottom: 8px; }
            .sw-play { display: inline-block; margin-top: 18px; text-decoration: none; border-bottom: var(--dw-border-width) solid var(--dw-blue); }
            .sw-footer { position: absolute; right: 26px; bottom: 18px; letter-spacing: .08em; }
            .sw-fragments { position: absolute; width: 1px; height: 1px; overflow: hidden; }
            @media (max-width: 880px) {
                .sw .dw-wrap { padding: 14px 0 10px; }
                .sw-grid-2 { gap: 20px; }
                .sw-photo { width: min(390px, 42vw); }
                .sw-board-shell { width: min(300px, 32vw); }
                .sw-model { padding: 18px; }
                .sw-model-flow { gap: 7px; }
                .sw-node { padding: 10px; }
                .sw-arch { gap: 22px 34px; }
                .sw-fields { grid-template-columns: repeat(3, 1fr); }
            }
            @media (max-height: 700px) {
                .sw-callouts { gap: 8px; }
                .sw-callout { padding: 11px 14px; }
                .sw-callout small { margin-top: 3px; }
                .sw-callouts + .sw-proof { margin-top: 12px; }
            }
            @media (max-width: 660px) {
                .slidewire-frame.sw, .slidewire-frame:has(.sw) { }
                .sw-grid-2, .sw-compare { grid-template-columns: 1fr; }
                .sw-photo, .sw-photo.is-portrait { display: none; }
                .sw-board-shell { width: min(310px, 72vw); }
                .sw-eq { grid-template-columns: 1fr; gap: 6px; }
                .sw-eq .sw-arrow { transform: rotate(90deg); text-align: center; }
                .sw-eq .sw-node { min-height: 68px; }
                .sw-stream-tree { grid-template-columns: 90px 1fr; gap: 10px; }
                .sw-floor-row { grid-template-columns: 70px repeat(3, 1fr); gap: 5px; }
                .sw-validation { grid-template-columns: repeat(3, 1fr); }
                .sw-fields { grid-template-columns: repeat(2, 1fr); }
                .sw-timeline { grid-template-columns: 75px repeat(6, 1fr); gap: 3px; }
                .sw-close-steps { grid-template-columns: 1fr; gap: 8px; }
                .sw-close-steps div { min-height: 56px; padding: 10px; }
            }
        </style>
        <section class="dw-wrap">
            <div class="sw-grid-2">
                <div class="sw-copy" data-sw-reveal>


                    <div class="sw-rule"></div>
                    <p class="sw-subtitle">Asynchronous Multiplayer<br>Without a Server</p>
                    <p class="sw-tagline">Same seed. Same dark. Different choices.</p>
                </div>
                <figure class="sw-photo" data-sw-reveal>
                    <img src="/images/darkwaar33/cover.png" alt="Seedwake tactical board with an amber player and cyan rival ghost">
                    <svg viewbox="0 0 630 500" aria-hidden="true" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none">
                        <path data-sw-path d="M178 409 L178 363 L224 363 L224 316 L270 316" fill="none" stroke="var(--dw-blue)" stroke-width="var(--dw-border-width)" vector-effect="non-scaling-stroke" stroke-linecap="round" opacity=".72"></path>
                        <path data-sw-path d="M224 316 L224 270 L270 270 L270 224" fill="none" stroke="var(--dw-blue)" stroke-width="var(--dw-border-width)" vector-effect="non-scaling-stroke" stroke-linecap="round" opacity=".72"></path>
                    </svg>
                </figure>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-question',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
  'data-sw-reveal' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The question
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Can multiplayer exist without a server?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sw-grid-2" style="margin-top:32px">
                <div class="sw-model" data-sw-model>
                    <h3>Live authority</h3>
                    <div class="sw-model-flow"><span class="sw-node">Player A</span><span class="sw-arrow">→</span><span class="sw-node sw-crimson">Server</span><span class="sw-arrow">←</span><span class="sw-node">Player B</span></div>
                </div>
                <div class="sw-model" data-sw-model>
                    <h3>Seedwake</h3>
                    <div class="sw-model-flow"><span class="sw-node is-amber">Player A</span><span class="sw-arrow">→</span><span class="sw-node is-cyan">Run code</span><span class="sw-arrow">→</span><span class="sw-node">Player B</span></div>
                </div>
            </div>
            <p class="sw-proof" data-sw-reveal>Players share a simulation history, not a live session.</p>
            <div class="sw-fragments"><div class="slidewire-fragment" data-fragment="data-fragment">
    <span>model two</span>
</div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-seed',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
  'data-sw-reveal' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Procedural generation as shared reference
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
One seed, one world
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sw-grid-2" style="margin-top:24px">
                <div class="sw-board-shell" data-sw-seeded-board>
                    <div class="sw-board" aria-label="Player A floor 2 generated from seed V9PNR2"><span class="sw-cell is-glint" data-x="0" data-y="0">✦</span><span class="sw-cell " data-x="1" data-y="0"></span><span class="sw-cell " data-x="2" data-y="0"></span><span class="sw-cell " data-x="3" data-y="0"></span><span class="sw-cell is-glint" data-x="4" data-y="0">✦</span><span class="sw-cell " data-x="5" data-y="0"></span><span class="sw-cell is-stair" data-x="6" data-y="0">▣</span><span class="sw-cell is-wall" data-x="0" data-y="1"></span><span class="sw-cell " data-x="1" data-y="1"></span><span class="sw-cell " data-x="2" data-y="1"></span><span class="sw-cell is-wall" data-x="3" data-y="1"></span><span class="sw-cell is-enemy" data-x="4" data-y="1">■</span><span class="sw-cell is-enemy" data-x="5" data-y="1">■</span><span class="sw-cell " data-x="6" data-y="1"></span><span class="sw-cell " data-x="0" data-y="2"></span><span class="sw-cell " data-x="1" data-y="2"></span><span class="sw-cell " data-x="2" data-y="2"></span><span class="sw-cell " data-x="3" data-y="2"></span><span class="sw-cell is-enemy is-lancer" data-x="4" data-y="2">▶</span><span class="sw-cell is-glint" data-x="5" data-y="2">✦</span><span class="sw-cell " data-x="6" data-y="2"></span><span class="sw-cell is-wall" data-x="0" data-y="3"></span><span class="sw-cell " data-x="1" data-y="3"></span><span class="sw-cell is-wall" data-x="2" data-y="3"></span><span class="sw-cell " data-x="3" data-y="3"></span><span class="sw-cell " data-x="4" data-y="3"></span><span class="sw-cell " data-x="5" data-y="3"></span><span class="sw-cell is-enemy is-sentry" data-x="6" data-y="3">●</span><span class="sw-cell " data-x="0" data-y="4"></span><span class="sw-cell " data-x="1" data-y="4"></span><span class="sw-cell " data-x="2" data-y="4"></span><span class="sw-cell " data-x="3" data-y="4"></span><span class="sw-cell " data-x="4" data-y="4"></span><span class="sw-cell " data-x="5" data-y="4"></span><span class="sw-cell " data-x="6" data-y="4"></span><span class="sw-cell " data-x="0" data-y="5"></span><span class="sw-cell is-glint" data-x="1" data-y="5">✦</span><span class="sw-cell " data-x="2" data-y="5"></span><span class="sw-cell " data-x="3" data-y="5"></span><span class="sw-cell is-wall" data-x="4" data-y="5"></span><span class="sw-cell " data-x="5" data-y="5"></span><span class="sw-cell " data-x="6" data-y="5"></span><span class="sw-cell is-wall" data-x="0" data-y="6"></span><span class="sw-cell " data-x="1" data-y="6"></span><span class="sw-cell is-player" data-x="2" data-y="6">◆</span><span class="sw-cell " data-x="3" data-y="6"></span><span class="sw-cell is-wall" data-x="4" data-y="6"></span><span class="sw-cell is-wall" data-x="5" data-y="6"></span><span class="sw-cell " data-x="6" data-y="6"></span></div>
                    <p class="sw-board-label"><b>Player A</b> · V9PNR2 · floor 2</p>
                </div>
                <div class="sw-board-shell" data-sw-seeded-board>
                    <div class="sw-board" aria-label="Player B floor 2 generated from seed V9PNR2"><span class="sw-cell is-glint" data-x="0" data-y="0">✦</span><span class="sw-cell " data-x="1" data-y="0"></span><span class="sw-cell " data-x="2" data-y="0"></span><span class="sw-cell " data-x="3" data-y="0"></span><span class="sw-cell is-glint" data-x="4" data-y="0">✦</span><span class="sw-cell " data-x="5" data-y="0"></span><span class="sw-cell is-stair" data-x="6" data-y="0">▣</span><span class="sw-cell is-wall" data-x="0" data-y="1"></span><span class="sw-cell " data-x="1" data-y="1"></span><span class="sw-cell " data-x="2" data-y="1"></span><span class="sw-cell is-wall" data-x="3" data-y="1"></span><span class="sw-cell is-enemy" data-x="4" data-y="1">■</span><span class="sw-cell is-enemy" data-x="5" data-y="1">■</span><span class="sw-cell " data-x="6" data-y="1"></span><span class="sw-cell " data-x="0" data-y="2"></span><span class="sw-cell " data-x="1" data-y="2"></span><span class="sw-cell " data-x="2" data-y="2"></span><span class="sw-cell " data-x="3" data-y="2"></span><span class="sw-cell is-enemy is-lancer" data-x="4" data-y="2">▶</span><span class="sw-cell is-glint" data-x="5" data-y="2">✦</span><span class="sw-cell " data-x="6" data-y="2"></span><span class="sw-cell is-wall" data-x="0" data-y="3"></span><span class="sw-cell " data-x="1" data-y="3"></span><span class="sw-cell is-wall" data-x="2" data-y="3"></span><span class="sw-cell " data-x="3" data-y="3"></span><span class="sw-cell " data-x="4" data-y="3"></span><span class="sw-cell " data-x="5" data-y="3"></span><span class="sw-cell is-enemy is-sentry" data-x="6" data-y="3">●</span><span class="sw-cell " data-x="0" data-y="4"></span><span class="sw-cell " data-x="1" data-y="4"></span><span class="sw-cell " data-x="2" data-y="4"></span><span class="sw-cell " data-x="3" data-y="4"></span><span class="sw-cell " data-x="4" data-y="4"></span><span class="sw-cell " data-x="5" data-y="4"></span><span class="sw-cell " data-x="6" data-y="4"></span><span class="sw-cell " data-x="0" data-y="5"></span><span class="sw-cell is-glint" data-x="1" data-y="5">✦</span><span class="sw-cell " data-x="2" data-y="5"></span><span class="sw-cell " data-x="3" data-y="5"></span><span class="sw-cell is-wall" data-x="4" data-y="5"></span><span class="sw-cell " data-x="5" data-y="5"></span><span class="sw-cell " data-x="6" data-y="5"></span><span class="sw-cell is-wall" data-x="0" data-y="6"></span><span class="sw-cell " data-x="1" data-y="6"></span><span class="sw-cell is-player" data-x="2" data-y="6">◆</span><span class="sw-cell " data-x="3" data-y="6"></span><span class="sw-cell is-wall" data-x="4" data-y="6"></span><span class="sw-cell is-wall" data-x="5" data-y="6"></span><span class="sw-cell " data-x="6" data-y="6"></span></div>
                    <p class="sw-board-label"><b>Player B</b> · V9PNR2 · floor 2</p>
                </div>
            </div>
            <p class="sw-caption" data-sw-reveal style="text-align:center">Actual generated layout: identical walls, enemies, glints, start and stair.</p>
            <div class="sw-fragments"><div class="slidewire-fragment" data-fragment="data-fragment">
    <span>second board</span>
</div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-diverge',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header sw-copy',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle sw-subtitle',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Player choice is the variable
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The same world does not mean the same outcome
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
<span class="sw-amber">Amber</span> and <span class="sw-cyan">cyan</span> share a path until one action differs.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="sw-grid-2">
                <div class="sw-copy" data-sw-reveal>



                    <p class="sw-proof">Deterministic rules make decisions the only source of divergence.</p>
                </div>
                <div class="sw-board-shell" data-sw-reveal>
                    <div class="sw-board" aria-label="Two player routes diverging on the same generated floor"><span class="sw-cell is-glint" data-x="0" data-y="0">✦</span><span class="sw-cell " data-x="1" data-y="0"></span><span class="sw-cell " data-x="2" data-y="0"></span><span class="sw-cell " data-x="3" data-y="0"></span><span class="sw-cell is-glint" data-x="4" data-y="0">✦</span><span class="sw-cell " data-x="5" data-y="0"></span><span class="sw-cell is-stair" data-x="6" data-y="0">▣</span><span class="sw-cell is-wall" data-x="0" data-y="1"></span><span class="sw-cell " data-x="1" data-y="1"></span><span class="sw-cell " data-x="2" data-y="1"></span><span class="sw-cell is-wall" data-x="3" data-y="1"></span><span class="sw-cell is-enemy" data-x="4" data-y="1">■</span><span class="sw-cell is-enemy" data-x="5" data-y="1">■</span><span class="sw-cell " data-x="6" data-y="1"></span><span class="sw-cell " data-x="0" data-y="2"></span><span class="sw-cell " data-x="1" data-y="2"></span><span class="sw-cell " data-x="2" data-y="2"></span><span class="sw-cell " data-x="3" data-y="2"></span><span class="sw-cell is-enemy is-lancer" data-x="4" data-y="2">▶</span><span class="sw-cell is-glint" data-x="5" data-y="2">✦</span><span class="sw-cell " data-x="6" data-y="2"></span><span class="sw-cell is-wall" data-x="0" data-y="3"></span><span class="sw-cell " data-x="1" data-y="3"></span><span class="sw-cell is-wall" data-x="2" data-y="3"></span><span class="sw-cell " data-x="3" data-y="3"></span><span class="sw-cell " data-x="4" data-y="3"></span><span class="sw-cell " data-x="5" data-y="3"></span><span class="sw-cell is-enemy is-sentry" data-x="6" data-y="3">●</span><span class="sw-cell " data-x="0" data-y="4"></span><span class="sw-cell " data-x="1" data-y="4"></span><span class="sw-cell " data-x="2" data-y="4"></span><span class="sw-cell " data-x="3" data-y="4"></span><span class="sw-cell " data-x="4" data-y="4"></span><span class="sw-cell " data-x="5" data-y="4"></span><span class="sw-cell " data-x="6" data-y="4"></span><span class="sw-cell " data-x="0" data-y="5"></span><span class="sw-cell is-glint" data-x="1" data-y="5">✦</span><span class="sw-cell " data-x="2" data-y="5"></span><span class="sw-cell " data-x="3" data-y="5"></span><span class="sw-cell is-wall" data-x="4" data-y="5"></span><span class="sw-cell " data-x="5" data-y="5"></span><span class="sw-cell " data-x="6" data-y="5"></span><span class="sw-cell is-wall" data-x="0" data-y="6"></span><span class="sw-cell " data-x="1" data-y="6"></span><span class="sw-cell is-player" data-x="2" data-y="6">◆</span><span class="sw-cell " data-x="3" data-y="6"></span><span class="sw-cell is-wall" data-x="4" data-y="6"></span><span class="sw-cell is-wall" data-x="5" data-y="6"></span><span class="sw-cell " data-x="6" data-y="6"></span></div>
                    <svg class="sw-board-route" viewbox="0 0 100 100" aria-hidden="true">
                        <polyline data-sw-route points="35.7,92.8 35.7,78.5 35.7,64.2 50,64.2 50,49.9" stroke="var(--dw-blue)"></polyline>
                        <polyline data-sw-route points="35.7,92.8 35.7,78.5 35.7,64.2 21.4,64.2 21.4,49.9" stroke="var(--dw-blue)"></polyline>
                    </svg>
                    <span class="sw-split-mark" data-sw-split>Y</span>
                    <p class="sw-board-label">First different action</p>
                </div>
            </div>
            <div class="sw-fragments"><div class="slidewire-fragment" data-fragment="data-fragment">
    <span>cyan route</span>
</div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-state',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
  'data-sw-reveal' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Deterministic simulation
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Same state + same action = same result
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sw-eq">
                <div class="sw-node" data-sw-stage><b>State N</b><small>board, HP, intents, floor turn</small></div>
                <div class="sw-arrow">+</div>
                <div class="sw-node is-amber" data-sw-stage><b>Action</b><small>0…12</small></div>
                <div class="sw-arrow">→</div>
                <div class="sw-node is-cyan" data-sw-stage><b>Rules.apply(state, action)</b><small>{ ok, reason, state, events }</small></div>
            </div>
            <div class="sw-grid-2" style="margin-top:18px;align-items:stretch">

@endverbatim
@php
$snippet = <<<'SW_CODE_4_0'
static func apply(
    st_in: Dictionary,
    action: int
) -> Dictionary
SW_CODE_4_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                <div class="sw-model" data-sw-reveal style="min-height:0;padding:18px 22px">
                    <p style="margin:0">Combat executes drawn intents.</p>
                    <p class="sw-caption">No RNG calls during play. No Node calls, time, frame delta or engine RNG in <code>rules.gd</code>.</p>
                </div>
            </div>
            <div class="sw-fragments"><div class="slidewire-fragment" data-fragment="data-fragment">
    <span>action</span>
</div>
<div class="slidewire-fragment" data-fragment="data-fragment">
    <span>apply</span>
</div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-streams',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
  'data-sw-reveal' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Randomness without drift
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Independent generation streams
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="sw-grid-2" style="margin-top:20px;align-items:start">
                <div class="sw-stream-tree" data-sw-reveal>
                    <div class="sw-seed-root">SEED</div>
                    <div class="sw-floor-row"><strong>Floor 1</strong><span class="sw-stream" data-sw-stream>map</span><span class="sw-stream" data-sw-stream>enemy</span><span class="sw-stream" data-sw-stream>offer</span></div>
                    <div class="sw-floor-row"><strong>Floor 2</strong><span class="sw-stream" data-sw-stream>map</span><span class="sw-stream" data-sw-stream>enemy</span><span class="sw-stream" data-sw-stream>offer</span></div>
                    <div class="sw-floor-row"><strong>Floor 7</strong><span class="sw-stream" data-sw-stream>map</span><span class="sw-stream" data-sw-stream>enemy</span><span class="sw-stream" data-sw-stream>offer</span></div>
                </div>

@endverbatim
@php
$snippet = <<<'SW_CODE_5_0'
static func stream(seed, floor_n, name):
    var h = fnv1a32(
        "dw33|%d|%d|%s" %
        [seed, floor_n, name]
    )
    return h if h != 0 else 1

static func xorshift32(s):
    s ^= (s << 13) & MASK
    s ^= s >> 17
    s ^= (s << 5) & MASK
    return s & MASK
SW_CODE_5_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            </div>
            <p class="sw-proof" data-sw-reveal>No random stream advances during gameplay.</p>
            <div class="sw-fragments"><div class="slidewire-fragment" data-fragment="data-fragment">
    <span>streams</span>
</div>
<div class="slidewire-fragment" data-fragment="data-fragment">
    <span>all streams</span>
</div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-arch',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
  'data-sw-reveal' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
A small architecture
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Rules, rendering and interaction stay separate
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sw-arch">
                <span class="sw-connector one"></span><span class="sw-connector two"></span><span class="sw-connector three"></span>
                <div class="sw-file is-main" data-sw-reveal><code>main.gd</code><p>Input, screens, rival tracking. Calls <code>Rules.apply()</code>.</p></div>
                <div class="sw-file is-rules" data-sw-reveal><code>rules.gd</code><p>Pure state transitions, generation, replay trace and ghost lookup.</p></div>
                <div class="sw-file is-view" data-sw-reveal><code>board_view.gd</code><p>Reads state and events. Draws animation, intents, ghost and fork.</p></div>
                <div class="sw-file is-codec" data-sw-reveal><code>codec.gd</code><p>Encodes runs. Imports through <code>Rules.simulate()</code>.</p></div>
            </div>
            <p class="sw-caption" data-sw-reveal style="text-align:center">Dependency direction ends at <code>rules.gd</code>. The rules never call the view.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-replay',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
  'data-sw-reveal' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Replay as data
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A replay is a list of actions
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sw-grid-2" style="margin-top:28px;align-items:start">
                <div data-sw-reveal>
                    <p class="sw-subtitle" style="margin-top:0">Seed <span class="sw-cyan">K7Q2MX</span></p>
                    <div class="sw-actions">
                        <span class="sw-action" data-sw-action>N</span><span class="sw-action" data-sw-action>E</span><span class="sw-action" data-sw-action>E</span><span class="sw-action is-wait" data-sw-action>WAIT</span><span class="sw-action is-push" data-sw-action>W</span><span class="sw-action" data-sw-action>N</span>
                    </div>
                    <p class="sw-caption"><span class="sw-amber">W resolves as PUSH</span> when an enemy occupies the destination. PUSH is not a separate encoded action.</p>
                </div>
                <div>

@endverbatim
@php
$snippet = <<<'SW_CODE_7_0'
static func simulate(
    seed: int,
    actions: PackedByteArray
) -> Dictionary
SW_CODE_7_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    <div class="sw-replay-result" data-sw-result>Reconstructed run<br><small>final state + trace</small></div>
                </div>
            </div>
            <p class="sw-proof" data-sw-reveal>No video recording. No saved state snapshots.</p>
            <div class="sw-fragments"><div class="slidewire-fragment" data-fragment="data-fragment">
    <span>actions</span>
</div>
<div class="slidewire-fragment" data-fragment="data-fragment">
    <span>more actions</span>
</div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-code',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
  'data-sw-reveal' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Portable run codes
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A complete game history in a short string
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <p class="sw-code-string" data-sw-reveal>SW1-04V9P-NR2B5-…</p>
            <p class="sw-caption" data-sw-reveal>Abbreviated for display. This fragment is not importable.</p>
            <div class="sw-fields" data-sw-reveal>
                <div class="sw-field"><b>Version</b><span>rules v1</span></div>
                <div class="sw-field"><b>Seed</b><span>32 bits</span></div>
                <div class="sw-field"><b>Player tag</b><span>3 letters</span></div>
                <div class="sw-field"><b>Result</b><span>score, floors, turns</span></div>
                <div class="sw-field"><b>Actions</b><span>4 bits each</span></div>
                <div class="sw-field"><b>Checksum</b><span>FNV-1a, 16 bits</span></div>
            </div>
            <div class="sw-grid-2" style="margin-top:22px;align-items:stretch">
                <div class="sw-model" data-sw-reveal style="min-height:0;padding:18px 22px"><p style="margin:0">Crockford base32</p><p class="sw-caption">Uppercase, copyable, tolerant of O/0 and I/L/1.</p></div>

@endverbatim
@php
$snippet = <<<'SW_CODE_8_0'
hi = actions[i] & 15
lo = actions[i + 1] & 15
byte = (hi << 4) | lo
SW_CODE_8_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-validate',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
  'data-sw-reveal' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Replay validation
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Never trust the claimed result
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sw-validation">
                <div class="sw-check" data-sw-check>Decode</div><div class="sw-check" data-sw-check>Checksum</div><div class="sw-check" data-sw-check>Version</div><div class="sw-check" data-sw-check>Simulate</div><div class="sw-check" data-sw-check>Compare<br>results</div><div class="sw-check" data-sw-check>Decision</div>
            </div>
            <div class="sw-gate" data-sw-reveal><div class="accept">ACCEPT<br><small>same score, floors and turns</small></div><div class="reject">REJECT<br><small>corrupt, illegal, unfinished or mismatched</small></div></div>
            <p class="sw-proof" data-sw-reveal>Consistency check, not cryptographic anti-cheat.</p>
            <div class="sw-fragments"><div class="slidewire-fragment" data-fragment="data-fragment">
    <span>checksum</span>
</div>
<div class="slidewire-fragment" data-fragment="data-fragment">
    <span>simulate</span>
</div>
<div class="slidewire-fragment" data-fragment="data-fragment">
    <span>compare</span>
</div>
<div class="slidewire-fragment" data-fragment="data-fragment">
    <span>decision</span>
</div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-ghost',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header sw-copy',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The rival ghost
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Another player’s history, not another live player
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="sw-grid-2">
                <figure class="sw-photo is-portrait" data-sw-reveal style="justify-self:center">
                    <img src="/images/darkwaar33/screenshot-1.png" alt="Seedwake gameplay showing a cyan rival ghost, wake trail, enemy intentions and split marker">
                </figure>
                <div class="sw-copy" data-sw-reveal>


                    <div class="sw-callouts" style="margin-top:24px">
                        <div class="sw-callout" data-sw-callout>Cyan ghost + four-cell wake<small>Rendered from the rival trace.</small></div>
                        <div class="sw-callout" data-sw-callout>Floor and floor-turn synchronization<small>Floor 2, turn 6 maps to the rival’s floor 2, turn 6.</small></div>
                        <div class="sw-callout" data-sw-callout>Exit and death markers<small>The last position remains visible after the rival stops walking.</small></div>
                    </div>
                    <p class="sw-proof">The ghost never mutates your world.</p>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-timeline',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
  'data-sw-reveal' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Decision comparison
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Where did our decisions become different?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sw-timeline" data-sw-reveal>
                <div class="turn label">Turn</div><div class="turn">1</div><div class="turn">2</div><div class="turn">3</div><div class="turn">4</div><div class="turn">5</div><div class="turn">6</div>
                <div class="label a">Player A</div><div class="a">N</div><div class="a">E</div><div class="a">E</div><div class="a split" data-sw-timeline-split>N</div><div class="a">W</div><div class="a">S</div>
                <div class="label b">Player B</div><div class="b">N</div><div class="b">E</div><div class="b">E</div><div class="b split" data-sw-timeline-split>W</div><div class="b">N</div><div class="b">S</div>
            </div>
            <p class="sw-proof" data-sw-reveal style="margin-top:54px">The fork identifies a decision, not merely a score difference.</p>
            <p class="sw-caption" data-sw-reveal><code>Rules.first_split(mine, theirs)</code> returns the first unequal action index, or −1 while one run remains a prefix.</p>
            <div class="sw-fragments"><div class="slidewire-fragment" data-fragment="data-fragment">
    <span>split</span>
</div>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-backend',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
  'data-sw-reveal' => '',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
  'data-sw-reveal' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Infrastructure avoided by design
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Why no backend?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="sw-compare">
                <div class="traditional" data-sw-reveal><h3>Traditional online competition</h3><ul><li>Authentication</li><li>Persistent storage</li><li>Matchmaking</li><li>Server-side validation</li><li>Global leaderboards</li></ul></div>
                <div class="seedwake" data-sw-reveal><h3>Seedwake rivalry</h3><ul><li>Shared seed</li><li>Recorded actions</li><li>Deterministic simulation</li><li>Portable run code</li><li>Local verification</li></ul></div>
            </div>
            <p class="sw-limit" data-sw-reveal>Trade-off: no opponent discovery, global leaderboard or authoritative anti-cheat.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | 14" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'sw-close',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide sw" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header sw-copy',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline sw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading sw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Conclusion
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Determinism makes gameplay portable
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="sw-grid-2">
                <div class="sw-copy" data-sw-reveal>


                    <div class="sw-close-steps">
                        <div><b>Seed</b><span>defines the world</span></div>
                        <div><b>Actions</b><span>define the history</span></div>
                        <div><b>Replay</b><span>reconstructs the result</span></div>
                    </div>
                    <p class="sw-subtitle"><b>Darkwaar33 — Seedwake</b></p>
                    <a class="sw-play" href="https://darkwoodcom.itch.io/darkwaar33">darkwoodcom.itch.io/darkwaar33</a>
                    <p class="sw-tagline">Same seed. Same dark. Different choices.</p>
                </div>
                <figure class="sw-photo is-portrait" data-sw-reveal>
                    <img src="/images/darkwaar33/screenshot-2.png" alt="Seedwake end screen comparing floor-by-floor results with a rival">
                </figure>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | 14" />
</x-darkwood.slide>
</x-darkwood.deck>
