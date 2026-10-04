{{-- From Noise to a Playable World --}}
{{-- Title: From Noise to a Playable World --}}
{{-- Description: How Darkwaar20 turns deterministic noise into terrain, constraints, and a playable path through five denoising passes. --}}
{{-- Slug: darkwaar20 --}}
{{-- Route: /slides/darkwaar20 --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    <x-slidewire::slide class="dw-slide dw20">
<style>
    .slidewire-frame:has(.dw20) { overflow: hidden; padding: 1.1rem 1.4rem 1.2rem; }
    article.dw20 { min-height: 100%; height: 100%; max-height: 100%; }
    .dw20 .dw-wrap { width: min(1180px, 100%); min-height: 0; height: 100%; padding: 28px 0 18px; justify-content: center; }
    .dw20-kicker { color: var(--dw-cyan); font-size: 13px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; }
    .dw20-title { margin: 0; font-size: clamp(46px, 5.4vw, 84px); line-height: .92; font-weight: 700; }
    .dw20-sub { margin: 16px 0 0; color: var(--dw-muted); font-size: clamp(18px, 1.7vw, 26px); }
    .dw20-line { margin: 8px 0 0; font-size: clamp(28px, 3vw, 44px); line-height: 1.05; font-weight: 700; }
    .dw20 [data-board], .dw20 [data-patch] { display: grid; gap: 3px; }
    .dw20-cell { position: relative; width: var(--cell, 28px); height: var(--cell, 28px); display: grid; grid-template-columns: 1fr 1fr; background: #17181f; box-shadow: inset 0 0 0 1px rgba(97, 102, 117, .95); }
    .dw20-cell i { display: block; }
    .dw20-cell.is-void { box-shadow: inset 0 0 0 2px rgba(176, 182, 196, .9); }
    .dw20-cell.is-core { box-shadow: inset 0 0 0 3px #59dce6; }
    .dw20-cell.is-exit::before { content: ""; position: absolute; inset: 27%; background: #f4f0e2; z-index: 1; }
    .dw20-cell.is-player::after { content: ""; position: absolute; inset: 30%; border-radius: 50%; background: #f3f5f7; z-index: 2; }
    .dw20-cell.is-eligible { box-shadow: inset 0 0 0 1px rgba(89, 215, 255, .7); }
    .dw20-cell.is-reach { outline: 1px solid rgba(184, 255, 106, .55); outline-offset: -3px; }
    .dw20-cell.is-core.is-eligible { box-shadow: inset 0 0 0 3px #59dce6; }
    .dw20-row { display: flex; align-items: center; justify-content: space-between; gap: 28px; }
    .dw20-facts { display: grid; gap: 14px; }
    .dw20-fact { border: 1px solid var(--dw-line); background: var(--dw-panel); padding: 16px 18px; min-width: 180px; }
    .dw20-fact b { display: block; font-size: clamp(32px, 3vw, 48px); line-height: 1; }
    .dw20-fact span { color: var(--dw-muted); font-size: 14px; letter-spacing: .12em; text-transform: uppercase; }
    .dw20-pair { display: flex; justify-content: center; align-items: center; gap: 28px; }
    .dw20-side { transition: box-shadow .3s linear; }
    .dw20-side.is-armed { box-shadow: 0 0 0 1px rgba(255, 176, 92, .8); }
    .dw20-caption { position: relative; min-height: 3.2rem; margin-top: 14px; }
    .dw20-caption .slidewire-fragment { position: absolute; left: 0; right: 0; top: 0; margin: 0; pointer-events: none; opacity: 0 !important; }
    .dw20-caption .slidewire-fragment-visible:not(:has(~ .slidewire-fragment-visible)) { opacity: 1 !important; }
    .dw20-caption p, .dw20-note { margin: 0; font-size: clamp(18px, 1.8vw, 28px); font-weight: 700; }
    .dw20-cross { display: grid; grid-template-areas: ". n ." "w c e" ". s ."; gap: 10px; justify-content: center; margin-top: 12px; }
    .dw20-cross b { display: grid; place-items: center; width: 92px; height: 72px; border: 1px solid var(--dw-line); background: var(--dw-panel); font-size: 28px; }
    .dw20-cross [data-role="n"] { grid-area: n; } .dw20-cross [data-role="w"] { grid-area: w; }
    .dw20-cross [data-role="c"] { grid-area: c; border-color: var(--dw-cyan); } .dw20-cross [data-role="e"] { grid-area: e; }
    .dw20-cross [data-role="s"] { grid-area: s; }
    .dw20-rungs { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); margin: 22px auto 0; width: min(520px, 100%); }
    .dw20-rungs span { text-align: center; font-size: 22px; color: var(--dw-muted); }
    .dw20-rungs [data-token] { position: absolute; top: -14px; left: 12%; width: 14px; height: 14px; margin-left: -7px; border-radius: 50%; background: var(--dw-lime); }
    .dw20-split3 { display: grid; grid-template-columns: repeat(3, max-content); gap: 28px; justify-content: center; margin-top: 16px; }
    .dw20-split3 h3 { margin: 0 0 8px; text-align: center; letter-spacing: .14em; font-size: 16px; }
    .dw20-rule { display: block; color: var(--dw-muted); font-size: 13px; letter-spacing: 0; font-weight: 400; margin-top: 4px; }
    .dw20-beats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 22px; }
    .dw20-beats div { border: 1px solid var(--dw-line); padding: 14px 10px; text-align: center; }
    .dw20-beats b { display: block; color: var(--dw-cyan); margin-bottom: 8px; }
    .dw20-beats span { display: block; font-size: 15px; line-height: 1.4; }
    .dw20-scale { position: relative; margin: 36px 8px 0; height: 84px; }
    .dw20-scale-bar { position: absolute; left: 0; right: 0; top: 28px; height: 2px; background: linear-gradient(90deg, #34363d 0 33%, #8f8b80 33% 67%, #f29429 67% 100%); }
    .dw20-scale > span { position: absolute; top: 0; transform: translateX(-50%); color: var(--dw-muted); font-size: 14px; }
    .dw20-scale [data-pip] { position: absolute; top: 46px; left: 0; transform: translateX(-50%); font-style: normal; font-size: 14px; font-weight: 700; white-space: nowrap; }
    .dw20-scale [data-pip]::before { content: ""; display: block; width: 10px; height: 10px; margin: 0 auto 4px; background: var(--dw-cyan); }
    .dw20-zones { display: grid; grid-template-columns: 1fr 1fr 1fr; margin-top: 28px; text-align: center; letter-spacing: .14em; font-size: 14px; color: var(--dw-muted); }
    .dw20-eq { font-family: var(--font-mono); font-size: clamp(16px, 1.5vw, 22px); line-height: 1.45; margin: 0; }
    .dw20-center { text-align: center; }
    .dw20-links { display: flex; gap: 18px; margin-top: 22px; }
    .dw20-links a { color: var(--dw-cyan); font-size: 18px; border-bottom: 1px solid rgba(89, 215, 255, .4); }
    .dw20-funnel { display: grid; gap: 10px; margin-top: 18px; }
    .dw20-funnel [data-row] { display: flex; gap: 6px; justify-content: center; }
    .dw20-block { width: 28px; height: 18px; background: #8f8b80; box-shadow: inset 0 0 0 1px rgba(255,255,255,.12); }
    .dw20-funnel [data-row="3"] .dw20-block { background: #f29429; }
    .dw20-hero-board { position: absolute; right: 7%; top: 16%; opacity: .9; }
    .dw20-copy { position: relative; z-index: 1; max-width: 760px; }
    @media (max-width: 1100px) {
        .dw20-row, .dw20-pair { gap: 16px; }
        .dw20-title { font-size: 42px; }
        .dw20-line { font-size: 26px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .dw20-side { transition: none; }
    }
</style>
        <section class="dw-wrap" data-dw-motion="title">
            <div class="dw20-hero-board" data-board></div>
            <div class="dw20-copy">
                <p class="dw20-kicker">Darkwaar20</p>
                <h1 class="dw20-title">From noise<br>to a playable world</h1>
                <p class="dw20-sub">Deterministic generation as gameplay</p>
            </div>
        </section>
        <footer class="dw-footer"><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="facts">
            <div class="dw20-row">
                <div>
                    <p class="dw20-kicker">Seed 20020</p>
                    <h2 class="dw20-line">Start with noise.</h2>
                    <div class="dw20-facts" style="margin-top:22px;">
                        <p class="dw20-fact" data-fact><b>63</b><span>cells</span></p>
                        <p class="dw20-fact" data-fact><b>4</b><span>channels</span></p>
                        <p class="dw20-fact" data-fact><b>1</b><span>seed</span></p>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="determinism">
            <p class="dw20-kicker">Same opening field</p>
            <h2 class="dw20-line">The seed is fixed. The choices are not.</h2>
            <div class="dw20-pair" style="margin-top:18px;">
                <div class="dw20-side" data-side="a" data-board></div>
                <div class="dw20-side" data-side="b" data-board></div>
            </div>
            <div class="dw20-caption">
                <x-slidewire::fragment><p>same seed</p></x-slidewire::fragment>
                <x-slidewire::fragment><p>different constraints</p></x-slidewire::fragment>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="local">
            <p class="dw20-kicker">One cell, four neighbors</p>
            <h2 class="dw20-line">Average, then snap.</h2>
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
                <x-slidewire::fragment><p>average(cell, north, south, east, west)</p></x-slidewire::fragment>
                <x-slidewire::fragment><p>0 · 85 · 170 · 255</p></x-slidewire::fragment>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap dw20-center" data-dw-motion="regions">
            <p class="dw20-kicker">Quantization</p>
            <h2 class="dw20-line">Shared values become regions.</h2>
            <div class="dw20-board" data-board style="justify-content:center;margin-top:18px;"></div>
            <div class="dw20-caption">
                <x-slidewire::fragment><p>Nearby values lose their differences.</p></x-slidewire::fragment>
                <x-slidewire::fragment><p>Structure appears by removing possibilities.</p></x-slidewire::fragment>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="constraints">
            <p class="dw20-kicker">Same patch</p>
            <h2 class="dw20-line">Three edits, not three labels.</h2>
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
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="decisions">
            <p class="dw20-kicker">Five passes</p>
            <h2 class="dw20-line">Each pass is a decision.</h2>
            <div class="dw20-beats">
                <div data-beat><b>01</b><span>condition</span><span>settle</span><span>inspect</span></div>
                <div data-beat><b>02</b><span>condition</span><span>settle</span><span>inspect</span></div>
                <div data-beat><b>03</b><span>condition</span><span>settle</span><span>inspect</span></div>
                <div data-beat><b>04</b><span>condition</span><span>settle</span><span>inspect</span></div>
                <div data-beat><b>05</b><span>condition</span><span>settle</span><span>inspect</span></div>
            </div>
            <p class="dw20-line" data-line style="margin-top:28px;">Generating the world is the first phase of play.</p>
            <div class="dw20-caption">
                <x-slidewire::fragment><p>Pass 1</p></x-slidewire::fragment>
                <x-slidewire::fragment><p>Pass 2</p></x-slidewire::fragment>
                <x-slidewire::fragment><p>Pass 3</p></x-slidewire::fragment>
                <x-slidewire::fragment><p>Pass 4</p></x-slidewire::fragment>
                <x-slidewire::fragment><p>Pass 5</p></x-slidewire::fragment>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="morph">
            <div class="dw20-row">
                <div>
                    <p class="dw20-kicker">Seed 20020</p>
                    <h2 class="dw20-line">Noise becomes terrain.</h2>
                    <div class="dw20-caption">
                        <x-slidewire::fragment><p>Mixed regions</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>Coherent regions</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>VOID · GROUND · ENERGY</p></x-slidewire::fragment>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="thresholds">
            <p class="dw20-kicker">Same four channels</p>
            <h2 class="dw20-line">sum = a + b + c + d</h2>
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
                <x-slidewire::fragment><p>Three cells from the locked field.</p></x-slidewire::fragment>
                <x-slidewire::fragment><p>The class is a reading of the sum.</p></x-slidewire::fragment>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="objective">
            <div class="dw20-row">
                <div>
                    <p class="dw20-kicker">After five passes</p>
                    <h2 class="dw20-line">The route is already in the field.</h2>
                    <pre class="dw20-eq">core = max(a + b + 2c)</pre>
                    <div class="dw20-caption">
                        <x-slidewire::fragment><p>Non-void cells</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>Core</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>Reachable ground and energy</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>Farthest ground → exit</p></x-slidewire::fragment>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="walk">
            <div class="dw20-row">
                <div>
                    <p class="dw20-kicker">Orthogonal steps</p>
                    <h2 class="dw20-line">Void blocks. Energy can be crossed.</h2>
                    <div class="dw20-caption">
                        <x-slidewire::fragment><p>Did generation leave a viable world?</p></x-slidewire::fragment>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="sequenceA">
            <div class="dw20-row">
                <div>
                    <p class="dw20-kicker">Seed 20020</p>
                    <h2 class="dw20-line">Sequence A</h2>
                    <div class="dw20-caption">
                        <x-slidewire::fragment><p>LIFE</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>ORDER</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>LIFE</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>VOID</p></x-slidewire::fragment>
                        <x-slidewire::fragment>
                            <p>ORDER</p>
                            <p class="dw20-sub">Core <span data-core>(3, 0)</span> · Exit <span data-exit>(6, 8)</span><br><span data-void>6</span> void · <span data-ground>54</span> ground · <span data-energy>3</span> energy</p>
                        </x-slidewire::fragment>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="sequenceB">
            <div class="dw20-row">
                <div>
                    <p class="dw20-kicker">Same seed, restored</p>
                    <h2 class="dw20-line">Sequence B</h2>
                    <div class="dw20-caption">
                        <x-slidewire::fragment><p>VOID</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>VOID</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>ORDER</p></x-slidewire::fragment>
                        <x-slidewire::fragment><p>VOID</p></x-slidewire::fragment>
                        <x-slidewire::fragment>
                            <p>LIFE</p>
                            <p class="dw20-sub"><span data-void>63</span> void<br>NO WAY OUT</p>
                        </x-slidewire::fragment>
                    </div>
                </div>
                <div data-board></div>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap dw20-center" data-dw-motion="compare">
            <p class="dw20-kicker">Seed 20020</p>
            <div class="dw20-pair" style="margin-top:16px;">
                <div data-side="a" data-board></div>
                <pre class="dw20-eq" data-equation>same seed
+
different constraints
=
different world</pre>
                <div data-side="b" data-board></div>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="funnel">
            <p class="dw20-kicker">Constraint, not placement</p>
            <h2 class="dw20-line">Possibilities fall away.</h2>
            <div class="dw20-funnel">
                <div data-row="0"><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i></div>
                <div data-row="1"><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i></div>
                <div data-row="2"><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i><i class="dw20-block"></i></div>
                <div data-row="3"><i class="dw20-block"></i></div>
            </div>
            <div class="dw20-caption">
                <x-slidewire::fragment><p>fewer possible worlds</p></x-slidewire::fragment>
                <x-slidewire::fragment><p>stable topology</p></x-slidewire::fragment>
                <x-slidewire::fragment><p>one world</p></x-slidewire::fragment>
            </div>
        </section>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide dw20">
        <section class="dw-wrap" data-dw-motion="close">
            <p class="dw20-kicker">Darkwaar20</p>
            <p class="dw20-line" data-line>Noise is the space of possible worlds.</p>
            <p class="dw20-line" data-line>Five choices remove possibilities.</p>
            <p class="dw20-line" data-line>Then the player walks through what remains.</p>
            <div class="dw20-links" data-line>
                <a href="https://darkwaar.com/blog/2026-10-03-darkwaar20">Release page</a>
                <a href="https://darkwoodcom.itch.io/darkwaar20">Play in the browser</a>
            </div>
        </section>
        <footer class="dw-footer"><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
