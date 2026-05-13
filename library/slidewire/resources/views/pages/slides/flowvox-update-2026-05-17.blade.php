{{-- Flowvox update 2026-05-17 — Voice-first realtime transcription on Symfony 8 --}}
{{-- Route: /slides/flowvox-update-2026-05-17 | Blog + YouTube + live demo --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $accent = '<span class="dw-accent">Flowvox</span>';
        $symfony = '<span class="dw-accent">Symfony</span>';
    @endphp

    {{-- ═══ ACT 0 — OUVERTURE ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · Symfony 8 · Voice-first · 2026-05-17</p>
            <h1 class="dw-title">Flowvox update</h1>
            <p class="dw-lead">De la console au worker vocal temps réel — web, Mercure, iOS.</p>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Parler</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Transcrire</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Diffuser</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Thèse</p>
            <h2 class="dw-heading">{!! $symfony !!} peut devenir une <span class="dw-accent">plateforme d'agents vocaux</span> temps réel.</h2>
            <p class="dw-lead">Pas un chatbot. Un système : workers, providers, UI live, mobile natif.</p>
        </section>
        <footer class="dw-footer"><span>01 / thèse</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Roadmap narrative</p>
            <h2 class="dw-heading">Six phases. Une évolution visible.</h2>
            <div class="mt-10 dw-grid dw-grid-3" style="grid-template-columns:repeat(3,1fr)">
                <div class="dw-chip">① Console POC</div>
                <div class="dw-chip">② Workers Flow</div>
                <div class="dw-chip">③ Dashboard Mercure</div>
                <div class="dw-chip">④ OpenAI Realtime</div>
                <div class="dw-chip">⑤ UX Native iOS</div>
                <div class="dw-chip">⑥ Voice agents</div>
            </div>
        </section>
        <footer class="dw-footer"><span>02 / roadmap</span></footer>
    </x-slidewire::slide>

    {{-- ═══ PHASE 1 — POC CONSOLE ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Phase 1 · 2024</p>
            <h2 class="dw-heading">POC console.<br><span class="dw-accent">whisper.cpp</span> sur Symfony.</h2>
            <p class="dw-lead">Enregistrer. Transcrire. Lire le texte dans le terminal.</p>
        </section>
        <footer class="dw-footer"><span>03 / phase 1</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Phase 1</p>
            <h2 class="dw-heading-slide">Pipeline minimal</h2>
            <div class="mt-6 dw-embed" style="height:min(380px,48vh)">
                <x-slidewire::diagram>
flowchart LR
  MIC[Micro] --> FFM[ffmpeg WAV]
  FFM --> CLI[whisper-cli]
  CLI --> OUT[stdout]
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>04 / poc pipeline</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Limite du POC</p>
            <h2 class="dw-heading">Ça marche.<br>Mais ce n'est pas un produit.</h2>
            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Pas de UI</h3><p>Tout dans le terminal.</p></div>
                <div class="dw-card"><h3>Pas de live</h3><p>Texte après STOP seulement.</p></div>
                <div class="dw-card"><h3>Pas de scale</h3><p>Une session, un process.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>05 / limite</span></footer>
    </x-slidewire::slide>

    {{-- ═══ PHASE 2 — WORKERS ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Phase 2</p>
            <h2 class="dw-heading">Workers distribués.<br><span class="dw-accent">Flow</span> + Messenger.</h2>
            <p class="dw-lead">START / STOP par session. Un worker long-running par <code>sessionId</code>.</p>
        </section>
        <footer class="dw-footer"><span>06 / phase 2</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Pourquoi Flow ?</p>
            <h2 class="dw-heading-slide">Orchestration fiber, pas spaghetti async.</h2>
            <div class="mt-6 dw-embed" style="height:min(400px,50vh)">
                <x-slidewire::diagram>
flowchart TB
  MSG[Messenger START/STOP] --> IN[InputProviderFlow]
  IN --> REC[RecorderFlow]
  REC --> TR[TranscribeFlow]
  TR --> DSP[DisplayFlow]
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>07 / flow</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Pourquoi Symfony ?</p>
            <h2 class="dw-heading">Le worker n'est pas l'UI.</h2>
            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Messenger</h3><p>Contrôle asynchrone ciblé.</p></div>
                <div class="dw-card"><h3>Doctrine</h3><p>Sessions, historique, export.</p></div>
                <div class="dw-card"><h3>Ports</h3><p>Mercure, providers, use cases.</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>08 / symfony</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Multi-session</p>
            <h2 class="dw-heading">alpha · beta · demo</h2>
            <pre class="mt-8 dw-code">php bin/console voice:worker --session=demo
php bin/console voice:start --session=demo
php bin/console voice:stop --session=demo</pre>
        </section>
        <footer class="dw-footer"><span>09 / cli</span></footer>
    </x-slidewire::slide>

    {{-- ═══ PHASE 3 — MERCURE ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Phase 3</p>
            <h2 class="dw-heading">Dashboard temps réel.<br><span class="dw-accent">Mercure</span> + Turbo.</h2>
            <p class="dw-lead">L'UI observe. Le worker exécute.</p>
        </section>
        <footer class="dw-footer"><span>10 / phase 3</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Architecture</p>
            <h2 class="dw-heading-slide">Publish first, persist after.</h2>
            <div class="mt-4 dw-embed" style="height:min(420px,52vh)">
                <x-slidewire::diagram>
flowchart LR
  W[Worker] --> E[VoiceDomainEvent]
  E --> M[Mercure]
  E --> DB[(PostgreSQL)]
  M --> WEB[Browser Turbo]
  M --> iOS[Hotwire Native]
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>11 / mercure arch</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Événements</p>
            <h2 class="dw-heading">Le contrat live.</h2>
            <div class="mt-8 dw-flow" style="flex-wrap:wrap;gap:8px">
                <div class="dw-chip">recording_started</div>
                <div class="dw-chip">transcription_partial</div>
                <div class="dw-chip">transcription_final</div>
                <div class="dw-chip">error</div>
            </div>
        </section>
        <footer class="dw-footer"><span>12 / events</span></footer>
    </x-slidewire::slide>

    {{-- ═══ PROVIDERS ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Architecture providers</p>
            <h2 class="dw-heading">Un pipeline.<br>Plusieurs moteurs.</h2>
            <div class="mt-8 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Local</h3><p>whisper_cpp · whisper_cpp_stream</p></div>
                <div class="dw-card"><h3>Cloud batch</h3><p>openai_batch · gpt-4o-transcribe</p></div>
                <div class="dw-card"><h3>Cloud live</h3><p>openai_realtime_whisper</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>13 / providers</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Providers</p>
            <h2 class="dw-heading-slide">Registre plug-in</h2>
            <div class="mt-4 dw-embed" style="height:min(360px,45vh)">
                <x-slidewire::diagram>
flowchart TB
  ENV[FLOWVOX_TRANSCRIPTION_PROVIDER] --> REG[TranscriptionProviderRegistry]
  REG --> W[WhisperCpp]
  REG --> WS[WhisperCppStream]
  REG --> B[OpenAiBatch]
  REG --> R[OpenAiRealtime]
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>14 / registry</span></footer>
    </x-slidewire::slide>

    {{-- ═══ PHASE 4 — OPENAI REALTIME ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Phase 4</p>
            <h2 class="dw-heading">OpenAI Realtime API.<br>Le texte <span class="dw-accent">pendant</span> qu'on parle.</h2>
        </section>
        <footer class="dw-footer"><span>15 / phase 4</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Realtime audio pipeline</p>
            <h2 class="dw-heading-slide">PCM 24 kHz → WebSocket → partials</h2>
            <div class="mt-4 dw-embed" style="height:min(400px,50vh)">
                <x-slidewire::diagram>
sequenceDiagram
  participant W as Worker
  participant F as ffmpeg
  participant O as OpenAI Realtime
  participant M as Mercure
  W->>F: PCM stream
  F->>O: input_audio_buffer.append
  O-->>W: transcription_partial
  W->>M: publish
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>16 / realtime pipeline</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">whisper-stream local</p>
            <h2 class="dw-heading">Même UX.<br>Moteur différent.</h2>
            <div class="mt-8 dw-grid dw-grid-2" style="grid-template-columns:1fr 1fr">
                <div class="dw-card"><h3>Privé</h3><p>SDL2 + whisper-stream · fenêtre glissante</p></div>
                <div class="dw-card"><h3>Cloud</h3><p>VAD OpenAI · gpt-4o-transcribe</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>17 / local vs cloud</span></footer>
    </x-slidewire::slide>

    {{-- ═══ DEMO SLOT ═══ --}}
    <x-slidewire::slide class="dw-slide" data-background-color="#0a0f14">
        <section class="dw-wrap" style="text-align:center">
            <p class="dw-kicker">Live demo</p>
            <h2 class="dw-title" style="font-size:clamp(2.5rem,6vw,4.5rem)">DEMO</h2>
            <p class="dw-lead">worker · START · parler · STOP · Mercure</p>
        </section>
        <footer class="dw-footer"><span>18 / demo</span></footer>
    </x-slidewire::slide>

    {{-- ═══ PHASE 5 — iOS ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Phase 5</p>
            <h2 class="dw-heading">UX Native + <span class="dw-accent">Hotwire</span> iOS.</h2>
            <p class="dw-lead">Une codebase Twig. Une expérience native.</p>
        </section>
        <footer class="dw-footer"><span>19 / phase 5</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Hotwire Native</p>
            <h2 class="dw-heading">WKWebView + bridges.</h2>
            <div class="mt-10 dw-flow">
                <div class="dw-node">Twig</div>
                <div class="dw-arrow">↔</div>
                <div class="dw-node">Stimulus Bridge</div>
                <div class="dw-arrow">↔</div>
                <div class="dw-node">Swift UI</div>
            </div>
        </section>
        <footer class="dw-footer"><span>20 / hotwire</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">ux_is_native()</p>
            <h2 class="dw-heading">Même template.<br>Deux expériences.</h2>
            <pre class="mt-8 dw-code">{% verbatim %}{% if not ux_is_native() %}
  <nav>…</nav>
{% endif %}{% endverbatim %}</pre>
        </section>
        <footer class="dw-footer"><span>21 / native twig</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">iOS validé</p>
            <h2 class="dw-heading">iPhone physique · LAN · Mercure JWT.</h2>
            <div class="mt-8 dw-grid dw-grid-3">
                <div class="dw-chip">flowvox-ios</div>
                <div class="dw-chip">192.168.x.x</div>
                <div class="dw-chip">Live transcript</div>
            </div>
        </section>
        <footer class="dw-footer"><span>22 / ios</span></footer>
    </x-slidewire::slide>

    {{-- ═══ NAVI ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">darkwood/navi</p>
            <h2 class="dw-heading">Trace structurée.<br>Workflows demain.</h2>
            <p class="dw-lead">NDJSON · Actions · Context — base pour assistants programmables.</p>
        </section>
        <footer class="dw-footer"><span>23 / navi</span></footer>
    </x-slidewire::slide>

    {{-- ═══ PHASE 6 — FUTURE ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Phase 6 · Horizon</p>
            <h2 class="dw-heading">Voice-first interface.</h2>
            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-card"><h3>Translate</h3><p>Realtime multilingue</p></div>
                <div class="dw-card"><h3>Agents</h3><p>Tool calling vocal</p></div>
                <div class="dw-card"><h3>Navi</h3><p>Workflows déclaratifs</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>24 / future</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Stack cible</p>
            <h2 class="dw-heading-slide">Flowvox dans l'écosystème Darkwood</h2>
            <div class="mt-4 dw-embed" style="height:min(380px,48vh)">
                <x-slidewire::diagram>
flowchart TB
  FV[Flowvox] --> FLOW[darkwood/flow]
  FV --> NAVI[darkwood/navi]
  FV --> SAI[Symfony AI]
  FV --> UF[Uniflow]
  SAI --> RT[OpenAI Realtime]
  NAVI --> WF[Voice workflows]
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>25 / stack</span></footer>
    </x-slidewire::slide>

    {{-- ═══ VISION ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Vision produit</p>
            <h2 class="dw-heading">Parler à son application.<br>Elle répond en temps réel.</h2>
            <p class="dw-lead">Dictée · réunions · accessibilité · agents vocaux métier.</p>
        </section>
        <footer class="dw-footer"><span>26 / vision</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">État d'avancement</p>
            <h2 class="dw-heading">MVP bout en bout.</h2>
            <div class="mt-8 dw-grid dw-grid-3">
                <div class="dw-card"><h3>~90%</h3><p>Workers + Flow</p></div>
                <div class="dw-card"><h3>~85%</h3><p>UI + Mercure</p></div>
                <div class="dw-card"><h3>~70%</h3><p>iOS native</p></div>
            </div>
        </section>
        <footer class="dw-footer"><span>27 / status</span></footer>
    </x-slidewire::slide>

    {{-- ═══ CONCLUSION ═══ --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Conclusion</p>
            <h2 class="dw-heading">{!! $accent !!} = plateforme vocale Symfony.</h2>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Workers</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Providers</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Live UI</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Native</div>
            </div>
        </section>
        <footer class="dw-footer"><span>28 / conclusion</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap" style="text-align:center">
            <p class="dw-kicker">Darkwood · Symfony · Uniflow</p>
            <h2 class="dw-heading">github.com/darkwood-com/flowvox</h2>
            <p class="dw-lead mt-8">Article · YouTube · Démo live</p>
            <p class="dw-lead">darkwood.com · Symfony AI · Flow · Navi</p>
        </section>
        <footer class="dw-footer"><span>29 / merci</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
