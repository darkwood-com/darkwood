<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide hc-hero" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header hc-hero-copy',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle hc-subtitle',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Darkwood · Security Engineering · Darkwaar40
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Holding<br>the Core
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Security Engineering Through <span class="hc-threat">Darkwaar40 — Breach</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<style>
            .slidewire-frame.hc-slide,
            .slidewire-frame:has(.hc-slide) { overflow: hidden; }
            .hc-slide {
                --hc-bg: var(--dw-blue);
                --hc-panel: var(--dw-blue);
                --hc-panel-soft: var(--dw-blue);
                --hc-text: var(--dw-blue);
                --hc-muted: var(--dw-blue);
                --hc-teal: var(--dw-blue);
                --hc-magenta: var(--dw-blue);
                --hc-gold: var(--dw-blue);
                --hc-coral: var(--dw-blue);
                --hc-line: var(--dw-blue);
                min-height: 100vh;
                position: relative;
                overflow: hidden;
            }
            .hc-slide::before {
                content: '';
                position: absolute;
                inset: 0;
                background-size: 54px 54px;
                mask-image: linear-gradient(to bottom, #000, transparent 92%);
                pointer-events: none;
            }
            .hc-slide::after {
                content: '';
                position: absolute;
                inset: auto 0 0;
                height: 6px;
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
            .hc-kicker { margin: 0 0 14px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
            .hc-title { margin: 0; max-width: 820px; line-height: .88; font-weight: 700; letter-spacing: -.045em; }
            .hc-subtitle { margin: 22px 0 0; max-width: 780px; line-height: 1.2; }
            .hc-heading { margin: 0; line-height: .98; font-weight: 700; letter-spacing: -.035em; text-wrap: balance; }
            .hc-lead { margin: 20px 0 0; max-width: 940px; line-height: 1.35; }
            .hc-grid { display: grid; gap: 16px; margin-top: 28px; }
            .hc-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .hc-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .hc-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .hc-grid > *, .hc-split > *, .hc-release > *, .hc-state > *, .hc-code-grid > *, .hc-auth > * { min-width: 0; }
            .hc-card {
                border: var(--dw-border-width) solid var(--hc-line);
                border-radius: 14px;
                padding: 20px 22px;
            }
            .hc-card h3 { margin: 0; line-height: 1.1; }
            .hc-card p { margin: 9px 0 0; line-height: 1.35; }
            .hc-chip { border: var(--dw-border-width) solid var(--hc-line); border-radius: 999px; padding: 11px 15px; font-weight: 700; text-align: center; }
            .hc-callout { margin-top: 28px; padding: 19px 23px; border-left: var(--dw-border-width) solid var(--hc-teal); line-height: 1.3; }
            .hc-footer { position: absolute; z-index: 5; left: 32px; right: 32px; bottom: 16px; display: flex; justify-content: space-between; align-items: center; letter-spacing: .1em; text-transform: uppercase; }
            .hc-footer img { width: 24px; height: 24px; object-fit: contain; }
            .hc-media { position: relative; border: var(--dw-border-width) solid var(--dw-blue); border-radius: 16px; overflow: hidden; }
            .hc-media img { display: block; width: 100%; height: 100%; object-fit: cover; }
            .hc-phone { width: min(360px, 31vw); height: min(690px, 73vh); margin-left: auto; }
            .hc-phone img { object-position: center 31%; }
            .hc-split { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(260px, .7fr); gap: 48px; align-items: center; }
            .hc-cover { position: absolute; top: -9vh; right: 6vw; width: min(430px, 33vw); height: 118vh; mask-image: linear-gradient(to left, #000 70%, transparent); }
            .hc-cover img { width: 100%; height: 100%; object-fit: cover; }
            .hc-hero-copy { position: relative; z-index: 2; max-width: 850px; }
            .hc-brand { margin-top: 34px; display: flex; gap: 14px; align-items: center; letter-spacing: .08em; text-transform: uppercase; }
            .hc-brand img { width: 36px; height: 36px; }
            .hc-stat-row { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; margin-top: 22px; }
            .hc-stat { padding: 14px 10px; border: var(--dw-border-width) solid var(--dw-blue); border-radius: 10px; text-align: center; }
            .hc-stat strong { display: block; }
            .hc-stat span { display: block; margin-top: 3px; text-transform: uppercase; letter-spacing: .08em; }
            .hc-thesis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); align-items: center; margin-top: 54px; }
            .hc-thesis-node { position: relative; display: grid; place-items: center; min-height: 150px; padding: 18px; border: var(--dw-border-width) solid var(--hc-line); border-radius: 50%; font-weight: 700; text-align: center; }
            .hc-thesis-node:not(:last-child)::after { content: '→'; position: absolute; right: -23px; }
            .hc-map { display: grid; grid-template-columns: 1fr auto 1.35fr; gap: 11px 18px; align-items: center; margin-top: 24px; }
            .hc-map > span { padding: 11px 15px; border-radius: 10px; }
            .hc-map .hc-map-game { font-weight: 700; }
            .hc-map .hc-map-arrow { padding: 0; }
            .hc-loop { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 48px; }
            .hc-loop-step { flex: 1; min-height: 78px; padding: 18px 12px; border: var(--dw-border-width) solid var(--hc-line); border-radius: 12px; display: grid; place-items: center; font-weight: 700; text-align: center; }
            .hc-portal-field { position: relative; width: min(560px, 46vw); aspect-ratio: 1; margin-left: auto; }
            .hc-core { position: absolute; inset: 39%; border: var(--dw-border-width) double var(--hc-teal); border-radius: 50%; display: grid; place-items: center; font-weight: 700; }
            .hc-portal { position: absolute; width: 112px; height: 112px; border: var(--dw-border-width) double var(--hc-magenta); border-radius: 50%; display: grid; place-items: center; font-weight: 700; text-align: center; text-transform: uppercase; letter-spacing: .07em; }
            .hc-portal.p1 { top: 2%; left: 40%; }
            .hc-portal.p2 { left: 2%; bottom: 13%; }
            .hc-portal.p3 { right: 2%; bottom: 13%; }
            .hc-portal-line { position: absolute; height: 1px; width: 34%; transform-origin: left center; }
            .hc-exposure { display: grid; grid-template-columns: 1fr 80px 1fr; gap: 24px; align-items: center; margin-top: 35px; }
            .hc-exposure-side { min-height: 320px; padding: 28px; border: var(--dw-border-width) solid var(--dw-blue); border-radius: 18px; }
            .hc-endpoint { display: inline-flex; margin: 6px 4px; padding: 9px 12px; border: var(--dw-border-width) solid var(--dw-blue); border-radius: 8px; }
            .hc-big-arrow { text-align: center; }
            .hc-upgrade-layout { display: grid; grid-template-columns: minmax(0, 1fr) 285px; gap: 20px; align-items: stretch; margin-top: 28px; }
            .hc-upgrades { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 0; }
            .hc-upgrade { min-height: 145px; padding: 18px; border: var(--dw-border-width) solid var(--hc-line); border-radius: 16px; }
            .hc-upgrade b { display: block; }
            .hc-upgrade span { display: block; margin-top: 10px; line-height: 1.35; }
            .hc-upgrade em { display: block; margin-top: 18px; font-style: normal; text-transform: uppercase; letter-spacing: .08em; }
            .hc-patch-shot { height: 304px; border: var(--dw-border-width) solid var(--dw-blue); border-radius: 16px; overflow: hidden; }
            .hc-patch-shot img { width: 100%; height: 100%; object-fit: cover; object-position: center 42%; }
            .hc-release { display: grid; grid-template-columns: 1fr 1.2fr; gap: 28px; margin-top: 30px; }
            .hc-release-critical { padding: 28px; border: var(--dw-border-width) solid var(--dw-blue); border-radius: 16px; }
            .hc-release-critical strong { display: block; line-height: 1; }
            .hc-version-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .hc-version { padding: 18px; border: var(--dw-border-width) solid var(--hc-line); border-radius: 12px; text-align: center; }
            .hc-formula { margin-top: 48px; display: flex; align-items: stretch; justify-content: center; gap: 10px; }
            .hc-factor { width: 190px; min-height: 145px; display: grid; place-items: center; padding: 18px; border: var(--dw-border-width) solid var(--hc-line); border-radius: 14px; font-weight: 700; text-align: center; }
            .hc-times { align-self: center; }
            .hc-layers { margin: 28px auto 0; width: min(900px, 76vw); display: grid; gap: 8px; }
            .hc-layer { min-height: 58px; padding: 12px 24px; border: var(--dw-border-width) solid var(--hc-line); border-radius: 12px; display: flex; align-items: center; justify-content: space-between; }
            .hc-layer:nth-child(odd) { margin: 0 7%; }
            .hc-code-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 22px; align-items: start; margin-top: 22px; }
            .hc-code-label { margin: 0 0 7px; font-size: 13px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
            .hc-slide pre.phiki,
            .hc-slide pre.slidewire-code { margin: 0 !important; padding: 18px 20px !important; border: var(--dw-border-width) solid var(--hc-line); border-radius: 12px !important; font-size: 14px !important; line-height: 1.42 !important; max-height: 520px; max-width: 100%; overflow-x: auto; }
            .hc-auth { display: grid; grid-template-columns: 1fr 82px 1fr; gap: 20px; align-items: center; margin-top: 46px; }
            .hc-auth-box { min-height: 240px; padding: 28px; border: var(--dw-border-width) solid var(--hc-line); border-radius: 16px; }
            .hc-auth-box h3 { margin: 0; }
            .hc-auth-arrow { text-align: center; }
            .hc-list { margin: 16px 0 0; padding: 0; list-style: none; }
            .hc-list li { position: relative; margin: 9px 0; padding-left: 20px; line-height: 1.3; }
            .hc-list li::before { content: '›'; position: absolute; left: 0; font-weight: 700; }
            .hc-sequence { margin-top: 25px; display: grid; grid-template-columns: 145px minmax(0, 1fr) 145px; gap: 8px 14px; align-items: center; }
            .hc-actor { grid-row: span 8; align-self: stretch; display: grid; place-items: center; border: var(--dw-border-width) solid var(--hc-line); border-radius: 14px; font-weight: 700; text-align: center; }
            .hc-seq-step { grid-column: 2; padding: 10px 16px; border: var(--dw-border-width) solid var(--dw-blue); border-radius: 9px; text-align: center; }
            .hc-trust { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-top: 28px; }
            .hc-trust-item { min-height: 114px; padding: 15px; border: var(--dw-border-width) solid var(--hc-line); border-radius: 12px; }
            .hc-trust-item b { display: block; }
            .hc-trust-item span { display: block; margin-top: 7px; line-height: 1.3; }
            .hc-state { display: grid; grid-template-columns: 1fr 90px 1fr; gap: 20px; align-items: center; margin-top: 34px; }
            .hc-state-box { min-height: 330px; padding: 24px; border: var(--dw-border-width) solid var(--hc-line); border-radius: 16px; }
            .hc-state-box h3 { margin: 0 0 16px; }
            .hc-state-box.is-target { border-style: dashed; }
            .hc-state-arrow { text-align: center; }
            .hc-caveat { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 30px; }
            .hc-caveat .hc-card { min-height: 145px; }
            .hc-final-loop { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 40px; }
            .hc-final-loop .hc-card { min-height: 104px; display: grid; place-items: center; font-weight: 700; text-align: center; }
            .hc-url { display: inline-block; margin-top: 28px; padding: 15px 19px; border: var(--dw-border-width) solid var(--hc-line); border-radius: 10px; text-decoration: none; }
            .hc-source { margin-top: 14px; line-height: 1.3; }
            .hc-source a { text-decoration: none; }
            @media (max-width: 900px), (max-height: 700px) {
                .hc-wrap { width: min(1160px, calc(100vw - 56px)); padding-top: 40px; }
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



                <div class="hc-brand"><img src="/darkwood/logos/dw512x512-uni-dark.png" alt="Darkwood" class=" sw-logo" data-background="white"><span>Darkwood technical session · @matyo91</span></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 · Holding the Core · 20–25 min" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Act I · The game
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
100 Seconds to Survive
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">
            <div class="hc-split">
                <div>


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 · Implemented game loop · Real gameplay capture" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The thesis
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Security is not a wall.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">


            <div class="hc-thesis">
                <div class="hc-thesis-node">Reduce<br><span class="hc-accent">exposure</span></div>
                <div class="hc-thesis-node">Detect<br><span class="hc-accent">activity</span></div>
                <div class="hc-thesis-node">Respond<br><span class="hc-accent">under pressure</span></div>
                <div class="hc-thesis-node">Maintain<br><span class="hc-accent">resilience</span></div>
            </div>
            <p class="hc-callout">Security is continuous operational work—not a perimeter completed once.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 · Continuous process" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The security model
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A readable metaphor—with boundaries
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap hc-top">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 · Metaphor ≠ implementation" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Act II · Defensive security
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Detection is not prevention.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 · Evidence needs ownership · OWASP Logging Cheat Sheet" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Exposure
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Every open portal is an attack surface.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">
            <div class="hc-split">
                <div>


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 · Reachability creates opportunity" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Attack-surface management
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Reduce exposure.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">


            <div class="hc-exposure">
                <div class="hc-exposure-side">
                    <p class="hc-kicker hc-threat">Before · unknown + broad</p>
                    <span class="hc-endpoint">/admin</span><span class="hc-endpoint">/debug</span><span class="hc-endpoint">/v1</span><span class="hc-endpoint">/v2</span><span class="hc-endpoint">old-worker</span><span class="hc-endpoint">CI token</span><span class="hc-endpoint">root DB</span><span class="hc-endpoint">unused package</span>
                </div>
                <div class="hc-big-arrow">→</div>
                <div class="hc-exposure-side is-reduced">
                    <p class="hc-kicker">After · known + necessary</p>
                    <span class="hc-endpoint">/v2</span><span class="hc-endpoint">scoped worker</span><span class="hc-endpoint">least-privilege DB</span><span class="hc-endpoint">rotated secret</span>
                    <p class="hc-lead">Fewer entry points. Smaller blast radius. Clear ownership.</p>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 · Remove · narrow · inventory" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Patching under pressure
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Four investments. One limited choice.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 · Competing defensive investments" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
21 October 2026 · PHP security release
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A patch is a coordinated response.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap hc-top">


            <div class="hc-release">
                <div class="hc-release-critical"><strong>2</strong><span>critical remote-code-execution vulnerabilities</span><p class="hc-lead">Discovery → triage → coordination → fix → packaging → communication → deployment</p></div>
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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 · Verified advisory data" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Vulnerability prioritization
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Severity alone is not the queue.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">


            <div class="hc-formula">
                <div class="hc-factor">Severity</div><div class="hc-times">×</div>
                <div class="hc-factor">Exploitability</div><div class="hc-times">×</div>
                <div class="hc-factor">Exposure</div><div class="hc-times">×</div>
                <div class="hc-factor">Business<br>impact</div>
            </div>
            <p class="hc-callout">A conceptual model—not a universal numerical formula. Add existing exploitation, controls, remediation cost, and cost of delay.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 · Risk-informed ordering · OWASP Risk Rating Methodology" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Defense in depth
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Different layers. Different failures.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap hc-top">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 · Before · during · after" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Act III · PHP + Symfony
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Security starts with maintenance.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">


            <div class="hc-code-grid">
                <div>
                    <p class="hc-code-label">Dependency signal</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_11_0'
composer validate --strict
composer audit --locked
composer outdated --direct
SW_CODE_11_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="shellscript" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="hc-grid" style="margin-top:0">
                    <div class="hc-card"><h3>Supported runtime</h3><p>Track the PHP support calendar and ship security patch releases.</p></div>
                    <div class="hc-card"><h3>Maintained framework</h3><p>Know Symfony support dates before the branch becomes an emergency.</p></div>
                    <div class="hc-card"><h3>Continuous audit</h3><p>Run Composer audit in CI and on a schedule—not from memory.</p></div>
                </div>
            </div>
            <p class="hc-source">Sources: php.net/supported-versions.php · symfony.com/releases · getcomposer.org/doc/03-cli.md#audit</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 · Dependency lifecycle" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Symfony security baseline
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Secure defaults still need explicit policy.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap hc-top">


            <div class="hc-code-grid">
                <div>
                    <p class="hc-code-label">security.yaml</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_12_0'
security:
  password_hashers:
    App\Entity\User: 'auto'

  firewalls:
    main:
      lazy: true
      login_throttling:
        max_attempts: 5
        interval: '1 minute'
SW_CODE_12_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="yaml" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div>
                    <p class="hc-code-label">framework.yaml</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_12_1'
framework:
  csrf_protection: true
  session:
    cookie_secure: true
    cookie_httponly: true
    cookie_samesite: lax
SW_CODE_12_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="yaml" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    <p class="hc-callout">Application throttling raises brute-force cost. DDoS protection belongs at a cheaper edge.</p>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 · Passwords · throttling · CSRF · sessions" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Identity is not permission
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Authentication ≠ authorization
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">


            <div class="hc-auth">
                <div class="hc-auth-box"><h3>Authentication</h3><p>Who is presenting this request?</p><ul class="hc-list"><li>Password or identity provider</li><li>Session or access token</li><li>Credential validation</li></ul></div>
                <div class="hc-auth-arrow">→</div>
                <div class="hc-auth-box"><h3>Authorization</h3><p>May this identity act on this object?</p><ul class="hc-list"><li>Route and role gates</li><li>Object-level Symfony voters</li><li>Ownership and business policy</li></ul></div>
            </div>
            <p class="hc-callout"><code>#[IsGranted('PROJECT_DELETE', 'project')]</code> checks the action against the actual project—not only the login state.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 · Identity → policy → object" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Safe input and data handling
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Untrusted data must not become instructions.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap hc-top">


            <div class="hc-code-grid">
                <div>
                    <p class="hc-code-label">Doctrine · bind values</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_14_0'
$projects = $repository->createQueryBuilder('p')
    ->andWhere('p.owner = :owner')
    ->setParameter('owner', $user)
    ->getQuery()
    ->getResult();
SW_CODE_14_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    <p class="hc-source">Dynamic identifiers still require an allowlist.</p>
                </div>
                <div class="hc-grid hc-grid-2" style="margin-top:0">
                    <div class="hc-card"><h3>Twig</h3><p>Keep contextual auto-escaping. Audit every <code>|raw</code>.</p></div>
                    <div class="hc-card"><h3>Secrets</h3><p>Use a deployment manager or Symfony vault; keep keys separate.</p></div>
                    <div class="hc-card"><h3>Logs</h3><p>Exclude passwords, session IDs, access tokens, and private keys.</p></div>
                    <div class="hc-card"><h3>Deploy</h3><p>Production mode, least privilege, narrow web root, tested headers.</p></div>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="15 · Bind · encode · allowlist · redact" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Act IV · Darkwood Identity
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Why replace custom SSO?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">


            <div class="hc-state">
                <div class="hc-state-box"><h3>Custom protocol</h3><ul class="hc-list"><li>Locally designed redirect rules</li><li>Custom token semantics</li><li>Private key-rotation behavior</li><li>Application-specific testing surface</li><li>Long-term maintenance ownership</li></ul></div>
                <div class="hc-state-arrow">→</div>
                <div class="hc-state-box"><h3>OpenID Connect</h3><ul class="hc-list"><li>Authorization Code flow</li><li>PKCE S256, state, nonce</li><li>Discovery and JWKS</li><li>Standard token claims</li><li>Interoperable libraries and tooling</li></ul></div>
            </div>
            <p class="hc-callout">Standards do not remove security work. They make the required work explicit and reviewable.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="16 · Reduce custom authentication behavior" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
OIDC Authorization Code + PKCE S256
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Redirect briefly. Validate rigorously.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap hc-top">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="17 · Code is short-lived; verifier never travels in the authorization request" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Trust, tokens and sessions
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Parsing a JWT is not validation.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap hc-top">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="18 · Authentication ≠ application authorization" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading hc-heading-small',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Actual repository state · inspected 24 Oct 2026
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Implemented now. Target next.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap hc-top">


            <div class="hc-state">
                <div class="hc-state-box"><h3>Implemented in source</h3><ul class="hc-list"><li>Darkwood OIDC discovery, authorize, token, JWKS, end-session routes</li><li>Authorization Code; implicit and password grants disabled</li><li>Navi PKCE S256, state, nonce, JWKS cache + retry</li><li>RS256 and claim validation</li><li>Local role gate and session migration</li></ul></div>
                <div class="hc-state-arrow">→</div>
                <div class="hc-state-box is-target"><h3>Target architecture</h3><ul class="hc-list"><li>Move Navi onto a maintained Symfony branch</li><li>Adopt Symfony 8.2 native <code>oidc_login</code></li><li>Delete more custom relying-party code</li><li>Preserve local authorization policy</li></ul><p class="hc-source">Planned in repository design documentation; Symfony 8.2 was still under development.</p></div>
            </div>
            <p class="hc-source">Repository code confirms implementation—not deployed production rollout or a completed live login.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="19 · Current implementation vs future plan" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Act V · Limits
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What the game gets wrong
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">


            <div class="hc-caveat">
                <div class="hc-card"><h3>Confidentiality is quiet</h3><p>Data can be copied while the Core still reads 100.</p></div>
                <div class="hc-card"><h3>A zero-day is not “stronger”</h3><p>It is defined by defenders lacking an available fix—not by hit points.</p></div>
                <div class="hc-card"><h3>Recovery is not HP</h3><p>It needs tested backups, rebuilt credentials, restoration, and forensic confidence.</p></div>
                <div class="hc-card"><h3>Attackers do not follow timers</h3><p>They adapt to controls, defenders, incentives, and opportunity.</p></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="20 · Keep the decision structure—not the literal model" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The durable lesson
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Security is a continuous loop.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="hc-wrap">


            <div class="hc-final-loop">
                <div class="hc-card"><span class="hc-accent">01</span>  Inventory</div>
                <div class="hc-card"><span class="hc-accent">02</span>  Prevent</div>
                <div class="hc-card"><span class="hc-accent">03</span>  Detect</div>
                <div class="hc-card"><span class="hc-accent">04</span>  Respond</div>
                <div class="hc-card"><span class="hc-accent">05</span>  Recover</div>
                <div class="hc-card"><span class="hc-accent">06</span>  Improve</div>
            </div>
            <p class="hc-callout">The system changes. The threat changes. The loop must keep moving.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="21 · Operate the loop" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide hc-slide hc-hero" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header hc-hero-copy',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline hc-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading hc-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Play Darkwaar40 — Breach
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Keep moving.<br><span class="hc-accent">Keep the Core alive.</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body class="sw-ending-with-cover">
@verbatim
<div class="hc-cover" aria-hidden="true"><img src="/images/holding-the-core/cover.png" alt=""></div>
        <section class="hc-wrap">
            <div class="hc-hero-copy">


                <a class="hc-url" href="https://darkwaar.com/games/01a16e69-3593-7df1-b460-e25c4912eae1">darkwaar.com/games/01a16e69-3593-7df1-b460-e25c4912eae1</a>
                <div class="hc-brand">@endverbatim
<x-darkwood.author-portrait author-id="matyo91" />
@verbatim<span>Darkwood · @matyo91</span></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="22 · Holding the Core · Thank you" />
</x-darkwood.slide>
</x-darkwood.deck>
