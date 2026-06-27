{{-- Symfony 8.1 - conference deck (10–15 min) | /slides/symfony-8-1 --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $sf = '<span class="dw-accent">Symfony 8.1</span>';
    @endphp

    {{-- 1 · TITLE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · Symfony · 2026</p>
            <h1 class="dw-title">Symfony 8.1</h1>
            <p class="dw-lead">Architecture, async, and developer experience - what actually changes for production apps.</p>
            <div class="mt-12 dw-flow">
                <div class="dw-node">HTTP-less kernel</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Messenger ops</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">CLI = HTTP parity</div>
            </div>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · OVERVIEW --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Overview</p>
            <h2 class="dw-heading">{!! $sf !!} - a minor release, major operational leverage.</h2>
            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-chip">HTTP-less kernel</div>
                <div class="dw-chip">DeepCloner</div>
                <div class="dw-chip">DI for workers</div>
                <div class="dw-chip">Dynamic attributes</div>
                <div class="dw-chip">API layer</div>
                <div class="dw-chip">Messenger ops</div>
                <div class="dw-chip">JSON streaming</div>
                <div class="dw-chip">Console resolvers</div>
                <div class="dw-chip">Validator</div>
            </div>
        </section>
        <footer class="dw-footer"><span>01 / overview</span></footer>
    </x-slidewire::slide>

    {{-- 3 · WHY --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Why it matters</p>
            <h2 class="dw-heading">Two platforms, one framework - clearer boundaries, less boilerplate.</h2>
            <div class="mt-12 dw-grid dw-grid-2">
                <div class="dw-card">
                    <h3>Non-HTTP runtimes</h3>
                    <p>Workers and CLI boot a lean kernel without HttpKernel baggage.</p>
                </div>
                <div class="dw-card">
                    <h3>Production async</h3>
                    <p>Messenger throughput, decode recovery, and cross-service contracts.</p>
                </div>
            </div>
            <p class="dw-note">High leverage for long-running processes, APIs, and operational tooling.</p>
        </section>
        <footer class="dw-footer"><span>02 / why</span></footer>
    </x-slidewire::slide>

    {{-- 4–5 · HTTP-LESS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">HTTP-less applications</p>
            <h2 class="dw-heading">Kernel moves to DI - boot a container without HTTP.</h2>
            <p class="dw-lead"><code>AbstractKernel</code> + <code>KernelTrait</code> replace <code>MicroKernelTrait</code> for workers, consumers, and CLI-only apps.</p>
            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-chip">ServicesBundle</div>
                <div class="dw-chip">ConsoleBundle</div>
            </div>
        </section>
        <footer class="dw-footer"><span>03 / http-less</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">HTTP-less · architecture</p>
            <h2 class="dw-heading-slide">Entry points share DI - HTTP is optional.</h2>
            <div class="mt-6 dw-embed" style="height:min(400px,50vh)">
                <x-slidewire::diagram>
flowchart TB
  subgraph ep [Entry points]
    H[HTTP]
    C[Console]
    M[Messenger consumer]
  end
  K[AbstractKernel + KernelTrait]
  D[DI Container]
  H --> K
  C --> K
  M --> K
  K --> D
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>04 / http-less-arch</span></footer>
    </x-slidewire::slide>

    {{-- 6 · DEEP CLONER --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Backend · VarExporter</p>
            <h2 class="dw-heading"><span class="dw-accent">DeepCloner</span> replaces serialize/unserialize graphs.</h2>
            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-card"><h3>4× faster</h3><p>Typical object graphs; up to 15× on heavy property sets.</p></div>
                <div class="dw-card"><h3>Portable payloads</h3><p><code>toArray()</code> / <code>fromArray()</code> - ~30–40% smaller than serialize.</p></div>
                <div class="dw-card"><h3>Built-in usage</h3><p>Container compile, Form snapshots, ArrayAdapter cache.</p></div>
            </div>
            <pre class="mt-8 dw-code">$clone = (new DeepCloner($graph))->clone();
$payload = (new DeepCloner($graph))->toArray();</pre>
        </section>
        <footer class="dw-footer"><span>05 / deep-cloner</span></footer>
    </x-slidewire::slide>

    {{-- 7–8 · DI --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Dependency Injection</p>
            <h2 class="dw-heading">DI built for long-running workers.</h2>
            <div class="mt-10 dw-grid dw-grid-3">
                <x-slidewire::fragment :index="0">
                    <div class="dw-card"><h3>Env as Closure</h3><p>Refresh DB URLs and secrets via <code>resetEnvCache()</code> - no rebuild.</p></div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-card"><h3>Stack decorators</h3><p>Declarative <code>decorates</code> / <code>decorates_tag</code> - drop custom compiler passes.</p></div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="2">
                    <div class="dw-card"><h3>Explicit #[Target]</h3><p>Parameter-name alias matching deprecated - Symfony 9.0.</p></div>
                </x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>06 / di</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">DI · code</p>
            <h2 class="dw-heading-slide">Runtime env refresh in workers.</h2>
            <pre class="mt-8 dw-code">public function __construct(
    #[Autowire(env: 'DB_URL')] private \Closure $dbUrl,
    #[Target('image')] private StorageInterface $storage,
) {}</pre>
        </section>
        <footer class="dw-footer"><span>07 / di-code</span></footer>
    </x-slidewire::slide>

    {{-- 9 · DYNAMIC ATTRIBUTES --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Dynamic controller attributes</p>
            <h2 class="dw-heading">Controller attributes are mutable per request.</h2>
            <p class="dw-lead"><code>_controller_attributes</code> stored after first resolve - listeners override <code>#[Cache]</code>, <code>#[IsGranted]</code>, custom attrs.</p>
            <p class="dw-note">Dedicated events: <code>kernel.controller_arguments.{AttributeFQCN}</code></p>
        </section>
        <footer class="dw-footer"><span>08 / attributes</span></footer>
    </x-slidewire::slide>

    {{-- 10 · API LAYER --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">API layer</p>
            <h2 class="dw-heading">Input and output - less manual wiring.</h2>
            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-card">
                    <h3>#[MapRequestPayload]</h3>
                    <p>Multipart files in DTOs · variadic unpacking · dynamic validation groups.</p>
                </div>
                <div class="dw-card">
                    <h3>#[Serialize]</h3>
                    <p>Return objects - Symfony builds Response + Content-Type + context.</p>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>09 / api</span></footer>
    </x-slidewire::slide>

    {{-- 11–12 · MESSENGER --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Async · Messenger</p>
            <h2 class="dw-heading">Throughput knobs for production workers.</h2>
            <pre class="mt-8 dw-code">php bin/console messenger:consume async \
  --fetch-size=8 --no-reset=100</pre>
            <div class="mt-8 dw-grid dw-grid-2">
                <div class="dw-chip"><code>--fetch-size</code> - batch round-trips</div>
                <div class="dw-chip"><code>--no-reset</code> - balance state vs speed</div>
            </div>
        </section>
        <footer class="dw-footer"><span>10 / messenger-throughput</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Async · reliability</p>
            <h2 class="dw-heading-slide">Poison messages and polyglot consumers.</h2>
            <div class="mt-6 dw-embed" style="height:min(360px,46vh)">
                <x-slidewire::diagram>
flowchart LR
  Q[Transport] --> W[Worker]
  W -->|decode fail| R[Retry / failure pipeline]
  W -->|ok| H[Handler]
  M[serializedTypeName] -.->|header| Q
                </x-slidewire::diagram>
            </div>
            <p class="dw-note mt-6">Decode failures route through retry/failure transports - not silently dropped.</p>
        </section>
        <footer class="dw-footer"><span>11 / messenger-reliability</span></footer>
    </x-slidewire::slide>

    {{-- 13 · JSON --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Serialization · JSON</p>
            <h2 class="dw-heading">Stream and query large JSON without loading it all.</h2>
            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-card">
                    <h3>JsonStreamer</h3>
                    <p><code>ValueObjectTransformerInterface</code> - compact domain scalars · timezone-aware DateTime.</p>
                </div>
                <div class="dw-card">
                    <h3>JsonPath</h3>
                    <p><code>#[AsJsonPathFunction]</code> - domain filters without preprocessing pipelines.</p>
                </div>
            </div>
            <p class="dw-note">Relevant for APIs, log streams, RAG document stores.</p>
        </section>
        <footer class="dw-footer"><span>12 / json</span></footer>
    </x-slidewire::slide>

    {{-- 14–15 · CONSOLE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">CLI &amp; DX</p>
            <h2 class="dw-heading">Console gains HTTP-style argument resolvers.</h2>
            <pre class="mt-8 dw-code">public function __invoke(
    #[Argument, MapEntity] User $user,
    #[Option, MapDateTime(format: 'Y-m-d')] \DateTimeInterface $date,
    #[Autowire(service: 'messenger.bus.async')] MessageBusInterface $bus,
): int {}</pre>
        </section>
        <footer class="dw-footer"><span>13 / console-resolvers</span></footer>
    </x-slidewire::slide>

    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">CLI &amp; DX</p>
            <h2 class="dw-heading">Interactive CLI with validation parity.</h2>
            <div class="mt-10 dw-grid dw-grid-3">
                <x-slidewire::fragment :index="0">
                    <div class="dw-card"><h3>#[Ask] / #[AskChoice]</h3><p>Prompts + Validator constraints · image paste via <code>InputFile</code>.</p></div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="1">
                    <div class="dw-card"><h3>#[MapInput]</h3><p>DTO validation like <code>#[MapRequestPayload]</code>.</p></div>
                </x-slidewire::fragment>
                <x-slidewire::fragment :index="2">
                    <div class="dw-card"><h3>#[AsCommand] on methods</h3><p>Multiple commands per class · shared constructor deps.</p></div>
                </x-slidewire::fragment>
            </div>
        </section>
        <footer class="dw-footer"><span>14 / console-dx</span></footer>
    </x-slidewire::slide>

    {{-- 16 · WORKERS / AI --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Workers · AI · orchestration</p>
            <h2 class="dw-heading-slide">8.1 aligns the worker stack end-to-end.</h2>
            <div class="mt-6 dw-embed" style="height:min(420px,52vh)">
                <x-slidewire::diagram>
flowchart TB
  CLI[CLI commands\nresolvers + MapInput] --> BUS[Messenger bus]
  BUS --> W[HTTP-less consumer\nfetch-size · no-reset]
  W --> S[Services via DI\nenv Closure · DeepCloner]
  W --> OUT[JSON stream / polyglot type header]
  AI[AI / RAG pipelines] --> CLI
  AI --> W
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>15 / workers</span></footer>
    </x-slidewire::slide>

    {{-- VALIDATOR (before closing) --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Validator</p>
            <h2 class="dw-heading">Deterministic rules and safer nested validation.</h2>
            <ul class="mt-10 dw-list">
                <li><code>#[Assert\Xml]</code> - XSD validation with line-numbered violations</li>
                <li>Clock-aware date constraints - <code>MockClock</code> in tests</li>
                <li><code>validateInContext()</code> - reentrant validators (nested DTOs)</li>
                <li><code>enablePropertyMetadataExistenceCheck()</code> - catch property typos</li>
            </ul>
        </section>
        <footer class="dw-footer"><span>16 / validator</span></footer>
    </x-slidewire::slide>

    {{-- TOP IMPACTS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Most impactful</p>
            <h2 class="dw-heading">Five changes worth adopting first.</h2>
            <div class="mt-10 dw-map">
                <div><strong>01</strong><span>HTTP-less kernel for workers</span></div>
                <div><strong>02</strong><span>Messenger fetch-size &amp; decode recovery</span></div>
                <div><strong>03</strong><span>Console argument resolvers</span></div>
                <div><strong>04</strong><span>Env vars as Closure in DI</span></div>
                <div><strong>05</strong><span>#[Serialize] + payload mapping gaps closed</span></div>
            </div>
        </section>
        <footer class="dw-footer"><span>17 / impact</span></footer>
    </x-slidewire::slide>

    {{-- IN PRACTICE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Takeaway</p>
            <h2 class="dw-heading">Symfony 8.1 in practice</h2>
            <div class="mt-12 dw-flow">
                <div class="dw-node">Lean workers</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Reliable async</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">CLI = first-class</div>
            </div>
            <p class="dw-lead">Upgrade path is incremental - adopt per entry point, not big-bang.</p>
        </section>
        <footer class="dw-footer"><span>18 / practice</span></footer>
    </x-slidewire::slide>

    {{-- CONCLUSION --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Conclusion</p>
            <h2 class="dw-heading">{!! $sf !!} sharpens boundaries - HTTP, CLI, and async each get the right kernel.</h2>
            <p class="dw-lead">Less boilerplate. Faster compiles. Production Messenger fixes. Same Symfony mental model.</p>
        </section>
        <footer class="dw-footer"><span>19 / conclusion</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- REFERENCES --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap" style="text-align:center">
            <p class="dw-kicker">References · Darkwood</p>
            <h2 class="dw-heading-slide">Links &amp; resources</h2>
            <div class="mt-10 dw-grid dw-grid-2" style="text-align:left">
                <div class="dw-card">
                    <h3>Symfony</h3>
                    <ul class="dw-list-compact">
                        <li>symfony.com/blog - 8.1 release</li>
                        <li>github.com/symfony/symfony</li>
                        <li>symfony.com/doc/current/setup/upgrade_minor.html</li>
                    </ul>
                </div>
                <div class="dw-card">
                    <h3>Darkwood</h3>
                    <ul class="dw-list-compact">
                        <li>darkwood.com</li>
                        <li>github.com/darkwood-com</li>
                        <li>@matyo91</li>
                    </ul>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>20 / merci</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
