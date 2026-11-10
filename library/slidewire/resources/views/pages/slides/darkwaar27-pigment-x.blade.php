<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'data-dw-motion' => 'pg-promo-x',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide pg pgx" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header pgx-copy',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading pgx-title',
  'data-pg-promo-title' => '',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle pgx-sub',
  'data-pg-promo-sub' => '',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:title>@verbatim
DARKWAAR27 — PIGMENT
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
One die. Six faces. Twenty-five cells.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<style>
    .slidewire-frame.pgx,
    .slidewire-frame:has(.pgx-stage) {
        --pg-bg: var(--dw-blue);
        --pg-board: var(--dw-blue);
        --pg-yellow: var(--dw-blue);
        --pg-green: var(--dw-blue);
        --pg-white: var(--dw-blue);
        --pg-muted: var(--dw-blue);
        --pg-line: var(--dw-blue);
        overflow: hidden;
    }
    .pgx-stage {
        position: relative;
        width: 100%;
        height: 100%;
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 48px;
        box-sizing: border-box;
    }
    .pgx-copy {
        position: absolute;
        top: 7%;
        left: 0;
        right: 0;
        text-align: center;
        z-index: 3;
        pointer-events: none;
    }
    .pgx-title {
        margin: 0;
        letter-spacing: .14em;
        text-transform: uppercase;
        font-weight: 700;
    }
    .pgx-sub {
        margin: 14px 0 0;
        letter-spacing: .04em;
    }
    .pgx-overlay {
        position: absolute;
        bottom: 14%;
        left: 0;
        right: 0;
        text-align: center;
        letter-spacing: .12em;
        text-transform: uppercase;
        font-weight: 700;
        z-index: 3;
        pointer-events: none;
    }
    .pgx-cta {
        position: absolute;
        inset: 0;
        display: grid;
        place-content: center;
        gap: 18px;
        text-align: center;
        z-index: 4;
        pointer-events: none;
    }
    .pgx-cta strong {
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .pgx-cta span {
        letter-spacing: .04em;
    }
    .pg-board {
        display: grid;
        grid-template-columns: repeat(5, var(--pg-cell, 92px));
        grid-template-rows: repeat(5, var(--pg-cell, 92px));
        gap: 6px;
        padding: 14px;
        border: var(--dw-border-width) solid var(--pg-line);
        width: max-content;
        perspective: 900px;
    }
    .pg-cell {
        position: relative;
        display: grid;
        place-items: center;
        transform-style: preserve-3d;
    }
    .pg-cell.is-die { z-index: 2; }
    .pg-die-token {
        position: absolute;
        inset: 16%;
        display: grid;
        place-items: center;
        font-size: calc(var(--pg-cell, 92px) * .28);
        font-weight: 700;
        border: var(--dw-border-width) solid var(--dw-blue);
        transform-style: preserve-3d;
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
</style>
        <div class="pgx-stage">
            <div class="pgx-copy">


            </div>
            <div data-pg-board="promo" aria-label="Pigment board"></div>
            <div class="pgx-overlay" data-pg-promo-overlay></div>
            <div class="pgx-cta" data-pg-promo-cta>
                <strong>PLAY DARKWAAR27 — PIGMENT</strong>
                <span>darkwoodcom.itch.io/darkwaar27</span>
            </div>
        </div>
@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>
</x-darkwood.deck>
