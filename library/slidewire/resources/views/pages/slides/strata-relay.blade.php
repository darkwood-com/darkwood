{{-- Strata Relay — procedural terrain and interoperable messages --}}
{{-- Route: /slides/strata-relay --}}
{{-- Public editorial source: Darkwood article 836. --}}

<x-darkwood.deck transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    {{-- 1 · INTRODUCTION --}}
    {{-- @notes Strata Relay is a small deterministic puzzle about changing terrain. This presentation follows the game rules, then uses the same concern for explicit boundaries to explain Symfony 8.2 cross-application messages. --}}
    <x-slidewire::slide class="dw-slide sr">
        <style>
            .slidewire-frame.sr, .slidewire-frame:has(.sr) { overflow: hidden; padding: 1rem 1.25rem 1.1rem; }
            article.sr, .sr.dw-slide {
                --sr-bg: #090b11;
                --sr-panel: #121722;
                --sr-text: #f3efe5;
                --sr-muted: #a8a49d;
                --sr-cyan: #55dbe8;
                --sr-amber: #f5b84c;
                --sr-stone: #404756;
                --sr-line: rgba(243,239,229,.16);
                min-height: 100%; height: 100%; max-height: 100%;
            }
            .slidewire-frame.sr::before, .slidewire-frame.sr::after,
            article.sr::before, article.sr::after, .sr.dw-slide::before, .sr.dw-slide::after { display:none !important; content:none !important; }
            .sr .dw-wrap { width:min(1180px,100%); min-height:0; height:100%; padding:28px 0 20px; justify-content:center; }
            .sr .sr-top { justify-content:flex-start; padding-top:20px; }
            .sr-kicker { margin:0; font-size:14px; font-weight:700; letter-spacing:.16em; text-transform:uppercase; }
            .sr-title { margin:12px 0 0; max-width:760px; font-size:clamp(54px,6.2vw,90px); line-height:.94; font-weight:700; }
            .sr-heading { margin:9px 0 0; max-width:1000px; font-size:clamp(36px,4.4vw,62px); line-height:1; font-weight:700; }
            .sr-lead { margin:20px 0 0; max-width:760px; font-size:clamp(19px,2vw,28px); line-height:1.28; }
            .sr-rule { width:190px; height:3px; margin:22px 0; }
            .sr-grid-2 { display:grid; grid-template-columns:1.1fr .9fr; gap:50px; align-items:center; width:100%; }
            .sr-photo { position:relative; justify-self:end; width:min(355px,31vw); max-height:720px; overflow:hidden; border:1px solid var(--sr-line); }
            .sr-photo img { display:block; width:100%; height:100%; object-fit:cover; object-position:top; }
            .sr-photo::after { content:''; position:absolute; inset:0; pointer-events:none; }
            .sr-list { margin:28px 0 0; padding:0; list-style:none; display:grid; gap:16px; }
            .sr-list li { padding-left:22px; border-left:3px solid var(--sr-cyan); font-size:clamp(19px,1.8vw,25px); line-height:1.25; }
            .sr-list strong { }
            .sr-stage { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-top:30px; }
            .sr-column { position:relative; height:270px; display:flex; align-items:flex-end; justify-content:center; }
            .sr-block { width:86%; min-height:50px; border:1px solid rgba(243,239,229,.18); }
            .sr-block.h1 { height:62px; } .sr-block.h3 { height:142px; } .sr-block.h5 { height:226px; }
            .sr-block.resonant { }
            .sr-column label { position:absolute; bottom:-34px; font-size:16px; }
            .sr-bound { margin:54px auto 0; display:flex; justify-content:center; gap:54px; font-size:21px; }
            .sr-bound strong { font-size:32px; }
            .sr-cross { display:grid; grid-template-columns:repeat(3,86px); grid-template-rows:repeat(3,86px); gap:8px; justify-content:center; margin-top:26px; }
            .sr-cell { display:grid; place-items:center; border:1px solid var(--sr-line); font-family:var(--font-mono); font-size:18px; }
            .sr-cell.hit { }
            .sr-cell.center { }
            .sr-cross .c2 { grid-column:2; grid-row:1; } .sr-cross .c4 { grid-column:1; grid-row:2; }
            .sr-cross .c5 { grid-column:2; grid-row:2; } .sr-cross .c6 { grid-column:3; grid-row:2; }
            .sr-cross .c8 { grid-column:2; grid-row:3; }
            .sr-cycle { display:flex; align-items:center; justify-content:center; gap:18px; margin-top:34px; font-size:22px; }
            .sr-level { min-width:160px; padding:18px; text-align:center; border:1px solid var(--sr-line); }
            .sr-level b { display:block; font-size:28px; } .sr-level span { }
            .sr-cycle-mark { font-size:32px; }
            .sr-pipeline { display:grid; grid-template-columns:1fr 44px 1fr 44px 1fr 44px 1fr; gap:10px; align-items:center; margin-top:44px; }
            .sr-step { min-height:150px; display:grid; align-content:center; gap:8px; padding:18px; border-top:3px solid var(--sr-cyan); }
            .sr-step:nth-of-type(5) { border-top-color:var(--sr-amber); }
            .sr-step b { font-size:21px; } .sr-step span { font-size:16px; line-height:1.3; }
            .sr-arrow { text-align:center; font-size:30px; }
            .sr-proof { margin:30px auto 0; max-width:880px; text-align:center; font-size:21px; }
            .sr-compare { display:grid; grid-template-columns:1fr 1fr; gap:42px; margin-top:38px; }
            .sr-measure { padding:10px 0 18px; border-bottom:1px solid var(--sr-line); }
            .sr-measure b { display:block; font-size:clamp(34px,4vw,58px); }
            .sr-measure span { font-size:18px; }
            .sr-bar { height:18px; margin-top:12px; overflow:hidden; }
            .sr-bar i { display:block; height:100%; }
            .sr-bar.is-amber i { }
            .sr-code { margin:30px 0 0; padding:24px 28px; border:1px solid var(--sr-line); font-family:var(--font-mono); font-size:clamp(14px,1.25vw,18px); line-height:1.5; white-space:pre-wrap; }
            .sr-architecture { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-top:34px; }
            .sr-layer { min-height:170px; padding:24px; border-top:3px solid var(--sr-cyan); }
            .sr-layer:nth-child(2) { border-top-color:var(--sr-amber); }
            .sr-layer b { display:block; font-size:25px; } .sr-layer p { font-size:18px; line-height:1.35; }
            .sr-metrics { display:grid; grid-template-columns:repeat(3,1fr); gap:30px; margin-top:38px; }
            .sr-metric { padding:24px 0; border-top:3px solid var(--sr-cyan); }
            .sr-metric:nth-child(2n) { border-top-color:var(--sr-amber); }
            .sr-metric strong { display:block; font-size:clamp(34px,3.4vw,54px); line-height:1; white-space:nowrap; }
            .sr-metric span { display:block; margin-top:10px; font-size:18px; }
            .sr-message { display:grid; grid-template-columns:1fr 210px 1fr; gap:28px; align-items:center; margin-top:38px; }
            .sr-app { min-height:210px; display:grid; align-content:center; padding:28px; border:1px solid var(--sr-line); }
            .sr-app b { font-size:28px; } .sr-app p { font-size:18px; line-height:1.35; }
            .sr-contract { text-align:center; font-family:var(--font-mono); font-size:17px; line-height:1.45; }
            .sr-ids { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-top:38px; }
            .sr-id { min-height:180px; padding:24px; border-left:3px solid var(--sr-cyan); }
            .sr-id:nth-child(2) { border-left-color:var(--sr-amber); }
            .sr-id code { font-size:20px; } .sr-id p { font-size:17px; line-height:1.35; }
            .sr-principles { display:grid; grid-template-columns:repeat(2,1fr); gap:24px 42px; margin-top:38px; }
            .sr-principle { padding:16px 0; border-bottom:1px solid var(--sr-line); }
            .sr-principle b { display:block; font-size:25px; } .sr-principle p { margin:7px 0 0; font-size:18px; }
            .sr-cta { display:inline-block; margin-top:30px; font-family:var(--font-mono); font-size:clamp(21px,2.3vw,32px); text-decoration:none; border-bottom:2px solid rgba(85,219,232,.5); }
            .sr-mosaic { display:grid; grid-template-columns:repeat(5,42px); gap:8px; transform:rotate(-8deg); }
            .sr-mosaic span { width:42px; height:42px; border:1px solid var(--sr-line); }
            .sr-mosaic span:nth-child(3n) { }
            .sr-mosaic span:nth-child(7n) { }
            .sr-footer { position:absolute; right:26px; bottom:18px; font-size:12px; letter-spacing:.08em; }
            @media (max-height:700px) { .sr .dw-wrap{padding:14px 0 10px}.sr-photo{max-height:610px}.sr-stage{margin-top:16px}.sr-column{height:220px}.sr-block.h5{height:180px}.sr-metrics{margin-top:22px}.sr-metric{padding:14px 0} }
        </style>
        <section class="dw-wrap">
            <div class="sr-grid-2">
                <div>
                    <p class="sr-kicker">Darkwood engineering</p>
                    <h1 class="sr-title">Strata Relay</h1>
                    <div class="sr-rule"></div>
                    <p class="sr-lead">Procedural terrain, deterministic mutations and interoperable messages with Symfony 8.2</p>
                </div>
                <figure class="sr-photo"><img src="/images/strata-relay/cover.png" alt="Strata Relay isometric terrain and crystal anchors"></figure>
            </div>
        </section>
        <span class="sr-footer">01 / 12</span>
    </x-slidewire::slide>

    {{-- 2 · GAME CONCEPT --}}
    {{-- @notes The player never moves a character. A tap pulses one terrain column and its local cross. The terrain decides whether the two crystals can connect. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap">
            <div class="sr-grid-2">
                <div>
                    <p class="sr-kicker">The game concept</p>
                    <h2 class="sr-heading">The player moves the terrain</h2>
                    <ul class="sr-list">
                        <li><strong>Tap one column</strong> to pulse a local cross</li>
                        <li><strong>Read the new contour</strong> before spending the next move</li>
                        <li><strong>Connect both crystals</strong> through middle-height terrain</li>
                    </ul>
                </div>
                <figure class="sr-photo"><img src="/images/strata-relay/screenshot-1.png" alt="Initial Strata Relay board"></figure>
            </div>
        </section>
        <span class="sr-footer">02 / 12</span>
    </x-slidewire::slide>

    {{-- 3 · COMPACT WORLD --}}
    {{-- @notes The logical volume is five by five by five, represented as twenty-five visible columns. Heights one, three and five keep every interaction readable from a fixed camera. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap sr-top">
            <p class="sr-kicker">A compact isometric world</p>
            <h2 class="sr-heading">25 columns inside a 5 × 5 × 5 bound</h2>
            <div class="sr-stage" aria-label="Five example terrain columns at heights one, three and five">
                <div class="sr-column"><div class="sr-block h1"></div><label>height 1</label></div>
                <div class="sr-column"><div class="sr-block h3 resonant"></div><label>height 3</label></div>
                <div class="sr-column"><div class="sr-block h5"></div><label>height 5</label></div>
                <div class="sr-column"><div class="sr-block h3 resonant"></div><label>height 3</label></div>
                <div class="sr-column"><div class="sr-block h1"></div><label>height 1</label></div>
            </div>
            <div class="sr-bound"><span><strong>5 × 5</strong><br>interactive heightfield</span><span><strong>125</strong><br>logical voxel positions</span><span><strong>1 camera</strong><br>fixed portrait view</span></div>
        </section>
        <span class="sr-footer">03 / 12</span>
    </x-slidewire::slide>

    {{-- 4 · TERRAIN MUTATIONS --}}
    {{-- @notes A legal action changes the selected mutable column and up to four cardinal neighbours. Every affected column cycles through three levels. Immutable rock stays unchanged. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap">
            <p class="sr-kicker">Terrain mutations</p>
            <h2 class="sr-heading">One pulse changes at most five columns</h2>
            <div class="sr-grid-2" style="margin-top:28px;">
                <div>
                    <div class="sr-cross" aria-label="Local cross affected by one terrain pulse">
                        <div class="sr-cell hit c2">+1</div><div class="sr-cell hit c4">+1</div><div class="sr-cell hit center c5">tap</div><div class="sr-cell hit c6">+1</div><div class="sr-cell hit c8">+1</div>
                    </div>
                    <p class="sr-proof">Only mutable cells inside the board advance.</p>
                </div>
                <div>
                    <div class="sr-cycle">
                        <div class="sr-level"><b>1</b><span>low</span></div><div class="sr-cycle-mark">›</div>
                        <div class="sr-level"><b style="color:var(--sr-cyan)">3</b><span>resonant</span></div><div class="sr-cycle-mark">›</div>
                        <div class="sr-level"><b>5</b><span>high</span></div>
                    </div>
                    <p class="sr-proof">The wrap from high to low makes each pulse reversible.</p>
                </div>
            </div>
        </section>
        <span class="sr-footer">04 / 12</span>
    </x-slidewire::slide>

    {{-- 5 · PROCEDURAL GENERATION --}}
    {{-- @notes The generator constructs a valid resonant route first. It adds decoys and immutable rock, scrambles the route with known pulses, then replays the inverse sequence through the public rule. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap">
            <p class="sr-kicker">Procedural generation</p>
            <h2 class="sr-heading">Solvability comes from construction</h2>
            <div class="sr-pipeline">
                <div class="sr-step"><b>Seed + stage</b><span>One deterministic input recreates the same puzzle.</span></div><div class="sr-arrow">›</div>
                <div class="sr-step"><b>Valid route</b><span>A middle-height path links source and target.</span></div><div class="sr-arrow">›</div>
                <div class="sr-step"><b>Local scramble</b><span>Known pulses add decoys and disruption.</span></div><div class="sr-arrow">›</div>
                <div class="sr-step"><b>Witness replay</b><span>The same public rule verifies a route to victory.</span></div>
            </div>
            <p class="sr-proof">The witness proves solvability. It does not claim the shortest or only solution.</p>
        </section>
        <span class="sr-footer">05 / 12</span>
    </x-slidewire::slide>

    {{-- 6 · COMPACT SOFTWARE --}}
    {{-- @notes Procedural rules reduce stored level content, but they do not make the engine runtime small. The maintained game code and the exported WebAssembly build answer different questions. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap">
            <p class="sr-kicker">Small rules, many situations</p>
            <h2 class="sr-heading">Source compactness and delivery size differ</h2>
            <div class="sr-compare">
                <div>
                    <div class="sr-measure"><b>22,202 bytes</b><span>runtime GDScript</span><div class="sr-bar"><i style="width:18%"></i></div></div>
                    <div class="sr-measure"><b>0 imported</b><span>gameplay art or audio assets</span><div class="sr-bar"><i style="width:3%"></i></div></div>
                </div>
                <div>
                    <div class="sr-measure"><b>39.5 MB</b><span>raw WebAssembly runtime</span><div class="sr-bar is-amber"><i style="width:100%"></i></div></div>
                    <div class="sr-measure"><b>Unmeasured</b><span>isolated game-only runtime memory</span><div class="sr-bar is-amber"><i style="width:0%"></i></div></div>
                </div>
            </div>
            <p class="sr-proof">Procedural generation reduces authored level data. It does not automatically reduce every other metric.</p>
        </section>
        <span class="sr-footer">06 / 12</span>
    </x-slidewire::slide>

    {{-- 7 · GODOT IMPLEMENTATION --}}
    {{-- @notes The rule layer owns the authoritative state transition. Rendering receives the changed cells and animates them after the next state already exists. Connectivity is derived with a bounded traversal over twenty-five cells. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap sr-top">
            <p class="sr-kicker">Godot implementation</p>
            <h2 class="sr-heading">State first, animation second</h2>
            <pre class="sr-code">for changed in affected:
    var before := int(next["levels"][index_of(changed)])
    var after := (before + 1) % 3
    next["levels"][index_of(changed)] = after
    events.append({"cell": changed, "before": before, "after": after})</pre>
            <div class="sr-architecture">
                <div class="sr-layer"><b>Rules</b><p>Generate, mutate and evaluate the authoritative board.</p></div>
                <div class="sr-layer"><b>Events</b><p>Describe the local columns that changed during one turn.</p></div>
                <div class="sr-layer"><b>View</b><p>Interpolate heights and redraw the derived signal path.</p></div>
            </div>
        </section>
        <span class="sr-footer">07 / 12</span>
    </x-slidewire::slide>

    {{-- 8 · PERFORMANCE AND VALIDATION --}}
    {{-- @notes The measurements come from one Apple M4 development host. They describe this build, not a universal mobile guarantee. The larger value is repeatable validation of the small deterministic state space. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap">
            <p class="sr-kicker">Performance and validation</p>
            <h2 class="sr-heading">Measured results from the completed game</h2>
            <div class="sr-metrics">
                <div class="sr-metric"><strong>557</strong><span>rule assertions<br>zero failures</span></div>
                <div class="sr-metric"><strong>5,000</strong><span>generated puzzles<br>witness replay passed</span></div>
                <div class="sr-metric"><strong>80.564 µs</strong><span>average generation<br>measured host</span></div>
                <div class="sr-metric"><strong>12.346 µs</strong><span>average local update<br>including state copy</span></div>
                <div class="sr-metric"><strong>686</strong><span>runtime GDScript lines</span></div>
                <div class="sr-metric"><strong>25</strong><span>cells examined at most<br>during connectivity</span></div>
            </div>
        </section>
        <span class="sr-footer">08 / 12</span>
    </x-slidewire::slide>

    {{-- 9 · RESULT --}}
    {{-- @notes Victory appears when the resonant connected component reaches the second crystal. The game recomputes that component after each local mutation and never regenerates the whole board. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap">
            <div class="sr-grid-2">
                <div>
                    <p class="sr-kicker">A visible consequence</p>
                    <h2 class="sr-heading">Connectivity turns terrain into the objective</h2>
                    <p class="sr-lead">Middle-height columns carry the cyan signal. Victory arrives when one connected component reaches both crystal anchors.</p>
                    <p class="sr-proof" style="text-align:left;margin-left:0;">The board evolves locally. The signal is derived from the resulting state.</p>
                </div>
                <figure class="sr-photo"><img src="/images/strata-relay/screenshot-2.png" alt="Completed Strata Relay board with a connected cyan path"></figure>
            </div>
        </section>
        <span class="sr-footer">09 / 12</span>
    </x-slidewire::slide>

    {{-- 10 · CROSS-APPLICATION MESSAGING --}}
    {{-- @notes Cross-application messaging is a separate technical subject. A hypothetical completed-session event can cross an application boundary without moving the terrain simulation out of the game. Strata Relay does not emit this event today. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap">
            <p class="sr-kicker">Symfony 8.2 cross-application messaging</p>
            <h2 class="sr-heading">A contract can cross the boundary</h2>
            <div class="sr-message">
                <div class="sr-app"><b>Application A</b><p>Owns its local simulation and publishes one accepted fact.</p></div>
                <div class="sr-contract">game.session<br>completed.v1</div>
                <div class="sr-app"><b>Application B</b><p>Maps the stable type to its own schema and behavior.</p></div>
            </div>
            <p class="sr-proof">Hypothetical example. Strata Relay remains offline and does not emit this event.</p>
        </section>
        <span class="sr-footer">10 / 12</span>
    </x-slidewire::slide>

    {{-- 11 · MESSAGE IDENTITY --}}
    {{-- @notes Symfony's interoperability serializer keeps the JSON contract independent from application-specific Messenger stamps. Identity headers explain what happened, which workflow it belongs to and what caused it. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap">
            <p class="sr-kicker">Message identity</p>
            <h2 class="sr-heading">Three identifiers answer three questions</h2>
            <div class="sr-ids">
                <div class="sr-id"><code>message_id</code><p>Which logical event is this, across delivery attempts?</p></div>
                <div class="sr-id"><code>correlation_id</code><p>Which wider workflow groups this event with related messages?</p></div>
                <div class="sr-id"><code>causation_id</code><p>Which earlier command or event produced this one?</p></div>
            </div>
            <pre class="sr-code" style="margin-top:24px;">#[AsMessage(serializedTypeName: 'game.session.completed.v1')]
final readonly class GameSessionCompleted { /* explicit versioned fields */ }</pre>
        </section>
        <span class="sr-footer">11 / 12</span>
    </x-slidewire::slide>

    {{-- 12 · CONCLUSION AND CTA --}}
    {{-- @notes Strata Relay demonstrates how a small deterministic ruleset can create many readable states. The messaging lesson is parallel: keep local behavior local, and cross application boundaries only with explicit versioned contracts. --}}
    <x-slidewire::slide class="dw-slide sr">
        <section class="dw-wrap">
            <div class="sr-grid-2">
                <div>
                    <p class="sr-kicker">Conclusion</p>
                    <h2 class="sr-heading">Small rules, explicit boundaries</h2>
                    <div class="sr-principles">
                        <div class="sr-principle"><b>Deterministic worlds</b><p>A seed reconstructs the same playable state.</p></div>
                        <div class="sr-principle"><b>Local consequences</b><p>One action changes a bounded, visible neighborhood.</p></div>
                        <div class="sr-principle"><b>Derived signals</b><p>Connectivity follows the terrain that actually exists.</p></div>
                        <div class="sr-principle"><b>Versioned contracts</b><p>Applications exchange facts without sharing internals.</p></div>
                    </div>
                    <a class="sr-cta" href="https://darkwaar.com/games/strata-relay">darkwaar.com/games/strata-relay</a>
                </div>
                <div class="sr-mosaic" aria-label="Abstract five by five terrain mosaic">
                    @for ($i = 0; $i < 25; $i++)<span></span>@endfor
                </div>
            </div>
        </section>
        <span class="sr-footer">12 / 12</span>
    </x-slidewire::slide>

</x-darkwood.deck>
