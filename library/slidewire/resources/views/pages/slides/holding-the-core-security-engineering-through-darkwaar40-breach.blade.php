{{-- Holding the Core: Security Engineering Through Darkwaar40 — Breach --}}
{{-- Route: /slides/holding-the-core-security-engineering-through-darkwaar40-breach --}}
{{-- Source: tasks/#31e548_darkwaar40/article.md --}}

<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    {{-- 1 · TITLE --}}
    {{-- @notes Main: Darkwaar40 turns security operations into a 100-second survival loop. Technical: Frame this as a metaphor, not a simulator. Transition: Start with the rules the player actually faces. Duration: 30 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide hc-hero">
        <style>
            .slidewire-frame.hc-slide,
            .slidewire-frame:has(.hc-slide) { overflow: hidden; padding: 0; background: #06050e; }
            .hc-slide {
                --hc-bg: #06050e;
                --hc-panel: rgba(18, 17, 34, .88);
                --hc-panel-soft: rgba(18, 17, 34, .68);
                --hc-text: #f4f1fa;
                --hc-muted: #aaa4bb;
                --hc-teal: #45e0cb;
                --hc-magenta: #ff4fc6;
                --hc-gold: #ffd36a;
                --hc-coral: #ff786e;
                --hc-line: rgba(69, 224, 203, .25);
                min-height: 100vh;
                color: var(--hc-text);
                background:
                    radial-gradient(circle at 82% 25%, rgba(255, 79, 198, .16), transparent 28rem),
                    radial-gradient(circle at 20% 78%, rgba(69, 224, 203, .12), transparent 31rem),
                    linear-gradient(145deg, #05040c 0%, #0b0815 52%, #070a13 100%);
                position: relative;
                overflow: hidden;
            }
            .hc-slide::before {
                content: '';
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
                background-size: 54px 54px;
                mask-image: linear-gradient(to bottom, #000, transparent 92%);
                pointer-events: none;
            }
            .hc-slide::after {
                content: '';
                position: absolute;
                inset: auto 0 0;
                height: 6px;
                background: linear-gradient(90deg, var(--hc-magenta), var(--hc-teal), transparent 84%);
                z-index: 4;
            }
            .hc-wrap {
                position: relative;
                z-index: 2;
                width: min(1180px, calc(100vw - 112px));
                min-height: 100vh;
                margin: 0 auto;
                padding: 58px 0 62px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                box-sizing: border-box;
            }
            .hc-top { justify-content: flex-start; padding-top: 48px; }
            .hc-kicker { margin: 0 0 14px; color: var(--hc-teal); font-size: 14px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
            .hc-title { margin: 0; max-width: 820px; font-size: clamp(58px, 7vw, 112px); line-height: .88; font-weight: 700; letter-spacing: -.045em; }
            .hc-subtitle { margin: 22px 0 0; max-width: 780px; color: var(--hc-muted); font-size: clamp(22px, 2.3vw, 36px); line-height: 1.2; }
            .hc-heading { margin: 0; font-size: clamp(40px, 4.7vw, 72px); line-height: .98; font-weight: 700; letter-spacing: -.035em; text-wrap: balance; }
            .hc-heading-small { font-size: clamp(34px, 3.8vw, 58px); }
            .hc-lead { margin: 20px 0 0; max-width: 940px; color: var(--hc-muted); font-size: clamp(20px, 2vw, 29px); line-height: 1.35; }
            .hc-accent { color: var(--hc-teal); }
            .hc-threat { color: var(--hc-magenta); }
            .hc-gold { color: var(--hc-gold); }
            .hc-muted { color: var(--hc-muted); }
            .hc-grid { display: grid; gap: 16px; margin-top: 28px; }
            .hc-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .hc-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .hc-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .hc-grid > *, .hc-split > *, .hc-release > *, .hc-state > *, .hc-code-grid > *, .hc-auth > * { min-width: 0; }
            .hc-card {
                border: 1px solid var(--hc-line);
                border-radius: 14px;
                padding: 20px 22px;
                background: var(--hc-panel-soft);
                box-shadow: 0 20px 55px rgba(0,0,0,.32);
                backdrop-filter: blur(14px);
            }
            .hc-card h3 { margin: 0; color: var(--hc-teal); font-size: 22px; line-height: 1.1; }
            .hc-card p { margin: 9px 0 0; color: var(--hc-muted); font-size: 17px; line-height: 1.35; }
            .hc-card strong { color: var(--hc-text); }
            .hc-chip { border: 1px solid var(--hc-line); border-radius: 999px; padding: 11px 15px; background: var(--hc-panel-soft); color: var(--hc-text); font-size: 17px; font-weight: 700; text-align: center; }
            .hc-callout { margin-top: 28px; padding: 19px 23px; border-left: 4px solid var(--hc-teal); background: var(--hc-panel); font-size: clamp(22px, 2.25vw, 32px); line-height: 1.3; }
            .hc-footer { position: absolute; z-index: 5; left: 32px; right: 32px; bottom: 16px; display: flex; justify-content: space-between; align-items: center; color: #777184; font-size: 12px; letter-spacing: .1em; text-transform: uppercase; }
            .hc-footer img { width: 24px; height: 24px; object-fit: contain; }
            .hc-media { position: relative; border: 1px solid rgba(69,224,203,.28); border-radius: 16px; background: #05040b; overflow: hidden; box-shadow: 0 28px 80px rgba(0,0,0,.52), 0 0 48px rgba(255,79,198,.08); }
            .hc-media img { display: block; width: 100%; height: 100%; object-fit: cover; }
            .hc-phone { width: min(360px, 31vw); height: min(690px, 73vh); margin-left: auto; }
            .hc-phone img { object-position: center 31%; }
            .hc-split { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(260px, .7fr); gap: 48px; align-items: center; }
            .hc-cover { position: absolute; top: -9vh; right: 6vw; width: min(430px, 33vw); height: 118vh; opacity: .78; filter: saturate(1.16) contrast(1.05); mask-image: linear-gradient(to left, #000 70%, transparent); }
            .hc-cover img { width: 100%; height: 100%; object-fit: cover; }
            .hc-hero-copy { position: relative; z-index: 2; max-width: 850px; }
            .hc-brand { margin-top: 34px; display: flex; gap: 14px; align-items: center; color: var(--hc-muted); font-size: 15px; letter-spacing: .08em; text-transform: uppercase; }
            .hc-brand img { width: 36px; height: 36px; }
            .hc-stat-row { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; margin-top: 22px; }
            .hc-stat { padding: 14px 10px; border: 1px solid rgba(255,79,198,.24); border-radius: 10px; background: rgba(11,8,21,.82); text-align: center; }
            .hc-stat strong { display: block; color: var(--hc-gold); font-size: 25px; }
            .hc-stat span { display: block; margin-top: 3px; color: var(--hc-muted); font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
            .hc-thesis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); align-items: center; margin-top: 54px; }
            .hc-thesis-node { position: relative; display: grid; place-items: center; min-height: 150px; padding: 18px; border: 1px solid var(--hc-line); border-radius: 50%; background: var(--hc-panel); color: var(--hc-text); font-size: 21px; font-weight: 700; text-align: center; }
            .hc-thesis-node:not(:last-child)::after { content: '→'; position: absolute; right: -23px; color: var(--hc-magenta); font-size: 28px; }
            .hc-map { display: grid; grid-template-columns: 1fr auto 1.35fr; gap: 11px 18px; align-items: center; margin-top: 24px; }
            .hc-map > span { padding: 11px 15px; border-radius: 10px; background: var(--hc-panel-soft); font-size: 19px; }
            .hc-map .hc-map-game { color: var(--hc-gold); font-weight: 700; }
            .hc-map .hc-map-arrow { padding: 0; background: none; color: var(--hc-magenta); }
            .hc-map .hc-map-security { color: var(--hc-text); }
            .hc-loop { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 48px; }
            .hc-loop-step { flex: 1; min-height: 78px; padding: 18px 12px; border: 1px solid var(--hc-line); border-radius: 12px; background: var(--hc-panel); display: grid; place-items: center; color: var(--hc-text); font-size: 18px; font-weight: 700; text-align: center; }
            .hc-loop-arrow { color: var(--hc-magenta); font-size: 25px; }
            .hc-portal-field { position: relative; width: min(560px, 46vw); aspect-ratio: 1; margin-left: auto; }
            .hc-core { position: absolute; inset: 39%; border: 7px double var(--hc-teal); border-radius: 50%; background: #141124; box-shadow: 0 0 26px rgba(69,224,203,.55); display: grid; place-items: center; color: var(--hc-teal); font-size: 22px; font-weight: 700; }
            .hc-portal { position: absolute; width: 112px; height: 112px; border: 6px double var(--hc-magenta); border-radius: 50%; background: radial-gradient(circle, rgba(255,79,198,.32), #100918 65%); box-shadow: 0 0 32px rgba(255,79,198,.34); display: grid; place-items: center; color: #ffc4ec; font-size: 13px; font-weight: 700; text-align: center; text-transform: uppercase; letter-spacing: .07em; }
            .hc-portal.p1 { top: 2%; left: 40%; }
            .hc-portal.p2 { left: 2%; bottom: 13%; }
            .hc-portal.p3 { right: 2%; bottom: 13%; }
            .hc-portal-line { position: absolute; height: 1px; width: 34%; background: linear-gradient(90deg, var(--hc-magenta), transparent); transform-origin: left center; opacity: .65; }
            .hc-exposure { display: grid; grid-template-columns: 1fr 80px 1fr; gap: 24px; align-items: center; margin-top: 35px; }
            .hc-exposure-side { min-height: 320px; padding: 28px; border: 1px solid rgba(255,79,198,.28); border-radius: 18px; background: var(--hc-panel); }
            .hc-exposure-side.is-reduced { border-color: rgba(69,224,203,.42); }
            .hc-endpoint { display: inline-flex; margin: 6px 4px; padding: 9px 12px; border: 1px solid rgba(255,79,198,.3); border-radius: 8px; color: #e7c5df; font-family: var(--font-mono); font-size: 15px; }
            .hc-exposure-side.is-reduced .hc-endpoint { border-color: rgba(69,224,203,.32); color: #c6fff6; }
            .hc-big-arrow { color: var(--hc-teal); font-size: 52px; text-align: center; }
            .hc-upgrade-layout { display: grid; grid-template-columns: minmax(0, 1fr) 285px; gap: 20px; align-items: stretch; margin-top: 28px; }
            .hc-upgrades { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 0; }
            .hc-upgrade { min-height: 145px; padding: 18px; border: 1px solid var(--hc-line); border-radius: 16px; background: var(--hc-panel); }
            .hc-upgrade b { display: block; color: var(--hc-teal); font-size: 24px; }
            .hc-upgrade span { display: block; margin-top: 10px; color: var(--hc-muted); font-size: 16px; line-height: 1.35; }
            .hc-upgrade em { display: block; margin-top: 18px; color: var(--hc-gold); font-style: normal; font-size: 13px; text-transform: uppercase; letter-spacing: .08em; }
            .hc-patch-shot { height: 304px; border: 1px solid rgba(69,224,203,.3); border-radius: 16px; overflow: hidden; background: #05040b; }
            .hc-patch-shot img { width: 100%; height: 100%; object-fit: cover; object-position: center 42%; }
            .hc-release { display: grid; grid-template-columns: 1fr 1.2fr; gap: 28px; margin-top: 30px; }
            .hc-release-critical { padding: 28px; border: 1px solid rgba(255,120,110,.5); border-radius: 16px; background: rgba(80,18,25,.22); }
            .hc-release-critical strong { display: block; color: var(--hc-coral); font-size: 62px; line-height: 1; }
            .hc-release-critical span { color: var(--hc-muted); font-size: 20px; }
            .hc-version-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .hc-version { padding: 18px; border: 1px solid var(--hc-line); border-radius: 12px; background: var(--hc-panel); color: var(--hc-text); font-family: var(--font-mono); font-size: 21px; text-align: center; }
            .hc-formula { margin-top: 48px; display: flex; align-items: stretch; justify-content: center; gap: 10px; }
            .hc-factor { width: 190px; min-height: 145px; display: grid; place-items: center; padding: 18px; border: 1px solid var(--hc-line); border-radius: 14px; background: var(--hc-panel); color: var(--hc-text); font-size: 22px; font-weight: 700; text-align: center; }
            .hc-times { align-self: center; color: var(--hc-magenta); font-size: 30px; }
            .hc-layers { margin: 28px auto 0; width: min(900px, 76vw); display: grid; gap: 8px; }
            .hc-layer { min-height: 58px; padding: 12px 24px; border: 1px solid var(--hc-line); border-radius: 12px; background: var(--hc-panel); display: flex; align-items: center; justify-content: space-between; font-size: 18px; }
            .hc-layer:nth-child(odd) { margin: 0 7%; }
            .hc-layer b { color: var(--hc-teal); }
            .hc-layer span { color: var(--hc-muted); }
            .hc-code-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 22px; align-items: start; margin-top: 22px; }
            .hc-code-label { margin: 0 0 7px; color: var(--hc-teal); font-size: 13px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
            .hc-slide pre.phiki,
            .hc-slide pre.slidewire-code { margin: 0 !important; padding: 18px 20px !important; border: 1px solid var(--hc-line); border-radius: 12px !important; background: #0b0a13 !important; font-size: 14px !important; line-height: 1.42 !important; max-height: 520px; max-width: 100%; overflow-x: auto; }
            .hc-auth { display: grid; grid-template-columns: 1fr 82px 1fr; gap: 20px; align-items: center; margin-top: 46px; }
            .hc-auth-box { min-height: 240px; padding: 28px; border: 1px solid var(--hc-line); border-radius: 16px; background: var(--hc-panel); }
            .hc-auth-box h3 { margin: 0; color: var(--hc-teal); font-size: 29px; }
            .hc-auth-box p { color: var(--hc-muted); font-size: 18px; }
            .hc-auth-arrow { color: var(--hc-gold); font-size: 46px; text-align: center; }
            .hc-list { margin: 16px 0 0; padding: 0; list-style: none; }
            .hc-list li { position: relative; margin: 9px 0; padding-left: 20px; color: var(--hc-muted); font-size: 18px; line-height: 1.3; }
            .hc-list li::before { content: '›'; position: absolute; left: 0; color: var(--hc-magenta); font-weight: 700; }
            .hc-sequence { margin-top: 25px; display: grid; grid-template-columns: 145px minmax(0, 1fr) 145px; gap: 8px 14px; align-items: center; }
            .hc-actor { grid-row: span 8; align-self: stretch; display: grid; place-items: center; border: 1px solid var(--hc-line); border-radius: 14px; background: var(--hc-panel); color: var(--hc-teal); font-size: 20px; font-weight: 700; text-align: center; }
            .hc-seq-step { grid-column: 2; padding: 10px 16px; border: 1px solid rgba(255,255,255,.11); border-radius: 9px; background: rgba(18,17,34,.72); color: var(--hc-text); font-size: 15px; text-align: center; }
            .hc-seq-step b { color: var(--hc-gold); }
            .hc-trust { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-top: 28px; }
            .hc-trust-item { min-height: 114px; padding: 15px; border: 1px solid var(--hc-line); border-radius: 12px; background: var(--hc-panel); }
            .hc-trust-item b { display: block; color: var(--hc-teal); font-family: var(--font-mono); font-size: 18px; }
            .hc-trust-item span { display: block; margin-top: 7px; color: var(--hc-muted); font-size: 15px; line-height: 1.3; }
            .hc-state { display: grid; grid-template-columns: 1fr 90px 1fr; gap: 20px; align-items: center; margin-top: 34px; }
            .hc-state-box { min-height: 330px; padding: 24px; border: 1px solid var(--hc-line); border-radius: 16px; background: var(--hc-panel); }
            .hc-state-box h3 { margin: 0 0 16px; color: var(--hc-teal); font-size: 25px; }
            .hc-state-box.is-target { border-style: dashed; opacity: .78; }
            .hc-state-arrow { color: var(--hc-magenta); font-size: 50px; text-align: center; }
            .hc-caveat { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 30px; }
            .hc-caveat .hc-card { min-height: 145px; border-color: rgba(255,120,110,.28); }
            .hc-final-loop { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 40px; }
            .hc-final-loop .hc-card { min-height: 104px; display: grid; place-items: center; font-size: 21px; font-weight: 700; text-align: center; }
            .hc-url { display: inline-block; margin-top: 28px; padding: 15px 19px; border: 1px solid var(--hc-line); border-radius: 10px; background: var(--hc-panel); color: var(--hc-teal); font-family: var(--font-mono); font-size: clamp(15px, 1.55vw, 22px); text-decoration: none; }
            .hc-source { margin-top: 14px; color: #777184; font-size: 12px; line-height: 1.3; }
            .hc-source a { color: #9590a1; text-decoration: none; }
            @media (max-width: 900px), (max-height: 700px) {
                .hc-wrap { width: min(1160px, calc(100vw - 56px)); padding-top: 40px; }
                .hc-card p, .hc-list li { font-size: 15px; }
                .hc-upgrade { min-height: 180px; }
                .hc-sequence { transform: scale(.92); transform-origin: top center; }
            }
            @media (prefers-reduced-motion: reduce) {
                .hc-slide *, .hc-slide *::before, .hc-slide *::after { animation: none !important; transition: none !important; }
            }
        </style>
        <div class="hc-cover" aria-hidden="true"><img src="/images/holding-the-core/cover.png" alt=""></div>
        <section class="hc-wrap">
            <div class="hc-hero-copy">
                <p class="hc-kicker">Darkwood · Security Engineering · Darkwaar40</p>
                <h1 class="hc-title">Holding<br>the Core</h1>
                <p class="hc-subtitle">Security Engineering Through <span class="hc-threat">Darkwaar40 — Breach</span></p>
                <div class="hc-brand"><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"><span>Darkwood technical session · @matyo91</span></div>
            </div>
        </section>
        <footer class="hc-footer"><span>01 · Holding the Core</span><span>20–25 min</span></footer>
    </x-slidewire::slide>

    {{-- 2 · 100 SECONDS --}}
    {{-- @notes Main: Establish the real game loop. Technical: Three portals, Auto-Scan, 100 Integrity, temporary sealing, patch choices, 100-second objective. Transition: The loop encodes a security thesis. Duration: 60 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <div class="hc-split">
                <div>
                    <p class="hc-kicker">Act I · The game</p>
                    <h2 class="hc-heading">100 Seconds to Survive</h2>
                    <div class="hc-stat-row">
                        <div class="hc-stat"><strong>3</strong><span>portals</span></div>
                        <div class="hc-stat"><strong>Auto</strong><span>scan</span></div>
                        <div class="hc-stat"><strong>100</strong><span>integrity</span></div>
                        <div class="hc-stat"><strong>6s+</strong><span>seals</span></div>
                        <div class="hc-stat"><strong>4</strong><span>patch lines</span></div>
                        <div class="hc-stat"><strong>100s</strong><span>objective</span></div>
                    </div>
                    <p class="hc-lead">Move, contain the portals, and let Auto-Scan clear nearby threats before they reach the Core.</p>
                </div>
                <figure class="hc-media hc-phone"><img src="/images/holding-the-core/gameplay.png" alt="Darkwaar40 gameplay showing three breach portals around the central Core"></figure>
            </div>
        </section>
        <footer class="hc-footer"><span>02 · Implemented game loop</span><span>Real gameplay capture</span></footer>
    </x-slidewire::slide>

    {{-- 3 · SECURITY IS NOT A WALL --}}
    {{-- @notes Main: State the thesis. Technical: Security is an operating loop across exposure, detection, response, and resilience. Transition: Map each game signal to a concept, with limits. Duration: 55 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <p class="hc-kicker">The thesis</p>
            <h2 class="hc-heading">Security is not a wall.</h2>
            <div class="hc-thesis">
                <div class="hc-thesis-node">Reduce<br><span class="hc-accent">exposure</span></div>
                <div class="hc-thesis-node">Detect<br><span class="hc-accent">activity</span></div>
                <div class="hc-thesis-node">Respond<br><span class="hc-accent">under pressure</span></div>
                <div class="hc-thesis-node">Maintain<br><span class="hc-accent">resilience</span></div>
            </div>
            <p class="hc-callout">Security is continuous operational work—not a perimeter completed once.</p>
        </section>
        <footer class="hc-footer"><span>03 · Continuous process</span></footer>
    </x-slidewire::slide>

    {{-- 4 · SECURITY MODEL --}}
    {{-- @notes Main: Use the game as a conceptual map. Technical: Each mapping is evocative rather than equivalent; confidentiality is especially underrepresented. Transition: Begin with the detection layer. Duration: 70 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap hc-top">
            <p class="hc-kicker">The security model</p>
            <h2 class="hc-heading hc-heading-small">A readable metaphor—with boundaries</h2>
            <div class="hc-map">
                <span class="hc-map-game">Portals</span><span class="hc-map-arrow">→</span><span class="hc-map-security">Attack surface</span>
                <span class="hc-map-game">Auto-Scan</span><span class="hc-map-arrow">→</span><span class="hc-map-security">Detection and triage</span>
                <span class="hc-map-game">Temporary seals</span><span class="hc-map-arrow">→</span><span class="hc-map-security">Containment</span>
                <span class="hc-map-game">Patch choices</span><span class="hc-map-arrow">→</span><span class="hc-map-security">Remediation investments</span>
                <span class="hc-map-game">Core Integrity</span><span class="hc-map-arrow">→</span><span class="hc-map-security">Integrity + availability</span>
                <span class="hc-map-game">Enemy pressure</span><span class="hc-map-arrow">→</span><span class="hc-map-security">Evolving threats</span>
            </div>
            <p class="hc-callout"><strong>Not equivalence:</strong> a game condenses uncertainty, people, data loss, and recovery into legible decisions.</p>
        </section>
        <footer class="hc-footer"><span>04 · Metaphor ≠ implementation</span></footer>
    </x-slidewire::slide>

    {{-- 5 · DETECTION --}}
    {{-- @notes Main: Detection begins after a signal exists. Technical: Preventive controls reduce likelihood; detective controls generate evidence that must enter an owned response loop. Transition: An open portal illustrates the exposure that prevention tries to reduce. Duration: 70 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <p class="hc-kicker">Act II · Defensive security</p>
            <h2 class="hc-heading">Detection is not prevention.</h2>
            <div class="hc-grid hc-grid-2">
                <div class="hc-card"><h3>Preventive</h3><p>Validation, least privilege, patching, and network restrictions constrain what can happen.</p></div>
                <div class="hc-card"><h3>Detective</h3><p>Logs, metrics, traces, anomaly signals, and alerts reveal what may already be happening.</p></div>
            </div>
            <div class="hc-loop">
                <div class="hc-loop-step">Observe</div><div class="hc-loop-arrow">→</div>
                <div class="hc-loop-step">Identify</div><div class="hc-loop-arrow">→</div>
                <div class="hc-loop-step">Triage</div><div class="hc-loop-arrow">→</div>
                <div class="hc-loop-step">Contain</div><div class="hc-loop-arrow">→</div>
                <div class="hc-loop-step">Remediate</div><div class="hc-loop-arrow">→</div>
                <div class="hc-loop-step">Verify</div><div class="hc-loop-arrow">→</div>
                <div class="hc-loop-step">Learn</div>
            </div>
        </section>
        <footer class="hc-footer"><span>05 · Evidence needs ownership</span><span>OWASP Logging Cheat Sheet</span></footer>
    </x-slidewire::slide>

    {{-- 6 · ATTACK SURFACE --}}
    {{-- @notes Main: Attack surface is every reachable place an attacker can influence. Technical: APIs, credentials, dependencies, workers, and infrastructure all create entry paths. Transition: If exposure adds opportunities, remove what the product does not need. Duration: 65 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <div class="hc-split">
                <div>
                    <p class="hc-kicker">Exposure</p>
                    <h2 class="hc-heading hc-heading-small">Every open portal is an attack surface.</h2>
                    <div class="hc-grid hc-grid-2">
                        <div class="hc-chip">Public APIs</div><div class="hc-chip">Credentials</div>
                        <div class="hc-chip">Dependencies</div><div class="hc-chip">Workers</div>
                        <div class="hc-chip">Admin paths</div><div class="hc-chip">Infrastructure</div>
                    </div>
                </div>
                <div class="hc-portal-field" aria-label="Three breach portals surrounding a central core">
                    <div class="hc-core">CORE</div>
                    <div class="hc-portal p1">Public<br>API</div>
                    <div class="hc-portal p2">Supply<br>chain</div>
                    <div class="hc-portal p3">Identity<br>edge</div>
                </div>
            </div>
        </section>
        <footer class="hc-footer"><span>06 · Reachability creates opportunity</span></footer>
    </x-slidewire::slide>

    {{-- 7 · REDUCE EXPOSURE --}}
    {{-- @notes Main: Exposure reduction is often cheaper than defending unused functionality forever. Technical: Remove endpoints, packages, accounts, privileges, and paths; then inventory the remainder. Transition: What remains must be maintained and patched under real constraints. Duration: 50 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <p class="hc-kicker">Attack-surface management</p>
            <h2 class="hc-heading">Reduce exposure.</h2>
            <div class="hc-exposure">
                <div class="hc-exposure-side">
                    <p class="hc-kicker hc-threat">Before · unknown + broad</p>
                    <span class="hc-endpoint">/admin</span><span class="hc-endpoint">/debug</span><span class="hc-endpoint">/v1</span><span class="hc-endpoint">/v2</span><span class="hc-endpoint">old-worker</span><span class="hc-endpoint">CI token</span><span class="hc-endpoint">root DB</span><span class="hc-endpoint">unused package</span>
                </div>
                <div class="hc-big-arrow">→</div>
                <div class="hc-exposure-side is-reduced">
                    <p class="hc-kicker">After · known + necessary</p>
                    <span class="hc-endpoint">/v2</span><span class="hc-endpoint">scoped worker</span><span class="hc-endpoint">least-privilege DB</span><span class="hc-endpoint">rotated secret</span>
                    <p class="hc-lead" style="font-size:17px;">Fewer entry points. Smaller blast radius. Clear ownership.</p>
                </div>
            </div>
        </section>
        <footer class="hc-footer"><span>07 · Remove · narrow · inventory</span></footer>
    </x-slidewire::slide>

    {{-- 8 · PATCHING --}}
    {{-- @notes Main: Breach uses “Patch” as an upgrade decision. Technical: Firewall, Scan Rate, Quarantine, and Mend represent competing investments, not literal vendor patches. Transition: A real PHP security release shows how different the engineering process is. Duration: 70 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <p class="hc-kicker">Patching under pressure</p>
            <h2 class="hc-heading hc-heading-small">Four investments. One limited choice.</h2>
            <div class="hc-upgrade-layout">
                <div class="hc-upgrades">
                    <div class="hc-upgrade"><b>Firewall</b><span>Wider shield, longer seals, farther sealing reach.</span><em>Containment</em></div>
                    <div class="hc-upgrade"><b>Scan Rate</b><span>Faster, stronger, longer-range automatic scanning.</span><em>Detection + response</em></div>
                    <div class="hc-upgrade"><b>Quarantine</b><span>Periodic slow applied to threats near the player.</span><em>Control</em></div>
                    <div class="hc-upgrade"><b>Mend</b><span>Restore Core Integrity and briefly regenerate it.</span><em>Recovery</em></div>
                </div>
                <figure class="hc-patch-shot"><img src="/images/holding-the-core/patch-choice.png" alt="Darkwaar40 Patch choice interface"></figure>
            </div>
            <p class="hc-callout"><strong>Game upgrade ≠ software patch.</strong> Real remediation changes code or configuration, then requires testing, deployment, rollback, and verification.</p>
        </section>
        <footer class="hc-footer"><span>08 · Competing defensive investments</span></footer>
    </x-slidewire::slide>

    {{-- 9 · PHP RELEASE --}}
    {{-- @notes Main: Ground vulnerability management in the 21 October 2026 PHP release. Technical: CERT-FR confirmed two critical RCE issues and fixed versions 8.2.35, 8.3.36, 8.4.27, and 8.5.12; public proofs of concept affected specific deployments. Transition: A critical score still does not finish prioritization. Duration: 80 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap hc-top">
            <p class="hc-kicker">21 October 2026 · PHP security release</p>
            <h2 class="hc-heading hc-heading-small">A patch is a coordinated response.</h2>
            <div class="hc-release">
                <div class="hc-release-critical"><strong>2</strong><span>critical remote-code-execution vulnerabilities</span><p class="hc-lead" style="font-size:17px;">Discovery → triage → coordination → fix → packaging → communication → deployment</p></div>
                <div>
                    <p class="hc-kicker">Fixed versions cited by CERT-FR</p>
                    <div class="hc-version-list">
                        <div class="hc-version">PHP 8.2.35</div><div class="hc-version">PHP 8.3.36</div>
                        <div class="hc-version">PHP 8.4.27</div><div class="hc-version">PHP 8.5.12</div>
                    </div>
                    <p class="hc-source">Sources: <a href="https://cert.cyber.gouv.fr/alerte/CERTFR-2026-ALE-014/">CERTFR-2026-ALE-014</a> · <a href="https://thephp.foundation/blog/2026/10/21/todays-php-security-release-and-how-were-strengthening-php-security/">The PHP Foundation</a></p>
                </div>
            </div>
            <p class="hc-callout">The exposure window closes only when the fix is deployed and verified.</p>
        </section>
        <footer class="hc-footer"><span>09 · Verified advisory data</span></footer>
    </x-slidewire::slide>

    {{-- 10 · PRIORITIZATION --}}
    {{-- @notes Main: Severity is an input, not the remediation queue. Technical: Combine exploitability, exposure, business impact, compensating controls, and cost of delay; the equation is conceptual. Transition: Since any one control may fail, layer different controls. Duration: 65 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <p class="hc-kicker">Vulnerability prioritization</p>
            <h2 class="hc-heading">Severity alone is not the queue.</h2>
            <div class="hc-formula">
                <div class="hc-factor">Severity</div><div class="hc-times">×</div>
                <div class="hc-factor">Exploitability</div><div class="hc-times">×</div>
                <div class="hc-factor">Exposure</div><div class="hc-times">×</div>
                <div class="hc-factor">Business<br>impact</div>
            </div>
            <p class="hc-callout">A conceptual model—not a universal numerical formula. Add existing exploitation, controls, remediation cost, and cost of delay.</p>
        </section>
        <footer class="hc-footer"><span>10 · Risk-informed ordering</span><span>OWASP Risk Rating Methodology</span></footer>
    </x-slidewire::slide>

    {{-- 11 · DEFENSE IN DEPTH --}}
    {{-- @notes Main: No single control is sufficient. Technical: Layers answer different questions before, during, and after compromise. Transition: The first practical layer is maintenance of the runtime and dependency graph. Duration: 70 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap hc-top">
            <p class="hc-kicker">Defense in depth</p>
            <h2 class="hc-heading hc-heading-small">Different layers. Different failures.</h2>
            <div class="hc-layers">
                <div class="hc-layer"><b>Network + TLS</b><span>Limit reachability and protect transport</span></div>
                <div class="hc-layer"><b>Authentication</b><span>Establish an identity</span></div>
                <div class="hc-layer"><b>Authorization</b><span>Decide access to this action and object</span></div>
                <div class="hc-layer"><b>Input + interpretation</b><span>Validate, bind, encode</span></div>
                <div class="hc-layer"><b>Monitoring + response</b><span>See and contain control failures</span></div>
                <div class="hc-layer"><b>Recovery</b><span>Restore trusted service and data</span></div>
            </div>
            <p class="hc-callout">Layering reduces the chance that one defect becomes total compromise.</p>
        </section>
        <footer class="hc-footer"><span>11 · Before · during · after</span></footer>
    </x-slidewire::slide>

    {{-- 12 · MAINTENANCE --}}
    {{-- @notes Main: Maintenance is a security control. Technical: Use supported PHP and Symfony branches, install from the lock file, audit continuously, and review direct upgrades. Transition: Runtime currency is necessary; application configuration still expresses policy. Duration: 65 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <p class="hc-kicker">Act III · PHP + Symfony</p>
            <h2 class="hc-heading hc-heading-small">Security starts with maintenance.</h2>
            <div class="hc-code-grid">
                <div>
                    <p class="hc-code-label">Dependency signal</p>
                    <x-slidewire::code language="bash">composer validate --strict
composer audit --locked
composer outdated --direct</x-slidewire::code>
                </div>
                <div class="hc-grid" style="margin-top:0;">
                    <div class="hc-card"><h3>Supported runtime</h3><p>Track the PHP support calendar and ship security patch releases.</p></div>
                    <div class="hc-card"><h3>Maintained framework</h3><p>Know Symfony support dates before the branch becomes an emergency.</p></div>
                    <div class="hc-card"><h3>Continuous audit</h3><p>Run Composer audit in CI and on a schedule—not from memory.</p></div>
                </div>
            </div>
            <p class="hc-source">Sources: php.net/supported-versions.php · symfony.com/releases · getcomposer.org/doc/03-cli.md#audit</p>
        </section>
        <footer class="hc-footer"><span>12 · Dependency lifecycle</span></footer>
    </x-slidewire::slide>

    {{-- 13 · SYMFONY BASELINE --}}
    {{-- @notes Main: Symfony provides secure primitives, while configuration defines policy. Technical: Auto password hashing, throttling, CSRF, and secure cookie attributes form a compact baseline; edge controls still handle volumetric denial of service. Transition: Once identity exists, authorization remains separate. Duration: 80 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap hc-top">
            <p class="hc-kicker">Symfony security baseline</p>
            <h2 class="hc-heading hc-heading-small">Secure defaults still need explicit policy.</h2>
            <div class="hc-code-grid">
                <div>
                    <p class="hc-code-label">security.yaml</p>
                    <x-slidewire::code language="yaml">security:
  password_hashers:
    App\Entity\User: 'auto'

  firewalls:
    main:
      lazy: true
      login_throttling:
        max_attempts: 5
        interval: '1 minute'</x-slidewire::code>
                </div>
                <div>
                    <p class="hc-code-label">framework.yaml</p>
                    <x-slidewire::code language="yaml">framework:
  csrf_protection: true
  session:
    cookie_secure: true
    cookie_httponly: true
    cookie_samesite: lax</x-slidewire::code>
                    <p class="hc-callout" style="font-size:17px;">Application throttling raises brute-force cost. DDoS protection belongs at a cheaper edge.</p>
                </div>
            </div>
        </section>
        <footer class="hc-footer"><span>13 · Passwords · throttling · CSRF · sessions</span></footer>
    </x-slidewire::slide>

    {{-- 14 · AUTHN VS AUTHZ --}}
    {{-- @notes Main: A valid identity is not permission. Technical: Authentication establishes who; authorization and voters decide whether that subject may act on a specific object. CSRF is a separate browser-origin defense. Transition: The same separation of concerns applies when untrusted data reaches interpreters. Duration: 70 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <p class="hc-kicker">Identity is not permission</p>
            <h2 class="hc-heading hc-heading-small">Authentication ≠ authorization</h2>
            <div class="hc-auth">
                <div class="hc-auth-box"><h3>Authentication</h3><p>Who is presenting this request?</p><ul class="hc-list"><li>Password or identity provider</li><li>Session or access token</li><li>Credential validation</li></ul></div>
                <div class="hc-auth-arrow">→</div>
                <div class="hc-auth-box"><h3>Authorization</h3><p>May this identity act on this object?</p><ul class="hc-list"><li>Route and role gates</li><li>Object-level Symfony voters</li><li>Ownership and business policy</li></ul></div>
            </div>
            <p class="hc-callout"><code>#[IsGranted('PROJECT_DELETE', 'project')]</code> checks the action against the actual project—not only the login state.</p>
        </section>
        <footer class="hc-footer"><span>14 · Identity → policy → object</span></footer>
    </x-slidewire::slide>

    {{-- 15 · SAFE DATA --}}
    {{-- @notes Main: Protect every interpreter boundary with the API appropriate to that sink. Technical: Bind Doctrine values, allowlist identifiers, preserve Twig auto-escaping, externalize secrets, and avoid logging credentials or tokens. Transition: Authentication protocols are an interpreter and trust boundary of their own. Duration: 70 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap hc-top">
            <p class="hc-kicker">Safe input and data handling</p>
            <h2 class="hc-heading hc-heading-small">Untrusted data must not become instructions.</h2>
            <div class="hc-code-grid">
                <div>
                    <p class="hc-code-label">Doctrine · bind values</p>
                    <x-slidewire::code language="php">$projects = $repository->createQueryBuilder('p')
    ->andWhere('p.owner = :owner')
    ->setParameter('owner', $user)
    ->getQuery()
    ->getResult();</x-slidewire::code>
                    <p class="hc-source">Dynamic identifiers still require an allowlist.</p>
                </div>
                <div class="hc-grid hc-grid-2" style="margin-top:0;">
                    <div class="hc-card"><h3>Twig</h3><p>Keep contextual auto-escaping. Audit every <code>|raw</code>.</p></div>
                    <div class="hc-card"><h3>Secrets</h3><p>Use a deployment manager or Symfony vault; keep keys separate.</p></div>
                    <div class="hc-card"><h3>Logs</h3><p>Exclude passwords, session IDs, access tokens, and private keys.</p></div>
                    <div class="hc-card"><h3>Deploy</h3><p>Production mode, least privilege, narrow web root, tested headers.</p></div>
                </div>
            </div>
        </section>
        <footer class="hc-footer"><span>15 · Bind · encode · allowlist · redact</span></footer>
    </x-slidewire::slide>

    {{-- 16 · CUSTOM SSO --}}
    {{-- @notes Main: Authentication protocol code has a large hidden attack surface. Technical: Redirects, state, nonce, signing keys, audiences, expiry, logout, and rotation all need exact behavior. Transition: OIDC Authorization Code with PKCE makes that behavior explicit and interoperable. Duration: 60 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <p class="hc-kicker">Act IV · Darkwood Identity</p>
            <h2 class="hc-heading">Why replace custom SSO?</h2>
            <div class="hc-state">
                <div class="hc-state-box"><h3>Custom protocol</h3><ul class="hc-list"><li>Locally designed redirect rules</li><li>Custom token semantics</li><li>Private key-rotation behavior</li><li>Application-specific testing surface</li><li>Long-term maintenance ownership</li></ul></div>
                <div class="hc-state-arrow">→</div>
                <div class="hc-state-box"><h3>OpenID Connect</h3><ul class="hc-list"><li>Authorization Code flow</li><li>PKCE S256, state, nonce</li><li>Discovery and JWKS</li><li>Standard token claims</li><li>Interoperable libraries and tooling</li></ul></div>
            </div>
            <p class="hc-callout">Standards do not remove security work. They make the required work explicit and reviewable.</p>
        </section>
        <footer class="hc-footer"><span>16 · Reduce custom authentication behavior</span></footer>
    </x-slidewire::slide>

    {{-- 17 · OIDC FLOW --}}
    {{-- @notes Main: Walk the Authorization Code plus PKCE sequence. Technical: State protects the authorization response, PKCE binds the code to the client, nonce binds the ID token to the request, and validation precedes the local session. Transition: Zoom into the trust decisions inside that validation step. Duration: 100 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap hc-top">
            <p class="hc-kicker">OIDC Authorization Code + PKCE S256</p>
            <h2 class="hc-heading hc-heading-small">Redirect briefly. Validate rigorously.</h2>
            <div class="hc-sequence">
                <div class="hc-actor">User<br>browser</div>
                <div class="hc-seq-step"><b>1</b> Open Navi admin login</div>
                <div class="hc-actor">Darkwood<br>Identity</div>
                <div class="hc-seq-step"><b>2</b> Navi creates state, nonce, verifier + S256 challenge → authorize redirect</div>
                <div class="hc-seq-step"><b>3</b> Login + 2FA at Identity</div>
                <div class="hc-seq-step"><b>4</b> Redirect authorization code + state to Navi</div>
                <div class="hc-seq-step"><b>5</b> Navi exchanges code + original verifier at token endpoint</div>
                <div class="hc-seq-step"><b>6</b> Identity returns signed ID token</div>
                <div class="hc-seq-step"><b>7</b> Navi loads JWKS; validates signature, claims, state + nonce</div>
                <div class="hc-seq-step"><b>8</b> Migrate session ID; apply local admin authorization</div>
                <div class="hc-seq-step"><b>9</b> Local Navi session continues</div>
            </div>
        </section>
        <footer class="hc-footer"><span>17 · Code is short-lived; verifier never travels in the authorization request</span></footer>
    </x-slidewire::slide>

    {{-- 18 · TRUST --}}
    {{-- @notes Main: A JWT that parses is not a trusted identity. Technical: Validate signature through JWKS and issuer, audience, authorized party, expiry, issued-at, nonce, and state; use immutable UUID subject. Then create a local session and apply local authorization. Transition: Separate what exists in the repositories from the target migration. Duration: 85 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap hc-top">
            <p class="hc-kicker">Trust, tokens and sessions</p>
            <h2 class="hc-heading hc-heading-small">Parsing a JWT is not validation.</h2>
            <div class="hc-trust">
                <div class="hc-trust-item"><b>iss</b><span>Expected identity provider</span></div>
                <div class="hc-trust-item"><b>aud / azp</b><span>Token intended for this client</span></div>
                <div class="hc-trust-item"><b>JWKS + RS256</b><span>Trusted key and valid signature</span></div>
                <div class="hc-trust-item"><b>exp / iat</b><span>Acceptable lifetime and issue time</span></div>
                <div class="hc-trust-item"><b>nonce</b><span>ID token belongs to this login</span></div>
                <div class="hc-trust-item"><b>state</b><span>Authorization response belongs to this browser flow</span></div>
                <div class="hc-trust-item"><b>sub = UUID</b><span>Stable subject; email remains a claim</span></div>
                <div class="hc-trust-item"><b>local session</b><span>Navi owns authorization and session policy</span></div>
            </div>
            <p class="hc-callout">OIDC authenticates identity claims. Navi still decides who may administer Navi.</p>
        </section>
        <footer class="hc-footer"><span>18 · Authentication ≠ application authorization</span></footer>
    </x-slidewire::slide>

    {{-- 19 · MIGRATION STATE --}}
    {{-- @notes Main: Be precise about current code and future architecture. Technical: The provider and custom Navi relying party are present; Navi uses HttpClient and web-token/jwt-library. Native Symfony 8.2 oidc_login is planned, not implemented, and production rollout was not verified. Transition: This precision matters because the game metaphor also has hard limits. Duration: 75 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap hc-top">
            <p class="hc-kicker">Actual repository state · inspected 24 Oct 2026</p>
            <h2 class="hc-heading hc-heading-small">Implemented now. Target next.</h2>
            <div class="hc-state">
                <div class="hc-state-box"><h3>Implemented in source</h3><ul class="hc-list"><li>Darkwood OIDC discovery, authorize, token, JWKS, end-session routes</li><li>Authorization Code; implicit and password grants disabled</li><li>Navi PKCE S256, state, nonce, JWKS cache + retry</li><li>RS256 and claim validation</li><li>Local role gate and session migration</li></ul></div>
                <div class="hc-state-arrow">→</div>
                <div class="hc-state-box is-target"><h3>Target architecture</h3><ul class="hc-list"><li>Move Navi onto a maintained Symfony branch</li><li>Adopt Symfony 8.2 native <code>oidc_login</code></li><li>Delete more custom relying-party code</li><li>Preserve local authorization policy</li></ul><p class="hc-source">Planned in repository design documentation; Symfony 8.2 was still under development.</p></div>
            </div>
            <p class="hc-source">Repository code confirms implementation—not deployed production rollout or a completed live login.</p>
        </section>
        <footer class="hc-footer"><span>19 · Current implementation vs future plan</span></footer>
    </x-slidewire::slide>

    {{-- 20 · METAPHOR LIMITS --}}
    {{-- @notes Main: State what the game gets wrong to preserve technical credibility. Technical: Confidentiality can fail invisibly; zero-day describes fix availability, recovery is procedural, and attackers are adaptive. Transition: Keep the decision loop, discard the false equivalences. Duration: 65 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <p class="hc-kicker">Act V · Limits</p>
            <h2 class="hc-heading">What the game gets wrong</h2>
            <div class="hc-caveat">
                <div class="hc-card"><h3>Confidentiality is quiet</h3><p>Data can be copied while the Core still reads 100.</p></div>
                <div class="hc-card"><h3>A zero-day is not “stronger”</h3><p>It is defined by defenders lacking an available fix—not by hit points.</p></div>
                <div class="hc-card"><h3>Recovery is not HP</h3><p>It needs tested backups, rebuilt credentials, restoration, and forensic confidence.</p></div>
                <div class="hc-card"><h3>Attackers do not follow timers</h3><p>They adapt to controls, defenders, incentives, and opportunity.</p></div>
            </div>
        </section>
        <footer class="hc-footer"><span>20 · Keep the decision structure—not the literal model</span></footer>
    </x-slidewire::slide>

    {{-- 21 · CONTINUOUS LOOP --}}
    {{-- @notes Main: Return to the thesis as an operational cycle. Technical: Inventory comes first; prevention, detection, response, recovery, and learning continually change the next risk decision. Transition: Close by inviting the audience to experience that pressure in the game. Duration: 55 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide">
        <section class="hc-wrap">
            <p class="hc-kicker">The durable lesson</p>
            <h2 class="hc-heading">Security is a continuous loop.</h2>
            <div class="hc-final-loop">
                <div class="hc-card"><span class="hc-accent">01</span>&nbsp; Inventory</div>
                <div class="hc-card"><span class="hc-accent">02</span>&nbsp; Prevent</div>
                <div class="hc-card"><span class="hc-accent">03</span>&nbsp; Detect</div>
                <div class="hc-card"><span class="hc-accent">04</span>&nbsp; Respond</div>
                <div class="hc-card"><span class="hc-accent">05</span>&nbsp; Recover</div>
                <div class="hc-card"><span class="hc-accent">06</span>&nbsp; Improve</div>
            </div>
            <p class="hc-callout">The system changes. The threat changes. The loop must keep moving.</p>
        </section>
        <footer class="hc-footer"><span>21 · Operate the loop</span></footer>
    </x-slidewire::slide>

    {{-- 22 · PLAY --}}
    {{-- @notes Main: Close on the game and the operating principle. Technical: The canonical darkwaar.com game URL is verified; no deployment claim is made. Transition: End. Duration: 30 seconds. --}}
    <x-slidewire::slide class="dw-slide hc-slide hc-hero">
        <div class="hc-cover" aria-hidden="true"><img src="/images/holding-the-core/cover.png" alt=""></div>
        <section class="hc-wrap">
            <div class="hc-hero-copy">
                <p class="hc-kicker">Play Darkwaar40 — Breach</p>
                <h2 class="hc-heading" style="font-size:clamp(56px,6vw,96px);">Keep moving.<br><span class="hc-accent">Keep the Core alive.</span></h2>
                <a class="hc-url" href="https://darkwaar.com/games/01a16e69-3593-7df1-b460-e25c4912eae1">darkwaar.com/games/01a16e69-3593-7df1-b460-e25c4912eae1</a>
                <div class="hc-brand"><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"><span>Darkwood · @matyo91</span></div>
            </div>
        </section>
        <footer class="hc-footer"><span>22 · Holding the Core</span><span>Thank you</span></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
