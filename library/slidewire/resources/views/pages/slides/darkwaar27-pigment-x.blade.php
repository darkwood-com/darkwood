{{-- Darkwaar27 Pigment — X gameplay promo (silent, ~28s autoplay) --}}
{{-- Route: /slides/darkwaar27-pigment-x --}}
<x-slidewire::deck
    theme="black"
    transition="fade"
    transition-speed="default"
    :show-controls="false"
    :show-progress="false"
    :show-fullscreen-button="false"
>
    <x-slidewire::slide class="dw-slide pg pgx">
<style>
    .slidewire-frame.pgx,
    .slidewire-frame:has(.pgx-stage) {
        --pg-bg: #14110e;
        --pg-board: #2a241c;
        --pg-yellow: #e2b134;
        --pg-green: #3e9a5a;
        --pg-white: #f4efe4;
        --pg-muted: #a89a88;
        --pg-line: rgba(244, 239, 228, .16);
        overflow: hidden;
        padding: 0 !important;
        background:
            radial-gradient(ellipse at 50% 20%, rgba(226, 177, 52, .12), transparent 55%),
            linear-gradient(160deg, #1a1510 0%, var(--pg-bg) 42%, #0f0d0b 100%) !important;
        color: var(--pg-white);
        font-family: "Iowan Old Style", "Palatino Linotype", Palatino, "Book Antiqua", Georgia, serif;
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
        font-size: clamp(28px, 4.2vw, 54px);
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--pg-yellow);
        font-weight: 700;
    }
    .pgx-sub {
        margin: 14px 0 0;
        font-size: clamp(16px, 2vw, 26px);
        color: var(--pg-muted);
        letter-spacing: .04em;
    }
    .pgx-overlay {
        position: absolute;
        bottom: 14%;
        left: 0;
        right: 0;
        text-align: center;
        font-size: clamp(18px, 2.4vw, 32px);
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--pg-yellow);
        font-weight: 700;
        z-index: 3;
        pointer-events: none;
        text-shadow: 0 2px 18px rgba(0, 0, 0, .55);
    }
    .pgx-cta {
        position: absolute;
        inset: 0;
        display: grid;
        place-content: center;
        gap: 18px;
        text-align: center;
        background: rgba(20, 17, 14, .55);
        z-index: 4;
        pointer-events: none;
    }
    .pgx-cta strong {
        font-size: clamp(28px, 4vw, 52px);
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--pg-yellow);
    }
    .pgx-cta span {
        font-size: clamp(18px, 2.2vw, 28px);
        color: var(--pg-white);
        letter-spacing: .04em;
    }
    .pg-board {
        display: grid;
        grid-template-columns: repeat(5, var(--pg-cell, 92px));
        grid-template-rows: repeat(5, var(--pg-cell, 92px));
        gap: 6px;
        padding: 14px;
        background: rgba(0, 0, 0, .32);
        border: 1px solid var(--pg-line);
        width: max-content;
        perspective: 900px;
    }
    .pg-cell {
        position: relative;
        background: var(--pg-board);
        box-shadow: inset 0 0 0 1px rgba(244, 239, 228, .08);
        display: grid;
        place-items: center;
        transform-style: preserve-3d;
    }
    .pg-cell.is-sealed { box-shadow: inset 0 0 0 3px rgba(244, 239, 228, .35); }
    .pg-cell.is-die { z-index: 2; }
    .pg-die-token {
        position: absolute;
        inset: 16%;
        display: grid;
        place-items: center;
        font-size: calc(var(--pg-cell, 92px) * .28);
        font-weight: 700;
        color: #1a140f;
        box-shadow: 0 8px 18px rgba(0, 0, 0, .4);
        border: 1px solid rgba(244, 239, 228, .35);
        transform-style: preserve-3d;
    }
    .pg-mark {
        position: absolute;
        inset: 10%;
        border: 3px solid var(--pg-white);
        display: grid;
        place-items: center;
        font-size: calc(var(--pg-cell, 92px) * .24);
        font-weight: 700;
        color: var(--pg-white);
        pointer-events: none;
        z-index: 1;
    }
</style>
        <div class="pgx-stage" data-dw-motion="pg-promo-x">
            <div class="pgx-copy">
                <h1 class="pgx-title" data-pg-promo-title>DARKWAAR27 — PIGMENT</h1>
                <p class="pgx-sub" data-pg-promo-sub>One die. Six faces. Twenty-five cells.</p>
            </div>
            <div data-pg-board="promo" aria-label="Pigment board"></div>
            <div class="pgx-overlay" data-pg-promo-overlay></div>
            <div class="pgx-cta" data-pg-promo-cta>
                <strong>PLAY DARKWAAR27 — PIGMENT</strong>
                <span>darkwoodcom.itch.io/darkwaar27</span>
            </div>
        </div>
    </x-slidewire::slide>
</x-slidewire::deck>
