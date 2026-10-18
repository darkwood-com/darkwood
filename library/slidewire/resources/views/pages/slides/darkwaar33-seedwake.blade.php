{{-- Darkwaar33 — Seedwake: Asynchronous Multiplayer Without a Server --}}
{{-- Route: /slides/darkwaar33-seedwake --}}
{{-- Editorial source: Darkwaar33 content/article.md. Technical source: rules.gd, codec.gd, main.gd, board_view.gd. --}}
@php
    $floorRows = ['*...*.X', '#..#BB.', '....L*.', '#.#...O', '.......', '.*..#..', '#.S.##.'];
    $glyphs = ['X' => '▣', 'B' => '■', 'O' => '●', 'L' => '▶', '*' => '✦', 'S' => '◆'];
    $classes = ['#' => 'is-wall', 'X' => 'is-stair', 'B' => 'is-enemy', 'O' => 'is-enemy is-sentry', 'L' => 'is-enemy is-lancer', '*' => 'is-glint', 'S' => 'is-player'];
    $renderBoard = function (string $label = '') use ($floorRows, $glyphs, $classes): string {
        $html = '<div class="sw-board" aria-label="' . e($label) . '">';
        foreach ($floorRows as $y => $row) {
            for ($x = 0; $x < 7; $x++) {
                $char = $row[$x];
                $class = $classes[$char] ?? '';
                $glyph = $glyphs[$char] ?? '';
                $html .= '<span class="sw-cell ' . $class . '" data-x="' . $x . '" data-y="' . $y . '">' . $glyph . '</span>';
            }
        }
        return $html . '</div>';
    };
@endphp

<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    {{-- 1 · TITLE --}}
    {{-- @notes Seedwake looks like a small solo tactics game, but its real technical idea is portability. Two players can inhabit the same generated world without meeting online. We will follow the data that makes that possible: one seed, a deterministic rules engine, and a compact action history. --}}
    <x-slidewire::slide class="dw-slide sw">
        <style>
            .slidewire-frame.sw,
            .slidewire-frame:has(.sw) { overflow: hidden; padding: 1rem 1.25rem 1.1rem; background: #0c0d12; }
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
                color: var(--sw-bone);
                background:
                    radial-gradient(circle at 82% 22%, rgba(95, 211, 230, .08), transparent 30rem),
                    radial-gradient(circle at 14% 84%, rgba(242, 165, 65, .09), transparent 26rem),
                    linear-gradient(155deg, #11131a 0%, var(--sw-ink) 58%, #08090d 100%) !important;
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
            .sw-kicker { margin: 0; color: var(--sw-cyan); font-size: 13px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
            .sw-title { margin: 10px 0 0; font-size: clamp(48px, 6.4vw, 92px); line-height: .92; font-weight: 700; letter-spacing: .015em; }
            .sw-heading { margin: 8px 0 0; font-size: clamp(32px, 4vw, 58px); line-height: 1.02; font-weight: 700; letter-spacing: .01em; }
            .sw-subtitle { margin: 18px 0 0; color: var(--sw-muted); font-size: clamp(18px, 2vw, 28px); line-height: 1.25; }
            .sw-tagline { margin: 20px 0 0; color: var(--sw-amber); font-size: clamp(18px, 1.8vw, 26px); font-style: italic; }
            .sw-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 36px; align-items: center; width: 100%; }
            .sw-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; width: 100%; }
            .sw-copy { max-width: 590px; }
            .sw-copy p { color: var(--sw-muted); font-size: clamp(17px, 1.55vw, 22px); line-height: 1.35; }
            .sw-photo { position: relative; width: min(510px, 42vw); justify-self: end; border: 1px solid var(--sw-line); background: #08090d; box-shadow: 0 26px 80px rgba(0,0,0,.45); overflow: hidden; }
            .sw-photo img { display: block; width: 100%; height: auto; }
            .sw-photo.is-portrait { width: min(370px, 31vw); max-height: min(690px, 74vh); }
            .sw-photo.is-portrait img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
            .sw-photo::after { content: ''; position: absolute; inset: 0; box-shadow: inset 0 0 0 1px rgba(233,228,216,.08); pointer-events: none; }
            .sw-rule { height: 1px; background: linear-gradient(90deg, var(--sw-amber), var(--sw-cyan), transparent); margin: 20px 0; }
            .sw-model { border: 1px solid var(--sw-line); background: rgba(20,23,32,.74); padding: 26px 30px; min-height: 178px; display: grid; align-content: center; }
            .sw-model h3 { margin: 0 0 20px; color: var(--sw-muted); font-size: 14px; letter-spacing: .15em; text-transform: uppercase; }
            .sw-model-flow { display: flex; align-items: center; justify-content: center; gap: 14px; font-family: var(--font-mono); font-size: clamp(17px, 1.6vw, 23px); font-weight: 700; }
            .sw-node { border: 1px solid var(--sw-line); background: var(--sw-panel); padding: 14px 18px; text-align: center; }
            .sw-node.is-amber { border-color: rgba(242,165,65,.5); color: var(--sw-amber); }
            .sw-node.is-cyan { border-color: rgba(95,211,230,.5); color: var(--sw-cyan); }
            .sw-arrow { color: var(--sw-muted); font-size: 28px; }
            .sw-caption { margin: 16px 0 0; color: var(--sw-muted); font-size: 16px; line-height: 1.35; }
            .sw-em { color: var(--sw-bone); }
            .sw-amber { color: var(--sw-amber); }
            .sw-cyan { color: var(--sw-cyan); }
            .sw-crimson { color: var(--sw-crimson); }
            .sw-board-shell { position: relative; width: min(350px, 31vw); margin: 0 auto; }
            .sw-board { display: grid; grid-template-columns: repeat(7, 1fr); aspect-ratio: 1; border: 1px solid var(--sw-line); background: #0a0b0f; padding: 7px; gap: 3px; }
            .sw-cell { position: relative; display: grid; place-items: center; background: #151821; border: 1px solid rgba(233,228,216,.08); color: transparent; font-size: clamp(12px, 1.45vw, 21px); font-weight: 700; }
            .sw-cell.is-wall { background: var(--sw-wall); box-shadow: inset 0 3px rgba(233,228,216,.12); }
            .sw-cell.is-stair { color: var(--sw-bone); background: #1b1d23; outline: 3px double rgba(233,228,216,.55); outline-offset: -7px; }
            .sw-cell.is-enemy { color: var(--sw-crimson); }
            .sw-cell.is-sentry { font-size: clamp(16px, 1.8vw, 26px); }
            .sw-cell.is-lancer { transform: none; }
            .sw-cell.is-glint { color: #fff0c2; }
            .sw-cell.is-player { color: var(--sw-amber); text-shadow: 0 0 16px rgba(242,165,65,.65); }
            .sw-board-label { margin: 12px 0 0; text-align: center; color: var(--sw-muted); font-size: 14px; letter-spacing: .1em; text-transform: uppercase; }
            .sw-board-label b { color: var(--sw-bone); }
            .sw-board-route { position: absolute; inset: 7px; width: calc(100% - 14px); height: calc(100% - 14px); pointer-events: none; overflow: visible; }
            .sw-board-route polyline { fill: none; stroke-width: 3.2; stroke-linecap: round; stroke-linejoin: round; }
            .sw-split-mark { position: absolute; left: 37%; top: 54%; width: 36px; height: 36px; display: grid; place-items: center; border: 2px solid var(--sw-cyan); border-left-color: var(--sw-amber); border-radius: 50%; background: var(--sw-ink); color: var(--sw-bone); font-size: 21px; transform: translate(-50%, -50%); }
            .sw-eq { display: grid; grid-template-columns: 1fr auto 1fr auto 1fr; align-items: center; gap: 14px; width: 100%; margin-top: 34px; }
            .sw-eq .sw-node { min-height: 126px; display: grid; align-content: center; gap: 8px; font-size: clamp(17px, 1.7vw, 24px); }
            .sw-eq small { color: var(--sw-muted); font-size: 14px; font-weight: 400; }
            .sw-code { margin: 0; padding: 18px 20px; border: 1px solid var(--sw-line); background: rgba(8,9,13,.74); color: var(--sw-bone); font-family: var(--font-mono); font-size: clamp(13px, 1.25vw, 17px); line-height: 1.45; white-space: pre; overflow: hidden; }
            .sw-proof { margin: 24px 0 0; padding-left: 18px; border-left: 3px solid var(--sw-amber); color: var(--sw-bone); font-size: clamp(19px, 2vw, 28px); }
            .sw-stream-tree { display: grid; grid-template-columns: 160px 1fr; gap: 18px 34px; align-items: center; margin-top: 24px; }
            .sw-seed-root { grid-row: 1 / 4; align-self: stretch; display: grid; place-items: center; border: 1px solid rgba(242,165,65,.48); color: var(--sw-amber); font-family: var(--font-mono); font-size: 24px; font-weight: 700; }
            .sw-floor-row { display: grid; grid-template-columns: 108px repeat(3, 1fr); gap: 10px; align-items: center; }
            .sw-floor-row strong { color: var(--sw-bone); font-size: 18px; }
            .sw-stream { padding: 13px 10px; border: 1px solid var(--sw-line); background: var(--sw-panel); color: var(--sw-cyan); font-family: var(--font-mono); font-size: 15px; text-align: center; }
            .sw-arch { position: relative; display: grid; grid-template-columns: 1fr 1.2fr 1fr; gap: 44px 70px; align-items: center; margin-top: 30px; }
            .sw-file { position: relative; z-index: 2; padding: 22px; border: 1px solid var(--sw-line); background: var(--sw-panel); min-height: 112px; }
            .sw-file code { color: var(--sw-cyan); font-size: clamp(17px, 1.6vw, 22px); font-weight: 700; }
            .sw-file p { margin: 8px 0 0; color: var(--sw-muted); font-size: 16px; line-height: 1.3; }
            .sw-file.is-rules { grid-column: 2; grid-row: 1 / 3; border-color: rgba(242,165,65,.55); box-shadow: 0 0 42px rgba(242,165,65,.08); }
            .sw-file.is-main { grid-column: 1; grid-row: 1 / 3; }
            .sw-file.is-view { grid-column: 3; grid-row: 1; }
            .sw-file.is-codec { grid-column: 3; grid-row: 2; }
            .sw-connector { position: absolute; z-index: 1; height: 2px; background: linear-gradient(90deg, var(--sw-cyan), rgba(95,211,230,.2)); transform-origin: left center; }
            .sw-connector.one { left: 29%; top: 50%; width: 20%; }
            .sw-connector.two { left: 61%; top: 27%; width: 18%; }
            .sw-connector.three { left: 61%; top: 73%; width: 18%; }
            .sw-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 9px; margin: 22px 0; }
            .sw-action { width: 54px; height: 54px; display: grid; place-items: center; border: 1px solid var(--sw-line); background: var(--sw-panel); color: var(--sw-bone); font-family: var(--font-mono); font-size: 18px; font-weight: 700; }
            .sw-action.is-wait { width: 78px; color: var(--sw-cyan); }
            .sw-action.is-push { border-color: rgba(242,165,65,.5); color: var(--sw-amber); }
            .sw-replay-result { padding: 24px; border: 1px solid rgba(95,211,230,.5); color: var(--sw-cyan); font-size: clamp(20px, 2.1vw, 30px); text-align: center; }
            .sw-code-string { margin: 26px 0 8px; color: var(--sw-bone); font-family: var(--font-mono); font-size: clamp(22px, 3vw, 42px); line-height: 1.25; word-break: break-all; }
            .sw-fields { display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; margin-top: 28px; }
            .sw-field { padding: 16px 10px; border-top: 3px solid var(--sw-cyan); background: var(--sw-panel); text-align: center; }
            .sw-field:nth-child(2n) { border-top-color: var(--sw-amber); }
            .sw-field b { display: block; color: var(--sw-bone); font-size: 16px; }
            .sw-field span { display: block; margin-top: 5px; color: var(--sw-muted); font-size: 12px; }
            .sw-validation { display: grid; grid-template-columns: repeat(6, 1fr); align-items: center; gap: 8px; margin-top: 36px; }
            .sw-check { min-height: 98px; display: grid; place-items: center; border: 1px solid var(--sw-line); background: var(--sw-panel); color: var(--sw-bone); text-align: center; font-size: 17px; font-weight: 700; }
            .sw-check::after { content: '↓'; position: absolute; display: none; }
            .sw-gate { margin-top: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
            .sw-gate div { padding: 16px; text-align: center; border: 1px solid var(--sw-line); font-size: 18px; font-weight: 700; }
            .sw-gate .accept { color: var(--sw-cyan); border-color: rgba(95,211,230,.45); }
            .sw-gate .reject { color: var(--sw-crimson); border-color: rgba(224,70,90,.45); }
            .sw-callouts { display: grid; gap: 14px; }
            .sw-callout { padding: 16px 18px; border-left: 3px solid var(--sw-cyan); background: rgba(20,23,32,.76); color: var(--sw-bone); font-size: 18px; }
            .sw-callout small { display: block; margin-top: 5px; color: var(--sw-muted); font-size: 14px; }
            .sw-timeline { display: grid; grid-template-columns: 110px repeat(6, 1fr); gap: 7px; margin-top: 30px; align-items: stretch; }
            .sw-timeline > div { min-height: 58px; display: grid; place-items: center; border: 1px solid var(--sw-line); background: var(--sw-panel); font-family: var(--font-mono); font-size: 18px; font-weight: 700; }
            .sw-timeline .label { justify-items: start; padding-left: 16px; color: var(--sw-muted); font-family: var(--font-sans); font-size: 15px; }
            .sw-timeline .turn { min-height: 35px; color: var(--sw-muted); background: transparent; border-color: transparent; font-size: 13px; }
            .sw-timeline .a { color: var(--sw-amber); }
            .sw-timeline .b { color: var(--sw-cyan); }
            .sw-timeline .split { position: relative; border-color: var(--sw-amber); box-shadow: inset 0 0 0 2px rgba(95,211,230,.35); }
            .sw-timeline .split::after { content: 'FIRST SPLIT'; position: absolute; top: calc(100% + 13px); color: var(--sw-bone); font-family: var(--font-sans); font-size: 12px; letter-spacing: .12em; white-space: nowrap; }
            .sw-compare { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 22px; }
            .sw-compare h3 { margin: 0 0 18px; font-size: 22px; }
            .sw-compare ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
            .sw-compare li { padding: 10px 0; border-bottom: 1px solid var(--sw-line); color: var(--sw-muted); font-size: 18px; }
            .sw-compare .traditional h3 { color: var(--sw-crimson); }
            .sw-compare .seedwake h3 { color: var(--sw-cyan); }
            .sw-limit { margin-top: 22px; color: var(--sw-muted); font-size: 16px; text-align: center; }
            .sw-close-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; margin: 28px 0; }
            .sw-close-steps div { min-height: 118px; border-top: 3px solid var(--sw-amber); background: var(--sw-panel); padding: 20px; font-size: 20px; }
            .sw-close-steps div:nth-child(2) { border-top-color: var(--sw-cyan); }
            .sw-close-steps b { display: block; margin-bottom: 8px; color: var(--sw-bone); }
            .sw-close-steps span { color: var(--sw-muted); }
            .sw-play { display: inline-block; margin-top: 18px; color: var(--sw-cyan); font-family: var(--font-mono); font-size: clamp(18px, 2vw, 28px); text-decoration: none; border-bottom: 1px solid rgba(95,211,230,.5); }
            .sw-footer { position: absolute; right: 26px; bottom: 18px; color: rgba(233,228,216,.45); font-size: 12px; letter-spacing: .08em; }
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
                .sw-callout { padding: 11px 14px; font-size: 16px; }
                .sw-callout small { margin-top: 3px; font-size: 13px; }
                .sw-callouts + .sw-proof { margin-top: 12px; font-size: 18px; }
            }
            @media (max-width: 660px) {
                .slidewire-frame.sw, .slidewire-frame:has(.sw) { padding: .5rem; }
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
        <section class="dw-wrap" data-dw-motion="sw-title">
            <div class="sw-grid-2">
                <div class="sw-copy" data-sw-reveal>
                    <p class="sw-kicker">Darkwaar 33</p>
                    <h1 class="sw-title">Seedwake</h1>
                    <div class="sw-rule"></div>
                    <p class="sw-subtitle">Asynchronous Multiplayer<br>Without a Server</p>
                    <p class="sw-tagline">Same seed. Same dark. Different choices.</p>
                </div>
                <figure class="sw-photo" data-sw-reveal>
                    <img src="/images/darkwaar33/cover.png" alt="Seedwake tactical board with an amber player and cyan rival ghost">
                    <svg viewBox="0 0 630 500" aria-hidden="true" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;">
                        <path data-sw-path d="M178 409 L178 363 L224 363 L224 316 L270 316" fill="none" stroke="#f2a541" stroke-width="4" stroke-linecap="round" opacity=".72"/>
                        <path data-sw-path d="M224 316 L224 270 L270 270 L270 224" fill="none" stroke="#5fd3e6" stroke-width="4" stroke-linecap="round" opacity=".72"/>
                    </svg>
                </figure>
            </div>
        </section>
        <span class="sw-footer">01 / 14</span>
    </x-slidewire::slide>

    {{-- 2 · THE QUESTION --}}
    {{-- @notes Conventional multiplayer uses a live authority between players. Seedwake replaces that connection with a portable run code. The second player can arrive hours or days later. Nothing must stay online because the code carries enough history to replay the first run locally. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-question">
            <p class="sw-kicker" data-sw-reveal>The question</p>
            <h2 class="sw-heading" data-sw-reveal>Can multiplayer exist without a server?</h2>
            <div class="sw-grid-2" style="margin-top:32px;">
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
            <div class="sw-fragments"><x-slidewire::fragment><span>model two</span></x-slidewire::fragment></div>
        </section>
        <span class="sw-footer">02 / 14</span>
    </x-slidewire::slide>

    {{-- 3 · ONE SEED --}}
    {{-- @notes Both boards come from the actual floor-two layout for seed V9PNR2. The map stream fixes walls and glints. The enemy stream fixes enemy placement. The stair and start also match. A seed therefore becomes a shared reference that both machines can recreate independently. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-seed">
            <p class="sw-kicker" data-sw-reveal>Procedural generation as shared reference</p>
            <h2 class="sw-heading" data-sw-reveal>One seed, one world</h2>
            <div class="sw-grid-2" style="margin-top:24px;">
                <div class="sw-board-shell" data-sw-seeded-board>
                    {!! $renderBoard('Player A floor 2 generated from seed V9PNR2') !!}
                    <p class="sw-board-label"><b>Player A</b> · V9PNR2 · floor 2</p>
                </div>
                <div class="sw-board-shell" data-sw-seeded-board>
                    {!! $renderBoard('Player B floor 2 generated from seed V9PNR2') !!}
                    <p class="sw-board-label"><b>Player B</b> · V9PNR2 · floor 2</p>
                </div>
            </div>
            <p class="sw-caption" data-sw-reveal style="text-align:center;">Actual generated layout: identical walls, enemies, glints, start and stair.</p>
            <div class="sw-fragments"><x-slidewire::fragment><span>second board</span></x-slidewire::fragment></div>
        </section>
        <span class="sw-footer">03 / 14</span>
    </x-slidewire::slide>

    {{-- 4 · DIFFERENT DECISIONS --}}
    {{-- @notes The world stays fixed, but choices do not. The two routes share their first moves and then separate. Because gameplay consumes no random numbers, this fork has one cause: a different action. That lets Seedwake identify the exact decision where two runs became different. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-diverge">
            <div class="sw-grid-2">
                <div class="sw-copy" data-sw-reveal>
                    <p class="sw-kicker">Player choice is the variable</p>
                    <h2 class="sw-heading">The same world does not mean the same outcome</h2>
                    <p class="sw-subtitle"><span class="sw-amber">Amber</span> and <span class="sw-cyan">cyan</span> share a path until one action differs.</p>
                    <p class="sw-proof">Deterministic rules make decisions the only source of divergence.</p>
                </div>
                <div class="sw-board-shell" data-sw-reveal>
                    {!! $renderBoard('Two player routes diverging on the same generated floor') !!}
                    <svg class="sw-board-route" viewBox="0 0 100 100" aria-hidden="true">
                        <polyline data-sw-route points="35.7,92.8 35.7,78.5 35.7,64.2 50,64.2 50,49.9" stroke="#f2a541"/>
                        <polyline data-sw-route points="35.7,92.8 35.7,78.5 35.7,64.2 21.4,64.2 21.4,49.9" stroke="#5fd3e6"/>
                    </svg>
                    <span class="sw-split-mark" data-sw-split>Y</span>
                    <p class="sw-board-label">First different action</p>
                </div>
            </div>
            <div class="sw-fragments"><x-slidewire::fragment><span>cyan route</span></x-slidewire::fragment></div>
        </section>
        <span class="sw-footer">04 / 14</span>
    </x-slidewire::slide>

    {{-- 5 · DETERMINISTIC SIMULATION --}}
    {{-- @notes Each accepted input goes through one pure transition. Rules.apply returns ok, reason, the next state and events for the view. Combat follows already drawn intents, so it never rolls for hits or damage. Rendering receives the result afterward, which keeps frame rate and animation timing outside the rules. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-state">
            <p class="sw-kicker" data-sw-reveal>Deterministic simulation</p>
            <h2 class="sw-heading" data-sw-reveal>Same state + same action = same result</h2>
            <div class="sw-eq">
                <div class="sw-node" data-sw-stage><b>State N</b><small>board, HP, intents, floor turn</small></div>
                <div class="sw-arrow">+</div>
                <div class="sw-node is-amber" data-sw-stage><b>Action</b><small>0…12</small></div>
                <div class="sw-arrow">→</div>
                <div class="sw-node is-cyan" data-sw-stage><b>Rules.apply(state, action)</b><small>{ ok, reason, state, events }</small></div>
            </div>
            <div class="sw-grid-2" style="margin-top:18px;align-items:stretch;">
                <pre class="sw-code" data-sw-reveal>static func apply(
    st_in: Dictionary,
    action: int
) -&gt; Dictionary</pre>
                <div class="sw-model" data-sw-reveal style="min-height:0;padding:18px 22px;">
                    <p style="margin:0;color:var(--sw-bone);font-size:19px;">Combat executes drawn intents.</p>
                    <p class="sw-caption">No RNG calls during play. No Node calls, time, frame delta or engine RNG in <code>rules.gd</code>.</p>
                </div>
            </div>
            <div class="sw-fragments"><x-slidewire::fragment><span>action</span></x-slidewire::fragment><x-slidewire::fragment><span>apply</span></x-slidewire::fragment></div>
        </section>
        <span class="sw-footer">05 / 14</span>
    </x-slidewire::slide>

    {{-- 6 · RANDOM STREAMS --}}
    {{-- @notes Reproducibility needs more than a seeded generator. Seedwake derives named streams with FNV-1a, then advances each stream with xorshift32. Map, enemy and offer generation remain independent for every floor. A change in offer generation cannot shift enemy placement, and gameplay never advances any stream. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap dw-wrap--top" data-dw-motion="sw-streams">
            <p class="sw-kicker" data-sw-reveal>Randomness without drift</p>
            <h2 class="sw-heading" data-sw-reveal>Independent generation streams</h2>
            <div class="sw-grid-2" style="margin-top:20px;align-items:start;">
                <div class="sw-stream-tree" data-sw-reveal>
                    <div class="sw-seed-root">SEED</div>
                    <div class="sw-floor-row"><strong>Floor 1</strong><span class="sw-stream" data-sw-stream>map</span><span class="sw-stream" data-sw-stream>enemy</span><span class="sw-stream" data-sw-stream>offer</span></div>
                    <div class="sw-floor-row"><strong>Floor 2</strong><span class="sw-stream" data-sw-stream>map</span><span class="sw-stream" data-sw-stream>enemy</span><span class="sw-stream" data-sw-stream>offer</span></div>
                    <div class="sw-floor-row"><strong>Floor 7</strong><span class="sw-stream" data-sw-stream>map</span><span class="sw-stream" data-sw-stream>enemy</span><span class="sw-stream" data-sw-stream>offer</span></div>
                </div>
                <pre class="sw-code" data-sw-reveal>static func stream(seed, floor_n, name):
    var h = fnv1a32(
        "dw33|%d|%d|%s" %
        [seed, floor_n, name]
    )
    return h if h != 0 else 1

static func xorshift32(s):
    s ^= (s &lt;&lt; 13) &amp; MASK
    s ^= s &gt;&gt; 17
    s ^= (s &lt;&lt; 5) &amp; MASK
    return s &amp; MASK</pre>
            </div>
            <p class="sw-proof" data-sw-reveal>No random stream advances during gameplay.</p>
            <div class="sw-fragments"><x-slidewire::fragment><span>streams</span></x-slidewire::fragment><x-slidewire::fragment><span>all streams</span></x-slidewire::fragment></div>
        </section>
        <span class="sw-footer">06 / 14</span>
    </x-slidewire::slide>

    {{-- 7 · ARCHITECTURE --}}
    {{-- @notes The architecture stays small because responsibility stays clear. Main converts input to action codes and invokes rules. Rules owns all state transitions. BoardView only reads state and events to draw or animate. Codec packs histories and asks Rules.simulate to verify imports. Rules never depends on the UI. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-arch">
            <p class="sw-kicker" data-sw-reveal>A small architecture</p>
            <h2 class="sw-heading" data-sw-reveal>Rules, rendering and interaction stay separate</h2>
            <div class="sw-arch">
                <span class="sw-connector one"></span><span class="sw-connector two"></span><span class="sw-connector three"></span>
                <div class="sw-file is-main" data-sw-reveal><code>main.gd</code><p>Input, screens, rival tracking. Calls <code>Rules.apply()</code>.</p></div>
                <div class="sw-file is-rules" data-sw-reveal><code>rules.gd</code><p>Pure state transitions, generation, replay trace and ghost lookup.</p></div>
                <div class="sw-file is-view" data-sw-reveal><code>board_view.gd</code><p>Reads state and events. Draws animation, intents, ghost and fork.</p></div>
                <div class="sw-file is-codec" data-sw-reveal><code>codec.gd</code><p>Encodes runs. Imports through <code>Rules.simulate()</code>.</p></div>
            </div>
            <p class="sw-caption" data-sw-reveal style="text-align:center;">Dependency direction ends at <code>rules.gd</code>. The rules never call the view.</p>
        </section>
        <span class="sw-footer">07 / 14</span>
    </x-slidewire::slide>

    {{-- 8 · REPLAY --}}
    {{-- @notes A replay stores accepted action opcodes, not pixels or transforms. The example uses the real vocabulary: four step directions, wait, four dash directions, three purchase slots and descend. A step into an enemy may resolve as a push, but push itself is an outcome rather than a separate opcode. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-replay">
            <p class="sw-kicker" data-sw-reveal>Replay as data</p>
            <h2 class="sw-heading" data-sw-reveal>A replay is a list of actions</h2>
            <div class="sw-grid-2" style="margin-top:28px;align-items:start;">
                <div data-sw-reveal>
                    <p class="sw-subtitle" style="margin-top:0;">Seed <span class="sw-cyan" style="font-family:var(--font-mono);">K7Q2MX</span></p>
                    <div class="sw-actions">
                        <span class="sw-action" data-sw-action>N</span><span class="sw-action" data-sw-action>E</span><span class="sw-action" data-sw-action>E</span><span class="sw-action is-wait" data-sw-action>WAIT</span><span class="sw-action is-push" data-sw-action>W</span><span class="sw-action" data-sw-action>N</span>
                    </div>
                    <p class="sw-caption"><span class="sw-amber">W resolves as PUSH</span> when an enemy occupies the destination. PUSH is not a separate encoded action.</p>
                </div>
                <div>
                    <pre class="sw-code" data-sw-reveal>static func simulate(
    seed: int,
    actions: PackedByteArray
) -&gt; Dictionary</pre>
                    <div class="sw-replay-result" data-sw-result>Reconstructed run<br><small style="color:var(--sw-muted);">final state + trace</small></div>
                </div>
            </div>
            <p class="sw-proof" data-sw-reveal>No video recording. No saved state snapshots.</p>
            <div class="sw-fragments"><x-slidewire::fragment><span>actions</span></x-slidewire::fragment><x-slidewire::fragment><span>more actions</span></x-slidewire::fragment></div>
        </section>
        <span class="sw-footer">08 / 14</span>
    </x-slidewire::slide>

    {{-- 9 · RUN CODE --}}
    {{-- @notes Codec packs two actions into every byte because each action fits in four bits. The byte payload contains a version, 32-bit seed, three-letter tag, claimed result, action count, packed actions and a 16-bit FNV-1a checksum. Crockford base32 makes the binary copyable and forgiving to type. The string shown here is deliberately abbreviated. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-code">
            <p class="sw-kicker" data-sw-reveal>Portable run codes</p>
            <h2 class="sw-heading" data-sw-reveal>A complete game history in a short string</h2>
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
            <div class="sw-grid-2" style="margin-top:22px;align-items:stretch;">
                <div class="sw-model" data-sw-reveal style="min-height:0;padding:18px 22px;"><p style="margin:0;color:var(--sw-bone);font-size:20px;">Crockford base32</p><p class="sw-caption">Uppercase, copyable, tolerant of O/0 and I/L/1.</p></div>
                <pre class="sw-code" data-sw-reveal style="display:grid;align-content:center;">hi = actions[i] &amp; 15
lo = actions[i + 1] &amp; 15
byte = (hi &lt;&lt; 4) | lo</pre>
            </div>
        </section>
        <span class="sw-footer">09 / 14</span>
    </x-slidewire::slide>

    {{-- 10 · VALIDATION --}}
    {{-- @notes The claimed score never earns trust. Import first decodes the payload, checks its checksum and rules version, then re-simulates every action. Illegal or unfinished histories fail. Finally it compares simulated score, floors and turns with the claims in the code. This catches damage and edits, but it does not prove who played the run. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-validate">
            <p class="sw-kicker" data-sw-reveal>Replay validation</p>
            <h2 class="sw-heading" data-sw-reveal>Never trust the claimed result</h2>
            <div class="sw-validation">
                <div class="sw-check" data-sw-check>Decode</div><div class="sw-check" data-sw-check>Checksum</div><div class="sw-check" data-sw-check>Version</div><div class="sw-check" data-sw-check>Simulate</div><div class="sw-check" data-sw-check>Compare<br>results</div><div class="sw-check" data-sw-check>Decision</div>
            </div>
            <div class="sw-gate" data-sw-reveal><div class="accept">ACCEPT<br><small>same score, floors and turns</small></div><div class="reject">REJECT<br><small>corrupt, illegal, unfinished or mismatched</small></div></div>
            <p class="sw-proof" data-sw-reveal>Consistency check, not cryptographic anti-cheat.</p>
            <div class="sw-fragments"><x-slidewire::fragment><span>checksum</span></x-slidewire::fragment><x-slidewire::fragment><span>simulate</span></x-slidewire::fragment><x-slidewire::fragment><span>compare</span></x-slidewire::fragment><x-slidewire::fragment><span>decision</span></x-slidewire::fragment></div>
        </section>
        <span class="sw-footer">10 / 14</span>
    </x-slidewire::slide>

    {{-- 11 · GHOST --}}
    {{-- @notes The cyan rival is a rendering of the validated trace. Ghost lookup uses floor number and floor turn, so each player can spend a different number of turns on earlier floors. The last four positions form the wake. If the rival already left or died, the view holds an exit or death marker. None of this can change the live rules state. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-ghost">
            <div class="sw-grid-2">
                <figure class="sw-photo is-portrait" data-sw-reveal style="justify-self:center;">
                    <img src="/images/darkwaar33/screenshot-1.png" alt="Seedwake gameplay showing a cyan rival ghost, wake trail, enemy intentions and split marker">
                </figure>
                <div class="sw-copy" data-sw-reveal>
                    <p class="sw-kicker">The rival ghost</p>
                    <h2 class="sw-heading">Another player’s history, not another live player</h2>
                    <div class="sw-callouts" style="margin-top:24px;">
                        <div class="sw-callout" data-sw-callout>Cyan ghost + four-cell wake<small>Rendered from the rival trace.</small></div>
                        <div class="sw-callout" data-sw-callout>Floor and floor-turn synchronization<small>Floor 2, turn 6 maps to the rival’s floor 2, turn 6.</small></div>
                        <div class="sw-callout" data-sw-callout>Exit and death markers<small>The last position remains visible after the rival stops walking.</small></div>
                    </div>
                    <p class="sw-proof">The ghost never mutates your world.</p>
                </div>
            </div>
        </section>
        <span class="sw-footer">11 / 14</span>
    </x-slidewire::slide>

    {{-- 12 · FIRST DIVERGENCE --}}
    {{-- @notes Both action arrays match through index three. At the next action, amber moves north while cyan moves west. Main records that first mismatch once, together with the pre-action floor, turn and cell. If one sequence merely ends first, Seedwake treats it as a matching prefix rather than inventing a split. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-timeline">
            <p class="sw-kicker" data-sw-reveal>Decision comparison</p>
            <h2 class="sw-heading" data-sw-reveal>Where did our decisions become different?</h2>
            <div class="sw-timeline" data-sw-reveal>
                <div class="turn label">Turn</div><div class="turn">1</div><div class="turn">2</div><div class="turn">3</div><div class="turn">4</div><div class="turn">5</div><div class="turn">6</div>
                <div class="label a">Player A</div><div class="a">N</div><div class="a">E</div><div class="a">E</div><div class="a split" data-sw-timeline-split>N</div><div class="a">W</div><div class="a">S</div>
                <div class="label b">Player B</div><div class="b">N</div><div class="b">E</div><div class="b">E</div><div class="b split" data-sw-timeline-split>W</div><div class="b">N</div><div class="b">S</div>
            </div>
            <p class="sw-proof" data-sw-reveal style="margin-top:54px;">The fork identifies a decision, not merely a score difference.</p>
            <p class="sw-caption" data-sw-reveal><code>Rules.first_split(mine, theirs)</code> returns the first unequal action index, or −1 while one run remains a prefix.</p>
            <div class="sw-fragments"><x-slidewire::fragment><span>split</span></x-slidewire::fragment></div>
        </section>
        <span class="sw-footer">12 / 14</span>
    </x-slidewire::slide>

    {{-- 13 · WHY NO BACKEND --}}
    {{-- @notes Seedwake avoids infrastructure by narrowing the product promise. It needs shared conditions, a recorded history and local verification. It does not offer discovery, a global leaderboard or authoritative identity. A backend would become necessary if those promises changed, but it is unnecessary for this form of rivalry. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-backend">
            <p class="sw-kicker" data-sw-reveal>Infrastructure avoided by design</p>
            <h2 class="sw-heading" data-sw-reveal>Why no backend?</h2>
            <div class="sw-compare">
                <div class="traditional" data-sw-reveal><h3>Traditional online competition</h3><ul><li>Authentication</li><li>Persistent storage</li><li>Matchmaking</li><li>Server-side validation</li><li>Global leaderboards</li></ul></div>
                <div class="seedwake" data-sw-reveal><h3>Seedwake rivalry</h3><ul><li>Shared seed</li><li>Recorded actions</li><li>Deterministic simulation</li><li>Portable run code</li><li>Local verification</li></ul></div>
            </div>
            <p class="sw-limit" data-sw-reveal>Trade-off: no opponent discovery, global leaderboard or authoritative anti-cheat.</p>
        </section>
        <span class="sw-footer">13 / 14</span>
    </x-slidewire::slide>

    {{-- 14 · CONCLUSION --}}
    {{-- @notes The architecture reduces asynchronous multiplayer to three portable facts. A seed defines the world. The action list defines what happened. Replay rebuilds and checks the result. That is enough to race a ghost, compare floors and point to the first decision that changed the run. Seedwake is playable now in the browser. --}}
    <x-slidewire::slide class="dw-slide sw">
        <section class="dw-wrap" data-dw-motion="sw-close">
            <div class="sw-grid-2">
                <div class="sw-copy" data-sw-reveal>
                    <p class="sw-kicker">Conclusion</p>
                    <h2 class="sw-heading">Determinism makes gameplay portable</h2>
                    <div class="sw-close-steps">
                        <div><b>Seed</b><span>defines the world</span></div>
                        <div><b>Actions</b><span>define the history</span></div>
                        <div><b>Replay</b><span>reconstructs the result</span></div>
                    </div>
                    <p class="sw-subtitle" style="color:var(--sw-bone);"><b>Darkwaar33 — Seedwake</b></p>
                    <a class="sw-play" href="https://darkwoodcom.itch.io/darkwaar33">darkwoodcom.itch.io/darkwaar33</a>
                    <p class="sw-tagline">Same seed. Same dark. Different choices.</p>
                </div>
                <figure class="sw-photo is-portrait" data-sw-reveal>
                    <img src="/images/darkwaar33/screenshot-2.png" alt="Seedwake end screen comparing floor-by-floor results with a rival">
                </figure>
            </div>
        </section>
        <span class="sw-footer">14 / 14</span>
    </x-slidewire::slide>

</x-slidewire::deck>
