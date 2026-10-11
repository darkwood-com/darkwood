{{-- Darkwaar27: Pigment — Rotation, Color Algebra & Deterministic Puzzles --}}
{{-- Route: /slides/darkwaar27-pigment --}}
{{-- Source: tasks/#2ee84a_darkwaar27/article.md --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

{{-- 1 · TITLE --}}
    <x-slidewire::slide class="dw-slide pg">
<style>
    .slidewire-frame.pg,
    .slidewire-frame:has(.pg) { overflow: hidden; padding: 1rem 1.25rem 1.1rem; }
    .slidewire-frame.pg,
    article.pg,
    .pg.dw-slide {
        --pg-bg: #14110e;
        --pg-board: #2a241c;
        --pg-red: #d24a3a;
        --pg-yellow: #e2b134;
        --pg-blue: #3c6fd6;
        --pg-orange: #e07a2f;
        --pg-purple: #8a4bb5;
        --pg-green: #3e9a5a;
        --pg-white: #f4efe4;
        --pg-black: #1a1a1a;
        --pg-muted: #a89a88;
        --pg-line: rgba(244, 239, 228, .16);
        min-height: 100%;
        height: 100%;
        max-height: 100%;
        color: var(--pg-white);
        background:
            radial-gradient(circle at 82% 12%, rgba(226, 177, 52, .14), transparent 28rem),
            radial-gradient(circle at 12% 78%, rgba(60, 111, 214, .12), transparent 24rem),
            linear-gradient(160deg, #1a1510 0%, var(--pg-bg) 42%, #0f0d0b 100%) !important;
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
        color: var(--pg-yellow);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
        margin: 0;
    }
    .pg-title {
        margin: 12px 0 0;
        font-size: clamp(42px, 5.2vw, 78px);
        line-height: .94;
        font-weight: 700;
    }
    .pg-line {
        margin: 0;
        font-size: clamp(28px, 3.2vw, 48px);
        line-height: 1.05;
        font-weight: 700;
    }
    .pg-sub {
        margin: 14px 0 0;
        color: var(--pg-muted);
        font-size: clamp(17px, 1.6vw, 24px);
        line-height: 1.3;
    }
    .pg-take {
        margin: 18px 0 0;
        font-size: clamp(20px, 2vw, 28px);
        font-weight: 700;
        color: var(--pg-yellow);
    }
    .pg-link {
        color: var(--pg-yellow);
        border-bottom: 1px solid rgba(226, 177, 52, .45);
        font-size: clamp(16px, 1.4vw, 20px);
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
        background: rgba(0, 0, 0, .28);
        border: 1px solid var(--pg-line);
        width: max-content;
    }
    .pg-cell {
        position: relative;
        background: var(--pg-board);
        box-shadow: inset 0 0 0 1px rgba(244, 239, 228, .08);
        display: grid;
        place-items: center;
    }
    .pg-cell.is-sealed { box-shadow: inset 0 0 0 2px rgba(244, 239, 228, .35); }
    .pg-cell.is-die { z-index: 2; }
    .pg-die-token {
        position: absolute;
        inset: 18%;
        display: grid;
        place-items: center;
        font-size: calc(var(--pg-cell, 36px) * .28);
        font-weight: 700;
        color: #1a140f;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .35);
        border: 1px solid rgba(244, 239, 228, .35);
    }
    .pg-mark {
        position: absolute;
        inset: 10%;
        border: 2px solid var(--pg-white);
        display: grid;
        place-items: center;
        font-size: calc(var(--pg-cell, 36px) * .26);
        font-weight: 700;
        color: var(--pg-white);
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
        border: 1px solid var(--pg-line);
        font-size: 14px;
        color: #1a140f;
    }
    .pg-swatch span {
        font-size: 11px;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--pg-muted);
    }
    .pg-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }
    .pg-btn {
        appearance: none;
        border: 1px solid var(--pg-line);
        background: rgba(42, 36, 28, .85);
        color: var(--pg-white);
        font: inherit;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 10px 14px;
        cursor: pointer;
    }
    .pg-btn:hover { border-color: var(--pg-yellow); color: var(--pg-yellow); }
    .pg-btn.is-accent { background: var(--pg-yellow); color: #1a140f; border-color: var(--pg-yellow); }
    .pg-status {
        margin: 12px 0 0;
        color: var(--pg-muted);
        font-size: clamp(15px, 1.35vw, 20px);
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
        border: 1px solid var(--pg-line);
        background: rgba(0, 0, 0, .22);
        padding: 10px;
        display: grid;
        gap: 8px;
    }
    .pg-face-card span {
        font-size: 12px;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--pg-muted);
    }
    .pg-face-card b {
        display: grid;
        place-items: center;
        min-height: 48px;
        font-size: 18px;
    }
    .pg-code {
        margin: 14px 0 0;
        padding: 16px 18px;
        background: rgba(0, 0, 0, .38);
        border: 1px solid var(--pg-line);
        font-family: var(--font-mono);
        font-size: clamp(13px, 1.15vw, 16px);
        line-height: 1.45;
        color: #e8dfd0;
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
        font-size: 42px;
        font-weight: 700;
        border: 3px solid rgba(244, 239, 228, .35);
        box-shadow: 0 18px 40px rgba(0, 0, 0, .35);
    }
    .pg-pipe {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-top: 22px;
    }
    .pg-pipe span {
        border: 1px solid var(--pg-line);
        background: rgba(42, 36, 28, .7);
        padding: 12px 14px;
        font-size: clamp(13px, 1.2vw, 17px);
        font-weight: 700;
        letter-spacing: .04em;
    }
    .pg-pipe i {
        color: var(--pg-yellow);
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
        font-size: 14px;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--pg-muted);
    }
    .pg-levels {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-top: 20px;
        width: 100%;
    }
    .pg-level {
        border: 1px solid var(--pg-line);
        background: rgba(0, 0, 0, .22);
        padding: 12px 10px;
        display: grid;
        gap: 10px;
        justify-items: center;
        text-align: center;
    }
    .pg-level b {
        font-size: 13px;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .pg-level span {
        color: var(--pg-muted);
        font-size: 13px;
    }
    .pg-arch {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 22px;
        width: 100%;
    }
    .pg-arch .pg-arch-card {
        border: 1px solid var(--pg-line);
        background: rgba(0, 0, 0, .22);
        padding: 18px 16px;
        min-height: 160px;
    }
    .pg-arch code {
        display: block;
        color: var(--pg-yellow);
        font-family: var(--font-mono);
        font-size: 15px;
        margin-bottom: 10px;
    }
    .pg-arch p {
        margin: 0;
        color: var(--pg-muted);
        font-size: 16px;
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
        border-left: 3px solid var(--pg-yellow);
        padding: 10px 0 10px 16px;
        font-size: clamp(18px, 1.7vw, 24px);
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
        opacity: .95;
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
        color: var(--pg-muted);
        font-size: 13px;
        letter-spacing: .08em;
        text-transform: uppercase;
        z-index: 2;
    }
    .pg-footer img { width: 28px; height: 28px; opacity: .85; }
    .pg-eq {
        font-family: var(--font-mono);
        font-size: clamp(16px, 1.5vw, 22px);
        color: #e8dfd0;
        margin: 16px 0 0;
    }
    .pg-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }
    .pg-chip {
        border: 1px solid var(--pg-line);
        padding: 8px 12px;
        font-size: 14px;
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
        <section class="dw-wrap" data-dw-motion="pg-title">
            <div class="pg-hero-board" data-pg-board="title"></div>
            <div class="pg-hero" data-pg-copy>
                <p class="pg-kicker">Darkwaar27</p>
                <h1 class="pg-title">Pigment</h1>
                <p class="pg-sub">Rotation, Color Algebra &amp; Deterministic Puzzles</p>
                <p class="pg-take" style="margin-top:28px;">One die. Six faces. Twenty-five cells.</p>
                <p style="margin-top:18px;"><a class="pg-link" href="https://darkwoodcom.itch.io/darkwaar27" target="_blank" rel="noopener">darkwoodcom.itch.io/darkwaar27</a></p>
            </div>
        </section>
        <footer class="pg-footer"><span>Darkwood</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · ONE DIE, ONE RULE --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-tip">
            <div class="pg-row">
                <div data-pg-fade>
                    <p class="pg-kicker">One die, one rule</p>
                    <h2 class="pg-line">Movement determines<br>the pigment.</h2>
                    <p class="pg-sub">Tip onto a neighbor. Rotate 90°. The down face stains the cell.</p>
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
            <x-slidewire::fragment><span class="sr-only">east tip</span></x-slidewire::fragment>
        </section>
        <footer class="pg-footer"><span>02 / tip</span></footer>
    </x-slidewire::slide>

    {{-- 3 · THE SIX FACES --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap dw-wrap--top" data-dw-motion="pg-die">
            <p class="pg-kicker">The six faces</p>
            <h2 class="pg-line">Orientation is the resource.</h2>
            <div class="pg-row" style="margin-top:18px;align-items:flex-start;">
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
                    <div class="pg-chip-row" style="margin-top:22px;">
                        <span class="pg-chip"><b style="background:#2a241c"></b>Null ↔ Black</span>
                        <span class="pg-chip"><b style="background:#d24a3a"></b>Red ↔ Blue</span>
                        <span class="pg-chip"><b style="background:#e2b134"></b>Yellow ↔ White</span>
                    </div>
                    <p class="pg-sub" style="max-width:420px;">Opposites stay fixed. Every tip is a permutation of six slots.</p>
                </div>
            </div>
            <x-slidewire::fragment><span class="sr-only">roll east</span></x-slidewire::fragment>
        </section>
        <footer class="pg-footer"><span>03 / faces</span></footer>
    </x-slidewire::slide>

    {{-- 4 · ROTATION IS A PERMUTATION --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-perm">
            <p class="pg-kicker" data-pg-fade>Rotation is a permutation</p>
            <h2 class="pg-line" data-pg-fade>No physics. Just a table.</h2>
            <p class="pg-eq" data-pg-fade>East → D ← E · U ← W · E ← U · W ← D</p>
            <p class="pg-sub" data-pg-fade>North and South unchanged.</p>
            <pre class="pg-code" data-pg-fade>"E":
    return {"D": e, "U": w, "N": n, "S": s, "E": u, "W": d}</pre>
            <p class="pg-take" data-pg-fade>The next state is computed, then drawn.</p>
        </section>
        <footer class="pg-footer"><span>04 / permutation</span></footer>
    </x-slidewire::slide>

    {{-- 5 · COLOR ALGEBRA --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-mix">
            <div class="pg-row">
                <div>
                    <p class="pg-kicker">Color algebra</p>
                    <h2 class="pg-line">next_stain = mix(existing, face)</h2>
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
                    <div class="pg-mix-pot" data-pg-mix-result style="background:#3e9a5a;">G</div>
                    <div class="pg-chip-row">
                        <span class="pg-chip"><b style="background:#e07a2f"></b>Orange</span>
                        <span class="pg-chip"><b style="background:#8a4bb5"></b>Purple</span>
                        <span class="pg-chip"><b style="background:#3e9a5a"></b>Green</span>
                    </div>
                </div>
            </div>
            <x-slidewire::fragment><span class="sr-only">rb</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">yb</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">white</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">black</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">null</span></x-slidewire::fragment>
        </section>
        <footer class="pg-footer"><span>05 / mix</span></footer>
    </x-slidewire::slide>

    {{-- 6 · STATE TRANSITION --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-pipeline">
            <p class="pg-kicker">The state transition</p>
            <h2 class="pg-line">Accept, or spend nothing.</h2>
            <div class="pg-pipe">
                <span data-pg-pipe>Input</span><i>→</i>
                <span data-pg-pipe>Validate</span><i>→</i>
                <span data-pg-pipe>Roll</span><i>→</i>
                <span data-pg-pipe>Mix</span><i>→</i>
                <span data-pg-pipe>Update</span><i>→</i>
                <span data-pg-pipe>Victory?</span>
            </div>
            <pre class="pg-code">if not _floor(current, to):
    return {state, events: {accepted: false}}
# roll → mix → spent += 1 → phase</pre>
            <p class="pg-take">Off-board tips are refused without consuming a turn.</p>
            <x-slidewire::fragment><span class="sr-only">pipeline</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">validate</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">roll</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">mix</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">update</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">victory</span></x-slidewire::fragment>
        </section>
        <footer class="pg-footer"><span>06 / apply</span></footer>
    </x-slidewire::slide>

    {{-- 7 · MORE THAN PATHFINDING --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-state">
            <p class="pg-kicker">More than pathfinding</p>
            <h2 class="pg-line">Same position ≠ same state.</h2>
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
            <x-slidewire::fragment><span class="sr-only">end</span></x-slidewire::fragment>
        </section>
        <footer class="pg-footer"><span>07 / state</span></footer>
    </x-slidewire::slide>

    {{-- 8 · SOLVING GREEN --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-green">
            <div class="pg-row">
                <div>
                    <p class="pg-kicker">Level 2 · Return</p>
                    <h2 class="pg-line">Solving Green</h2>
                    <p class="pg-sub">Start (0,0). Mark Green at (1,0). Limit 5.</p>
                    <p class="pg-eq">E → S → W → N → E</p>
                    <div class="pg-controls">
                        <button type="button" class="pg-btn" data-pg-prev="green">Prev</button>
                        <button type="button" class="pg-btn is-accent" data-pg-next="green">Next</button>
                        <button type="button" class="pg-btn" data-pg-reset="green">Replay</button>
                    </div>
                    <p class="pg-status"><span data-pg-step>0 / 5</span> · <span data-pg-status="green">Start at (0,0).</span></p>
                    <p class="pg-take">Yellow + Blue = Green</p>
                </div>
                <div>
                    <div data-pg-board="green"></div>
                    <div class="pg-compass" data-pg-compass="green"></div>
                </div>
            </div>
            <x-slidewire::fragment><span class="sr-only">1</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">2</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">3</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">4</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">5</span></x-slidewire::fragment>
        </section>
        <footer class="pg-footer"><span>08 / green</span></footer>
    </x-slidewire::slide>

    {{-- 9 · WHAT YOU LEAVE BEHIND --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-leave">
            <p class="pg-kicker" data-pg-fade>Levels 2 → 3</p>
            <h2 class="pg-line" data-pg-fade>A solution is the whole board.</h2>
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
        <footer class="pg-footer"><span>09 / leave</span></footer>
    </x-slidewire::slide>

    {{-- 10 · FIVE LEVELS --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap dw-wrap--top" data-dw-motion="pg-levels">
            <p class="pg-kicker">Five levels, five constraints</p>
            <h2 class="pg-line">Same machine. Steeper asks.</h2>
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
            <p class="pg-sub" style="margin-top:20px;">Purple lives in the mix table — unused by these five boards.</p>
        </section>
        <footer class="pg-footer"><span>10 / levels</span></footer>
    </x-slidewire::slide>

    {{-- 11 · SMALL GODOT ARCHITECTURE --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-arch">
            <p class="pg-kicker" data-pg-fade>Small Godot architecture</p>
            <h2 class="pg-line" data-pg-fade>Rules first. Scene second.</h2>
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
        <footer class="pg-footer"><span>11 / architecture</span></footer>
    </x-slidewire::slide>

    {{-- 12 · RENDERING THE RULES --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-render">
            <div class="pg-row">
                <div>
                    <p class="pg-kicker" data-pg-fade>Rendering the rules</p>
                    <h2 class="pg-line" data-pg-fade>Feedback is comprehension.</h2>
                    <div class="pg-chip-row" data-pg-fade>
                        <span class="pg-chip">Pseudo-3D die</span>
                        <span class="pg-chip">Face glyphs</span>
                        <span class="pg-chip">Pigment tween</span>
                        <span class="pg-chip">Target rings</span>
                        <span class="pg-chip">Roll arc</span>
                        <span class="pg-chip">Orientation compass</span>
                    </div>
                    <p class="pg-sub" data-pg-fade style="max-width:520px;">If facing is unreadable, the mix feels arbitrary. Animation that names a rule is interface, not decoration.</p>
                </div>
                <div data-pg-fade>
                    <div data-pg-board="render"></div>
                </div>
            </div>
        </section>
        <footer class="pg-footer"><span>12 / view</span></footer>
    </x-slidewire::slide>

    {{-- 13 · DETERMINISM AND UNDO --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-undo">
            <div class="pg-row">
                <div>
                    <p class="pg-kicker">Determinism &amp; undo</p>
                    <h2 class="pg-line">S₀ → S₁ → S₂<br>then S₂ → S₁</h2>
                    <p class="pg-sub">Snapshot before accept. Deterministic transition. Known-solution replay.</p>
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
            <x-slidewire::fragment><span class="sr-only">tip</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">undo</span></x-slidewire::fragment>
        </section>
        <footer class="pg-footer"><span>13 / undo</span></footer>
    </x-slidewire::slide>

    {{-- 14 · ENGINEERING LESSONS --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-lessons">
            <p class="pg-kicker">Engineering lessons</p>
            <h2 class="pg-line">What the model implies</h2>
            <ul class="pg-lessons">
                <li data-pg-lesson>Orientation is a resource.</li>
                <li data-pg-lesson>Movement modifies future actions.</li>
                <li data-pg-lesson>Small transition tables create complex decisions.</li>
                <li data-pg-lesson>Deterministic systems replay and debug cleanly.</li>
                <li data-pg-lesson>Animation should expose rules.</li>
                <li data-pg-lesson>Small games benefit from small architectures.</li>
            </ul>
            <x-slidewire::fragment><span class="sr-only">1</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">2</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">3</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">4</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">5</span></x-slidewire::fragment>
            <x-slidewire::fragment><span class="sr-only">6</span></x-slidewire::fragment>
        </section>
        <footer class="pg-footer"><span>14 / lessons</span></footer>
    </x-slidewire::slide>

    {{-- 15 · PLAY --}}
    <x-slidewire::slide class="dw-slide pg">
        <section class="dw-wrap" data-dw-motion="pg-close">
            <div class="pg-row">
                <div data-pg-fade>
                    <p class="pg-kicker">Play Pigment</p>
                    <h2 class="pg-title">One die.<br>Six faces.<br>Twenty-five cells.</h2>
                    <p class="pg-sub" style="margin-top:22px;">Tip carefully. Leave the board you meant.</p>
                    <p style="margin-top:24px;"><a class="pg-link" href="https://darkwoodcom.itch.io/darkwaar27" target="_blank" rel="noopener">https://darkwoodcom.itch.io/darkwaar27</a></p>
                </div>
                <div data-pg-fade>
                    <div data-pg-board="close"></div>
                </div>
            </div>
        </section>
        <footer class="pg-footer"><span>Darkwood · Darkwaar27</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
