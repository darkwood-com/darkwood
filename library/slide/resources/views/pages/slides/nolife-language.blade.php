<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="cover" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--cover',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-title dw-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="cover" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Darkwood · Symfony · Flow · language
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Nolife Language
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Don't say cost. Say <span class="dw-accent">investment</span>.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<style>
    .nl-note { margin-top: 16px; }
    .nl-kpi { line-height: .9; font-weight: 700; letter-spacing: -0.03em; }
    .nl-kpi-sub { margin-top: 18px; }
    .nl-word { font-weight: 700; line-height: .95; letter-spacing: -0.02em; }
    .nl-swap { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }
    .nl-bar-row { display: flex; align-items: center; gap: 14px; margin: 12px 0; }
    .nl-bar-label { width: 92px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .nl-bar-track { flex: 1; height: 22px; border-radius: 4px; overflow: hidden; border: var(--dw-border-width) solid var(--dw-line); }
    .nl-bar-fill { height: 100%; border-radius: 3px; }
    .nl-bar-val { width: 56px; text-align: right; font-weight: 700; }
    .nl-bar-delta { width: 110px; font-weight: 700; }
    .nl-pair { display: grid; grid-template-columns: 1fr auto 1fr; gap: 18px; align-items: center; }
    .nl-pair .dw-card { text-align: center; padding: 22px 18px; }
    .nl-pair .nl-count { display: block; margin-top: 8px; font-weight: 700; }
    .nl-table { width: 100%; border-collapse: collapse; }
    .nl-table th, .nl-table td { padding: 10px 12px; text-align: right; border-bottom: var(--dw-border-width) solid var(--dw-line); }
    .nl-table th:first-child, .nl-table td:first-child,
    .nl-table th:nth-child(2), .nl-table td:nth-child(2) { text-align: left; }
    .nl-table th { letter-spacing: .06em; text-transform: uppercase; font-weight: 700; }
    .nl-triangle { position: relative; height: 340px; margin: 12px auto 0; max-width: 760px; }
    .nl-vertex { position: absolute; min-width: 210px; padding: 18px 22px; text-align: center; font-weight: 700; }
    .nl-vertex--top { left: 50%; top: 0; transform: translateX(-50%); }
    .nl-vertex--left { left: 0; bottom: 0; }
    .nl-vertex--right { right: 0; bottom: 0; }
    .nl-tri-line { position: absolute; opacity: .55; }
    .nl-tri-line--v { width: 2px; height: 118px; left: 50%; top: 78px; transform: translateX(-50%); }
    .nl-tri-line--h { height: 2px; width: calc(100% - 240px); left: 120px; bottom: 48px; }
    .nl-corner { padding: 18px 20px; min-height: 168px; }
    .nl-corner h3 { letter-spacing: .08em; text-transform: uppercase; margin: 0 0 12px; }
    .nl-corner p { margin: 0; font-weight: 700; line-height: 1.25; }
    .nl-corner .nl-delta { display: block; margin-top: 10px; }
    .nl-neq { font-weight: 700; text-align: center; }
    .nl-stack { display: grid; gap: 10px; }
</style>
@endverbatim
@verbatim
<section class="dw-wrap">



            <p class="mt-8">When words change both persuasion and token cost.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Words change the frame</p>
            <div class="nl-swap" style="margin-top:12px">
                <div class="nl-word">COST</div>
                <div class="dw-arrow">↓</div>
                <div class="nl-word">INVESTMENT</div>
            </div>
            <div class="mt-10 dw-grid dw-grid-3" style="gap:10px">
                <div class="dw-chip">basic → essential</div>
                <div class="dw-chip">standard → customized</div>
                <div class="dw-chip">few → limited</div>
            </div>
            <p class="dw-takeaway mt-8">Same offer. Different frame.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | frame" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Then the tokenizer reads it
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Humans hear meaning.<br>Tokenizers count <span class="dw-accent">pieces</span>.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-grid dw-grid-3" style="gap:14px">
                <div class="dw-card" style="text-align:center">
                    <p class="nl-neq">words ≠ tokens</p>
                </div>
                <div class="dw-card" style="text-align:center">
                    <p class="nl-neq">chars ≠ tokens</p>
                </div>
                <div class="dw-card" style="text-align:center">
                    <p class="nl-neq">encoding matters</p>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | tokenizer" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Three axes</p>
            <div class="dw-flow dw-flow--vertical" style="max-width:720px;margin:20px auto 0;align-items:center">
                <div class="dw-node" style="min-height:auto;padding:18px 28px;width:280px">meaning</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-flow" style="margin-top:0;width:100%">
                    <div class="dw-node" style="min-height:auto;padding:18px 22px">token cost</div>
                    <div class="dw-arrow">+</div>
                    <div class="dw-node" style="min-height:auto;padding:18px 22px">persuasion</div>
                </div>
            </div>
            <p class="dw-takeaway mt-8">Improving one axis can hurt another.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | triangle" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
o200k_base · parallel corpus
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Same idea. Three languages.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-3" style="gap:14px">
                <div class="dw-card" style="text-align:center;padding:22px 16px">
                    <h3>English</h3>
                    <p style="font-weight:700;line-height:.9;margin-top:8px">46</p>
                    <p style="margin-top:8px">baseline</p>
                    <div style="margin-top:14px;height:8px;border-radius:4px">
                        <div style="height:8px;width:73%;border-radius:4px"></div>
                    </div>
                </div>
                <div class="dw-card" style="text-align:center;padding:22px 16px">
                    <h3>French</h3>
                    <p style="font-weight:700;line-height:.9;margin-top:8px">58</p>
                    <p style="margin-top:8px;font-weight:700">+26.09%</p>
                    <div style="margin-top:14px;height:8px;border-radius:4px">
                        <div style="height:8px;width:92.1%;border-radius:4px"></div>
                    </div>
                </div>
                <div class="dw-card" style="text-align:center;padding:22px 16px">
                    <h3>German</h3>
                    <p style="font-weight:700;line-height:.9;margin-top:8px">63</p>
                    <p style="margin-top:8px;font-weight:700">+36.96%</p>
                    <div style="margin-top:14px;height:8px;border-radius:4px">
                        <div style="height:8px;width:100%;border-radius:4px"></div>
                    </div>
                </div>
            </div>
            <p class="nl-note" style="text-align:center">this corpus × this tokenizer — not a universal language tax</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | languages" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Same texts · two encodings
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Language cost is not a constant.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <table class="nl-table mt-6">
                <thead>
                    <tr>
                        <th></th>
                        <th>o200k_base</th>
                        <th>cl100k_base</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>English</td><td>46</td><td>46</td></tr>
                    <tr><td>French</td><td>58</td><td>71</td></tr>
                    <tr><td>German</td><td>63</td><td>73</td></tr>
                </tbody>
            </table>
            <div class="mt-6 dw-grid dw-grid-2" style="gap:12px">
                <div class="dw-chip">FR +26.09% → <span>+54.35%</span></div>
                <div class="dw-chip">DE +36.96% → <span>+58.70%</span></div>
            </div>
            <p class="nl-note" style="text-align:center">corpus × tokenizer × language</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | encoding" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">o200k_base · German persuasion</p>
            <div class="dw-flow" style="margin-top:18px">
                <div class="dw-card" style="flex:1;text-align:center">
                    <p class="nl-word">Standard</p>
                    <span class="nl-count">1 token</span>
                </div>
                <div class="dw-arrow">→</div>
                <div class="dw-card" style="flex:1;text-align:center">
                    <p class="nl-word">maßgeschneidert</p>
                    <span class="nl-count">5 tokens</span>
                </div>
            </div>
            <p style="text-align:center;font-weight:700;margin-top:12px">+400%</p>
            <p class="mt-6" style="text-align:center">Basis 1 → unverzichtbar 4</p>
            <p class="dw-takeaway mt-6">Better sales framing can be worse BPE.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | regression" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Four corners of the experiment
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A phrase can win one axis and lose another.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-grid dw-grid-2" style="gap:12px">
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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | corners" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Regressions
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="nl-red">9 / 27</span> rewrites got more expensive
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <p class="mt-2">all 9 were persuasion · 0 were token_opt</p>
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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | red-rows" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Mixed pair aggregate</p>
            <p class="nl-kpi" style="text-align:center">62 <span class="dw-accent">→</span> 62</p>
            <p class="nl-kpi-sub" style="text-align:center">Zero change in the total. Huge change underneath.</p>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:14px">
                <div class="dw-card" style="text-align:center">
                    <h3>persuasion</h3>
                    <p class="nl-mono" style="font-weight:700;margin-top:8px">36 → 45</p>
                    <p class="nl-red" style="margin-top:4px">+9 tokens</p>
                </div>
                <div class="dw-card" style="text-align:center">
                    <h3>token_opt</h3>
                    <p class="nl-mono" style="font-weight:700;margin-top:8px">26 → 17</p>
                    <p class="nl-lime" style="margin-top:4px">−9 tokens</p>
                </div>
            </div>
            <p class="dw-takeaway mt-6">Cancellation ≠ neutrality.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | cancel" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
ESTIMATED · 1,000,000 requests · pair tokens
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The money is tiny. The structure is not.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-3" style="gap:12px">
                <div class="dw-card" style="text-align:center">
                    <h3>persuasion</h3>
                    <p class="nl-mono">input −$1.35</p>
                    <p class="nl-mono">output −$5.40</p>
                </div>
                <div class="dw-card" style="text-align:center">
                    <h3>token_opt</h3>
                    <p class="nl-mono">input +$1.35</p>
                    <p class="nl-mono">output +$5.40</p>
                </div>
                <div class="dw-card" style="text-align:center">
                    <h3>mixed</h3>
                    <p class="nl-kpi" style="margin-top:8px">$0.00</p>
                </div>
            </div>
            <p class="nl-note">tiny corpus · dated gpt-4o-mini snapshot · not a provider invoice</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | cost" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
100,000-token hypothetical window · o200k_base
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Same window. Less language.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-grid dw-grid-3" style="gap:14px">
                <div class="dw-card" style="text-align:center;padding:22px 14px">
                    <h3>English</h3>
                    <p style="font-weight:700;margin-top:8px">2,173.91</p>
                    <p style="margin-top:6px">copies</p>
                    <div style="margin-top:14px;height:8px;border-radius:4px">
                        <div style="height:8px;width:100%;border-radius:4px"></div>
                    </div>
                </div>
                <div class="dw-card" style="text-align:center;padding:22px 14px">
                    <h3>French</h3>
                    <p style="font-weight:700;margin-top:8px">1,724.14</p>
                    <p style="margin-top:6px;font-weight:700">−20.69%</p>
                    <div style="margin-top:14px;height:8px;border-radius:4px">
                        <div style="height:8px;width:79.3%;border-radius:4px"></div>
                    </div>
                </div>
                <div class="dw-card" style="text-align:center;padding:22px 14px">
                    <h3>German</h3>
                    <p style="font-weight:700;margin-top:8px">1,587.30</p>
                    <p style="margin-top:6px;font-weight:700">−26.98%</p>
                    <div style="margin-top:14px;height:8px;border-radius:4px">
                        <div style="height:8px;width:73%;border-radius:4px"></div>
                    </div>
                </div>
            </div>
            <p class="dw-takeaway mt-8">The model does not get a smaller context window.<br>The same token budget fits less of this text.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | context" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Caveman · method, not a rerun
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Compression has overhead too.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <p class="nl-neq mt-8">shorter output  ≠  lower total cost</p>
            <div class="mt-10 dw-grid dw-grid-3" style="gap:12px">
                <div class="dw-chip">publish regressions</div>
                <div class="dw-chip">compare against a control</div>
                <div class="dw-chip">output ≠ invoice</div>
            </div>
            <p class="nl-note" style="text-align:center">Inspected as methodology. Not a second benchmark on this corpus.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | caveman" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
content/nolife-language
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Seven jobs. One measurement.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-4 dw-grid dw-grid-2" style="gap:18px;align-items:start">
                <div class="dw-flow dw-flow--vertical" style="margin-top:0;gap:8px">
                    <div class="dw-pipeline-row" style="padding:10px 16px"><span class="dw-pipeline-label">1</span><span>LoadCorpus</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px"><span class="dw-pipeline-label">2</span><span>TokenizeBefore</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px"><span class="dw-pipeline-label">3</span><span>ApplyCandidates</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px"><span class="dw-pipeline-label">4</span><span>TokenizeAfter</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px"><span class="dw-pipeline-label">5</span><span>Compare</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px"><span class="dw-pipeline-label">6</span><span>EstimateCost</span></div>
                    <div class="dw-pipeline-row" style="padding:10px 16px"><span class="dw-pipeline-label">7</span><span>Report</span></div>
                </div>
                <div class="nl-stack">
                    <div class="dw-chip">Symfony 8.1</div>
                    <div class="dw-chip">PHP 8.5.4</div>
                    <div class="dw-chip">darkwood/flow 8.1.6</div>
                    <div class="dw-chip">yethee/tiktoken 1.1.1</div>

@endverbatim
@php
$snippet = <<<'SW_CODE_13_0'
php bin/console app:language:benchmark
SW_CODE_13_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | implementation" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What I learned</p>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:12px">
                <div class="dw-chip" style="text-align:left;padding:16px 20px">Natural languages are not tokenization-equivalent</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px">Tokenizer choice changes the gap</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px">Persuasion and token efficiency can conflict</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px">Character count is not token count</div>
                <div class="dw-chip" style="text-align:left;padding:16px 20px;grid-column:1/-1">Aggregates can hide opposing movements</div>
            </div>
            <p class="dw-takeaway mt-8">Measure the actual workload before you optimize it.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="@matyo91 · Darkwood" />
</x-darkwood.slide>
</x-darkwood.deck>
