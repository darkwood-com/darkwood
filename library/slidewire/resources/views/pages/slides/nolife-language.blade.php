{{-- Nolife Language — Don't say cost. Say investment. --}}
{{-- Source: tasks/#ff815c_nolife-language/article.md · Companion: content/nolife-language --}}
{{-- Route: /slides/nolife-language --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

<style>
    .nl-red { color: #ff8a70; }
    .nl-gold { color: #ffd36a; }
    .nl-lime { color: var(--dw-lime); }
    .nl-mono { font-family: var(--font-mono); }
    .nl-muted { color: var(--dw-muted); font-size: 16px; }
    .nl-note { margin-top: 16px; color: var(--dw-muted); font-size: 16px; font-family: var(--font-mono); }
    .nl-kpi { font-size: clamp(72px, 10vw, 128px); line-height: .9; font-weight: 700; letter-spacing: -0.03em; }
    .nl-kpi-sub { margin-top: 18px; color: var(--dw-muted); font-size: clamp(22px, 2.2vw, 30px); }
    .nl-word { font-size: clamp(48px, 6vw, 84px); font-weight: 700; line-height: .95; letter-spacing: -0.02em; }
    .nl-swap { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }
    .nl-bar-row { display: flex; align-items: center; gap: 14px; margin: 12px 0; font-family: var(--font-mono); }
    .nl-bar-label { width: 92px; color: var(--dw-muted); font-weight: 700; text-transform: uppercase; letter-spacing: .04em; font-size: 16px; }
    .nl-bar-track { flex: 1; height: 22px; background: rgba(255,255,255,.06); border-radius: 4px; overflow: hidden; border: 1px solid var(--dw-line); }
    .nl-bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--dw-blue), var(--dw-cyan)); }
    .nl-bar-fill.is-alt { background: linear-gradient(90deg, #c9842a, #ffd36a); }
    .nl-bar-fill.is-warn { background: linear-gradient(90deg, #c44b3a, #ff8a70); }
    .nl-bar-val { width: 56px; text-align: right; color: #eef7ff; font-weight: 700; font-size: 22px; }
    .nl-bar-delta { width: 110px; color: var(--dw-cyan); font-size: 16px; font-weight: 700; }
    .nl-pair { display: grid; grid-template-columns: 1fr auto 1fr; gap: 18px; align-items: center; }
    .nl-pair .dw-card { text-align: center; padding: 22px 18px; }
    .nl-pair .nl-count { display: block; margin-top: 8px; font-family: var(--font-mono); font-size: 28px; font-weight: 700; }
    .nl-table { width: 100%; border-collapse: collapse; font-family: var(--font-mono); font-size: 18px; }
    .nl-table th, .nl-table td { padding: 10px 12px; text-align: right; border-bottom: 1px solid var(--dw-line); }
    .nl-table th:first-child, .nl-table td:first-child,
    .nl-table th:nth-child(2), .nl-table td:nth-child(2) { text-align: left; }
    .nl-table th { color: var(--dw-cyan); font-size: 13px; letter-spacing: .06em; text-transform: uppercase; font-weight: 700; }
    .nl-table td { color: #eef7ff; }
    .nl-table tr.is-warn td { color: #ffb4a8; }
    .nl-triangle { position: relative; height: 340px; margin: 12px auto 0; max-width: 760px; }
    .nl-vertex { position: absolute; min-width: 210px; padding: 18px 22px; text-align: center; font-size: 28px; font-weight: 700; }
    .nl-vertex--top { left: 50%; top: 0; transform: translateX(-50%); }
    .nl-vertex--left { left: 0; bottom: 0; }
    .nl-vertex--right { right: 0; bottom: 0; }
    .nl-tri-line { position: absolute; background: var(--dw-cyan); opacity: .55; }
    .nl-tri-line--v { width: 2px; height: 118px; left: 50%; top: 78px; transform: translateX(-50%); }
    .nl-tri-line--h { height: 2px; width: calc(100% - 240px); left: 120px; bottom: 48px; }
    .nl-corner { padding: 18px 20px; min-height: 168px; }
    .nl-corner h3 { font-size: 15px; letter-spacing: .08em; text-transform: uppercase; color: var(--dw-cyan); margin: 0 0 12px; }
    .nl-corner p { margin: 0; font-size: 22px; font-weight: 700; color: #eef7ff; line-height: 1.25; }
    .nl-corner .nl-delta { display: block; margin-top: 10px; font-family: var(--font-mono); font-size: 20px; }
    .nl-corner.is-warn { border-color: rgba(255, 120, 100, .4); }
    .nl-corner.is-ok { border-color: rgba(184, 255, 106, .4); }
    .nl-corner.is-gold { border-color: rgba(255, 211, 106, .4); }
    .nl-neq { font-size: clamp(28px, 3vw, 40px); font-weight: 700; text-align: center; color: var(--dw-cyan); }
    .nl-stack { display: grid; gap: 10px; }
    .nl-stack .dw-chip { font-size: 17px; }
</style>

    {{-- 1 · TITLE --}}
    {{-- @notes
        Nolife Language. Don't say cost. Say investment.
        Words change persuasion for humans — and token cost for models.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · Symfony · Flow · language</p>
            <h1 class="dw-title">Nolife Language</h1>
            <p class="dw-lead">Don't say cost. Say <span class="dw-accent">investment</span>.</p>
            <p class="mt-8" style="color:var(--dw-muted);font-size:22px;">When words change both persuasion and token cost.</p>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · WORDS CHANGE THE FRAME --}}
    {{-- @notes
        Sales advice for humans. Same offer, different frame.
        Do not claim the psychology is proven — it is the laboratory vocabulary.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Words change the frame</p>
            <div class="nl-swap" style="margin-top:12px;">
                <div class="nl-word" style="color:#ff8a70;">COST</div>
                <div class="dw-arrow">↓</div>
                <div class="nl-word" style="color:#ffd36a;">INVESTMENT</div>
            </div>
            <div class="mt-10 dw-grid dw-grid-3" style="gap:10px;">
                <div class="dw-chip">basic → essential</div>
                <div class="dw-chip">standard → customized</div>
                <div class="dw-chip">few → limited</div>
            </div>
            <p class="dw-takeaway mt-8">Same offer. Different frame.</p>
        </section>
        <footer class="dw-footer"><span>01 / frame</span></footer>
    </x-slidewire::slide>

    {{-- 3 · THEN THE TOKENIZER READS IT --}}
    {{-- @notes
        Billing and context windows count tokens, not words.
        Encoding is part of the result. Not Claude or Gemini tokens.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Then the tokenizer reads it</p>
            <h2 class="dw-heading-slide">Humans hear meaning.<br>Tokenizers count <span class="dw-accent">pieces</span>.</h2>
            <div class="mt-10 dw-grid dw-grid-3" style="gap:14px;">
                <div class="dw-card" style="text-align:center;">
                    <p class="nl-neq">words ≠ tokens</p>
                </div>
                <div class="dw-card" style="text-align:center;">
                    <p class="nl-neq">chars ≠ tokens</p>
                </div>
                <div class="dw-card" style="text-align:center;">
                    <p class="nl-neq">encoding matters</p>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>02 / tokenizer</span></footer>
    </x-slidewire::slide>

    {{-- 4 · THE TRIANGLE --}}
    {{-- @notes
        Central idea. Meaning, token cost, and persuasion are three axes.
        Improving one can hurt another.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Three axes</p>
            <div class="dw-flow dw-flow--vertical" style="max-width:720px;margin:20px auto 0;align-items:center;">
                <div class="dw-node" style="min-height:auto;padding:18px 28px;width:280px;">meaning</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-flow" style="margin-top:0;width:100%;">
                    <div class="dw-node" style="min-height:auto;padding:18px 22px;">token cost</div>
                    <div class="dw-arrow">+</div>
                    <div class="dw-node" style="min-height:auto;padding:18px 22px;">persuasion</div>
                </div>
            </div>
            <p class="dw-takeaway mt-8">Improving one axis can hurt another.</p>
        </section>
        <footer class="dw-footer"><span>03 / triangle</span></footer>
    </x-slidewire::slide>

    {{-- 5 · THREE LANGUAGES --}}
    {{-- @notes
        Parallel corpus, o200k_base. EN 46 · FR 58 · DE 63.
        This corpus × this tokenizer. Not “French costs 26% more.”
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">o200k_base · parallel corpus</p>
            <h2 class="dw-heading-slide">Same idea. Three languages.</h2>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:14px;">
                <div class="dw-card" style="text-align:center;padding:22px 16px;">
                    <h3 style="font-size:18px;color:var(--dw-muted);">English</h3>
                    <p style="font-size:72px;font-weight:700;line-height:.9;color:#eef7ff;margin-top:8px;">46</p>
                    <p style="margin-top:8px;color:var(--dw-muted);">baseline</p>
                    <div style="margin-top:14px;height:8px;background:rgba(255,255,255,.08);border-radius:4px;">
                        <div style="height:8px;width:73%;background:linear-gradient(90deg,#0b84ff,#59d7ff);border-radius:4px;"></div>
                    </div>
                </div>
                <div class="dw-card" style="text-align:center;padding:22px 16px;">
                    <h3 style="font-size:18px;color:var(--dw-muted);">French</h3>
                    <p style="font-size:72px;font-weight:700;line-height:.9;color:#eef7ff;margin-top:8px;">58</p>
                    <p style="margin-top:8px;color:var(--dw-cyan);font-weight:700;">+26.09%</p>
                    <div style="margin-top:14px;height:8px;background:rgba(255,255,255,.08);border-radius:4px;">
                        <div style="height:8px;width:92.1%;background:linear-gradient(90deg,#0b84ff,#59d7ff);border-radius:4px;"></div>
                    </div>
                </div>
                <div class="dw-card" style="text-align:center;padding:22px 16px;">
                    <h3 style="font-size:18px;color:var(--dw-muted);">German</h3>
                    <p style="font-size:72px;font-weight:700;line-height:.9;color:#eef7ff;margin-top:8px;">63</p>
                    <p style="margin-top:8px;color:var(--dw-cyan);font-weight:700;">+36.96%</p>
                    <div style="margin-top:14px;height:8px;background:rgba(255,255,255,.08);border-radius:4px;">
                        <div style="height:8px;width:100%;background:linear-gradient(90deg,#0b84ff,#59d7ff);border-radius:4px;"></div>
                    </div>
                </div>
            </div>
            <p class="nl-note" style="text-align:center;">this corpus × this tokenizer — not a universal language tax</p>
        </section>
        <footer class="dw-footer"><span>04 / languages</span></footer>
    </x-slidewire::slide>

    {{-- 6 · TOKENIZER CHANGES THE RESULT --}}
    {{-- @notes
        Same texts, cl100k_base. English stays 46. French and German jump.
        Language cost is corpus × tokenizer × language.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Same texts · two encodings</p>
            <h2 class="dw-heading-slide">Language cost is not a constant.</h2>
            <table class="nl-table mt-6">
                <thead>
                    <tr>
                        <th></th>
                        <th style="color:#59d7ff;">o200k_base</th>
                        <th style="color:#ffd36a;">cl100k_base</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>English</td><td>46</td><td>46</td></tr>
                    <tr><td>French</td><td>58</td><td>71</td></tr>
                    <tr><td>German</td><td>63</td><td>73</td></tr>
                </tbody>
            </table>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:12px;">
                <div class="dw-chip">FR +26.09% → <span style="color:#ffd36a;">+54.35%</span></div>
                <div class="dw-chip">DE +36.96% → <span style="color:#ffd36a;">+58.70%</span></div>
            </div>
            <p class="nl-note" style="text-align:center;">corpus × tokenizer × language</p>
        </section>
        <footer class="dw-footer"><span>05 / encoding</span></footer>
    </x-slidewire::slide>

    {{-- 7 · STRONGER WORD, MORE TOKENS --}}
    {{-- @notes
        German hero. maßgeschneidert 1 → 5 (+400%). unverzichtbar 1 → 4.
        Better sales framing can be worse BPE.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">o200k_base · German persuasion</p>
            <div class="dw-flow" style="margin-top:18px;">
                <div class="dw-card" style="flex:1;text-align:center;border-color:rgba(255,120,100,.45);">
                    <p class="nl-word" style="font-size:clamp(28px,3.2vw,44px);">Standard</p>
                    <span class="nl-count" style="color:#ff8a70;">1 token</span>
                </div>
                <div class="dw-arrow">→</div>
                <div class="dw-card" style="flex:1;text-align:center;border-color:rgba(255,211,106,.5);">
                    <p class="nl-word" style="font-size:clamp(24px,2.6vw,38px);">maßgeschneidert</p>
                    <span class="nl-count" style="color:#ffd36a;">5 tokens</span>
                </div>
            </div>
            <p style="text-align:center;font-size:42px;font-weight:700;color:var(--dw-cyan);margin-top:12px;">+400%</p>
            <p class="mt-6" style="text-align:center;color:var(--dw-muted);font-size:22px;">Basis 1 → unverzichtbar 4</p>
            <p class="dw-takeaway mt-6">Better sales framing can be worse BPE.</p>
        </section>
        <footer class="dw-footer"><span>06 / regression</span></footer>
    </x-slidewire::slide>

    {{-- 8 · FOUR CORNERS --}}
    {{-- @notes
        All four corners of the triangle on this run.
        Charm pricing can help both. Investment is a wash. maßgeschneidert costs. passend recovers.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Four corners of the experiment</p>
            <h2 class="dw-heading-slide">A phrase can win one axis and lose another.</h2>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:12px;">
                <div class="dw-card nl-corner is-ok">
                    <h3>stronger frame · fewer tokens</h3>
                    <p>$1,000 → $999</p>
                    <span class="nl-delta nl-lime">4 → 2</span>
                </div>
                <div class="dw-card nl-corner is-gold">
                    <h3>different frame · same tokens</h3>
                    <p>cost → investment</p>
                    <span class="nl-delta nl-gold">1 → 1</span>
                </div>
                <div class="dw-card nl-corner is-warn">
                    <h3>stronger frame · more tokens</h3>
                    <p>Standard → maßgeschneidert</p>
                    <span class="nl-delta nl-red">1 → 5</span>
                </div>
                <div class="dw-card nl-corner is-ok">
                    <h3>simpler frame · fewer tokens</h3>
                    <p>maßgeschneidert → passend</p>
                    <span class="nl-delta nl-lime">5 → 2</span>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>07 / corners</span></footer>
    </x-slidewire::slide>

    {{-- 9 · THE RED ROWS --}}
    {{-- @notes
        9 of 27 pair rows got more expensive. All persuasion. Zero token_opt.
        A benchmark that only shows green rows is a brochure.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Regressions</p>
            <h2 class="dw-heading-slide"><span class="nl-red">9 / 27</span> rewrites got more expensive</h2>
            <p class="mt-2" style="color:var(--dw-muted);font-size:22px;">all 9 were persuasion · 0 were token_opt</p>
            <table class="nl-table mt-6">
                <thead>
                    <tr><th>Original</th><th>Replacement</th><th>Before</th><th>After</th></tr>
                </thead>
                <tbody>
                    <tr class="is-warn"><td>Basis</td><td>unverzichtbar</td><td>1</td><td>4</td></tr>
                    <tr class="is-warn"><td>Standard</td><td>maßgeschneidert</td><td>1</td><td>5</td></tr>
                    <tr class="is-warn"><td>Kosten</td><td>Investition</td><td>1</td><td>2</td></tr>
                    <tr class="is-warn"><td>achetez maintenant</td><td>plus que trois en stock</td><td>3</td><td>5</td></tr>
                </tbody>
            </table>
            <p class="dw-takeaway mt-6">A benchmark that only shows green rows is a brochure.</p>
        </section>
        <footer class="dw-footer"><span>08 / red-rows</span></footer>
    </x-slidewire::slide>

    {{-- 10 · 62 → 62 --}}
    {{-- @notes
        Mixed aggregate is a cancel. Persuasion +9, token_opt −9.
        Cancellation is not neutrality.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Mixed pair aggregate</p>
            <p class="nl-kpi" style="text-align:center;font-size:clamp(84px,11vw,140px);">62 <span class="dw-accent">→</span> 62</p>
            <p class="nl-kpi-sub" style="text-align:center;">Zero change in the total. Huge change underneath.</p>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:14px;">
                <div class="dw-card" style="text-align:center;border-color:rgba(255,120,100,.4);">
                    <h3 style="font-size:18px;color:#ff8a70;">persuasion</h3>
                    <p class="nl-mono" style="font-size:32px;font-weight:700;color:#eef7ff;margin-top:8px;">36 → 45</p>
                    <p class="nl-red" style="margin-top:4px;">+9 tokens</p>
                </div>
                <div class="dw-card" style="text-align:center;border-color:rgba(184,255,106,.4);">
                    <h3 style="font-size:18px;color:var(--dw-lime);">token_opt</h3>
                    <p class="nl-mono" style="font-size:32px;font-weight:700;color:#eef7ff;margin-top:8px;">26 → 17</p>
                    <p class="nl-lime" style="margin-top:4px;">−9 tokens</p>
                </div>
            </div>
            <p class="dw-takeaway mt-6">Cancellation ≠ neutrality.</p>
        </section>
        <footer class="dw-footer"><span>09 / cancel</span></footer>
    </x-slidewire::slide>

    {{-- 11 · COST IS SMALL --}}
    {{-- @notes
        ESTIMATED gpt-4o-mini snapshot at 1,000,000 requests. Tiny corpus. Not an invoice.
        Opposite strategies cancel into $0.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">ESTIMATED · 1,000,000 requests · pair tokens</p>
            <h2 class="dw-heading-slide">The money is tiny. The structure is not.</h2>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:12px;">
                <div class="dw-card" style="text-align:center;border-color:rgba(255,120,100,.4);">
                    <h3 style="font-size:18px;">persuasion</h3>
                    <p class="nl-mono" style="font-size:22px;color:#eef7ff;">input −$1.35</p>
                    <p class="nl-mono" style="font-size:22px;color:#eef7ff;">output −$5.40</p>
                </div>
                <div class="dw-card" style="text-align:center;border-color:rgba(184,255,106,.4);">
                    <h3 style="font-size:18px;">token_opt</h3>
                    <p class="nl-mono" style="font-size:22px;color:#eef7ff;">input +$1.35</p>
                    <p class="nl-mono" style="font-size:22px;color:#eef7ff;">output +$5.40</p>
                </div>
                <div class="dw-card" style="text-align:center;border-color:rgba(89,215,255,.45);">
                    <h3 style="font-size:18px;">mixed</h3>
                    <p class="nl-kpi" style="font-size:56px;margin-top:8px;">$0.00</p>
                </div>
            </div>
            <p class="nl-note">tiny corpus · dated gpt-4o-mini snapshot · not a provider invoice</p>
        </section>
        <footer class="dw-footer"><span>10 / cost</span></footer>
    </x-slidewire::slide>

    {{-- 12 · SAME WINDOW, LESS LANGUAGE --}}
    {{-- @notes
        Hypothetical 100,000-token fill of the parallel offer.
        The model does not get a smaller window. The same budget fits less of this text.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">100,000-token hypothetical window · o200k_base</p>
            <h2 class="dw-heading-slide">Same window. Less language.</h2>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:14px;">
                <div class="dw-card" style="text-align:center;padding:22px 14px;">
                    <h3 style="font-size:18px;color:var(--dw-muted);">English</h3>
                    <p style="font-family:var(--font-mono);font-size:36px;font-weight:700;color:#eef7ff;margin-top:8px;">2,173.91</p>
                    <p style="margin-top:6px;color:var(--dw-muted);">copies</p>
                    <div style="margin-top:14px;height:8px;background:rgba(255,255,255,.08);border-radius:4px;">
                        <div style="height:8px;width:100%;background:linear-gradient(90deg,#0b84ff,#59d7ff);border-radius:4px;"></div>
                    </div>
                </div>
                <div class="dw-card" style="text-align:center;padding:22px 14px;">
                    <h3 style="font-size:18px;color:var(--dw-muted);">French</h3>
                    <p style="font-family:var(--font-mono);font-size:36px;font-weight:700;color:#eef7ff;margin-top:8px;">1,724.14</p>
                    <p style="margin-top:6px;color:var(--dw-cyan);font-weight:700;">−20.69%</p>
                    <div style="margin-top:14px;height:8px;background:rgba(255,255,255,.08);border-radius:4px;">
                        <div style="height:8px;width:79.3%;background:linear-gradient(90deg,#0b84ff,#59d7ff);border-radius:4px;"></div>
                    </div>
                </div>
                <div class="dw-card" style="text-align:center;padding:22px 14px;">
                    <h3 style="font-size:18px;color:var(--dw-muted);">German</h3>
                    <p style="font-family:var(--font-mono);font-size:36px;font-weight:700;color:#eef7ff;margin-top:8px;">1,587.30</p>
                    <p style="margin-top:6px;color:var(--dw-cyan);font-weight:700;">−26.98%</p>
                    <div style="margin-top:14px;height:8px;background:rgba(255,255,255,.08);border-radius:4px;">
                        <div style="height:8px;width:73%;background:linear-gradient(90deg,#0b84ff,#59d7ff);border-radius:4px;"></div>
                    </div>
                </div>
            </div>
            <p class="dw-takeaway mt-8">The model does not get a smaller context window.<br>The same token budget fits less of this text.</p>
        </section>
        <footer class="dw-footer"><span>11 / context</span></footer>
    </x-slidewire::slide>

    {{-- 13 · CAVEMAN LESSON --}}
    {{-- @notes
        Methodology from Caveman, not a Nolife Language rerun.
        Shorter output is not automatically lower total cost. Publish regressions.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Caveman · method, not a rerun</p>
            <h2 class="dw-heading-slide">Compression has overhead too.</h2>
            <p class="nl-neq mt-8">shorter output &nbsp;≠&nbsp; lower total cost</p>
            <div class="mt-10 dw-grid dw-grid-3" style="gap:12px;">
                <div class="dw-chip">publish regressions</div>
                <div class="dw-chip">compare against a control</div>
                <div class="dw-chip">output ≠ invoice</div>
            </div>
            <p class="nl-note" style="text-align:center;">Inspected as methodology. Not a second benchmark on this corpus.</p>
        </section>
        <footer class="dw-footer"><span>12 / caveman</span></footer>
    </x-slidewire::slide>

    {{-- 14 · IMPLEMENTATION --}}
    {{-- @notes
        Symfony 8.1 console app. PHP 8.5.4. darkwood/flow 8.1.6. yethee/tiktoken 1.1.1.
        php bin/console app:language:benchmark
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">content/nolife-language</p>
            <h2 class="dw-heading-slide">Seven jobs. One measurement.</h2>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:18px;align-items:start;">
                <div class="dw-flow dw-flow--vertical" style="margin-top:0;gap:8px;">
                    <div class="dw-pipeline-row" style="padding:10px 16px;font-size:18px;"><span class="dw-pipeline-label">1</span><span>LoadCorpus</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px;font-size:18px;"><span class="dw-pipeline-label">2</span><span>TokenizeBefore</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px;font-size:18px;"><span class="dw-pipeline-label">3</span><span>ApplyCandidates</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px;font-size:18px;"><span class="dw-pipeline-label">4</span><span>TokenizeAfter</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px;font-size:18px;"><span class="dw-pipeline-label">5</span><span>Compare</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px;font-size:18px;"><span class="dw-pipeline-label">6</span><span>EstimateCost</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px;font-size:18px;"><span class="dw-pipeline-label">7</span><span>Report</span></div>
                </div>
                <div class="nl-stack">
                    <div class="dw-chip">Symfony 8.1</div>
                    <div class="dw-chip">PHP 8.5.4</div>
                    <div class="dw-chip">darkwood/flow 8.1.6</div>
                    <div class="dw-chip">yethee/tiktoken 1.1.1</div>
                    <pre class="dw-code mt-2" style="font-size:15px;padding:16px;">php bin/console app:language:benchmark</pre>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>13 / implementation</span></footer>
    </x-slidewire::slide>

    {{-- 15 · WHAT I LEARNED --}}
    {{-- @notes
        Modest conclusions from this corpus. Measure the actual workload before optimizing it.
    --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What I learned</p>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:12px;">
                <div class="dw-chip" style="text-align:left;padding:16px 20px;">Natural languages are not tokenization-equivalent</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px;">Tokenizer choice changes the gap</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px;">Persuasion and token efficiency can conflict</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px;">Character count is not token count</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px;grid-column:1/-1;">Aggregates can hide opposing movements</div>
            </div>
            <p class="dw-takeaway mt-8">Measure the actual workload before you optimize it.</p>
        </section>
        <footer class="dw-footer"><span>@matyo91 · Darkwood</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
