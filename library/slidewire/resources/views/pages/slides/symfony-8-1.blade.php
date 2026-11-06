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
Darkwood · Symfony · 2026
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Symfony 8.1
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Architecture, async, and developer experience - what actually changes for production apps.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-12 dw-flow">
                <div class="dw-node">HTTP-less kernel</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">Messenger ops</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">CLI = HTTP parity</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Overview
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">Symfony 8.1</span> - a minor release, major operational leverage.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | overview" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Why it matters
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Two platforms, one framework - clearer boundaries, less boilerplate.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | why" />
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
  'class' => 'sw-heading dw-heading',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
HTTP-less applications
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Kernel moves to DI - boot a container without HTTP.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
<code>AbstractKernel</code> + <code>KernelTrait</code> replace <code>MicroKernelTrait</code> for workers, consumers, and CLI-only apps.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-grid dw-grid-2">
                <div class="dw-chip">ServicesBundle</div>
                <div class="dw-chip">ConsoleBundle</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | http-less" />
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
HTTP-less · architecture
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Entry points share DI - HTTP is optional.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  subgraph ep [Entry points]
    H[HTTP]
    C[Console]
    M[Messenger consumer]
  end
  K[AbstractKernel + KernelTrait]
  D[DI Container]
  H --&gt; K
  C --&gt; K
  M --&gt; K
  K --&gt; D</pre>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | http-less-arch" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Backend · VarExporter
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">DeepCloner</span> replaces serialize/unserialize graphs.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-grid dw-grid-3">
                <div class="dw-card"><h3>4× faster</h3><p>Typical object graphs; up to 15× on heavy property sets.</p></div>
                <div class="dw-card"><h3>Portable payloads</h3><p><code>toArray()</code> / <code>fromArray()</code> - ~30–40% smaller than serialize.</p></div>
                <div class="dw-card"><h3>Built-in usage</h3><p>Container compile, Form snapshots, ArrayAdapter cache.</p></div>
            </div>

@endverbatim
@php
$snippet = <<<'SW_CODE_5_0'
$clone = (new DeepCloner($graph))->clone();
$payload = (new DeepCloner($graph))->toArray();
SW_CODE_5_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | deep-cloner" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Dependency Injection
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
DI built for long-running workers.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-grid dw-grid-3">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>Env as Closure</h3><p>Refresh DB URLs and secrets via <code>resetEnvCache()</code> - no rebuild.</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>Stack decorators</h3><p>Declarative <code>decorates</code> / <code>decorates_tag</code> - drop custom compiler passes.</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>Explicit #[Target]</h3><p>Parameter-name alias matching deprecated - Symfony 9.0.</p></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | di" />
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
DI · code
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Runtime env refresh in workers.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



@endverbatim
@php
$snippet = <<<'SW_CODE_7_0'
public function __construct(
    #[Autowire(env: 'DB_URL')] private \Closure $dbUrl,
    #[Target('image')] private StorageInterface $storage,
) {}
SW_CODE_7_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | di-code" />
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
  'class' => 'sw-heading dw-heading',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Dynamic controller attributes
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Controller attributes are mutable per request.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
<code>_controller_attributes</code> stored after first resolve - listeners override <code>#[Cache]</code>, <code>#[IsGranted]</code>, custom attrs.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <p class="dw-note">Dedicated events: <code>kernel.controller_arguments.{AttributeFQCN}</code></p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | attributes" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
API layer
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Input and output - less manual wiring.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | api" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Async · Messenger
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Throughput knobs for production workers.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



@endverbatim
@php
$snippet = <<<'SW_CODE_10_0'
php bin/console messenger:consume async \
  --fetch-size=8 --no-reset=100
SW_CODE_10_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-8 dw-grid dw-grid-2">
                <div class="dw-chip"><code>--fetch-size</code> - batch round-trips</div>
                <div class="dw-chip"><code>--no-reset</code> - balance state vs speed</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | messenger-throughput" />
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
Async · reliability
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Poison messages and polyglot consumers.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart LR
  Q[Transport] --&gt; W[Worker]
  W --&gt;|decode fail| R[Retry / failure pipeline]
  W --&gt;|ok| H[Handler]
  M[serializedTypeName] -.-&gt;|header| Q</pre>
            </div>
            <p class="dw-note mt-6">Decode failures route through retry/failure transports - not silently dropped.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | messenger-reliability" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Serialization · JSON
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Stream and query large JSON without loading it all.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | json" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
CLI &amp; DX
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Console gains HTTP-style argument resolvers.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



@endverbatim
@php
$snippet = <<<'SW_CODE_13_0'
public function __invoke(
    #[Argument, MapEntity] User $user,
    #[Option, MapDateTime(format: 'Y-m-d')] \DateTimeInterface $date,
    #[Autowire(service: 'messenger.bus.async')] MessageBusInterface $bus,
): int {}
SW_CODE_13_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | console-resolvers" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
CLI &amp; DX
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Interactive CLI with validation parity.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-grid dw-grid-3">
                <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="dw-card"><h3>#[Ask] / #[AskChoice]</h3><p>Prompts + Validator constraints · image paste via <code>InputFile</code>.</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <div class="dw-card"><h3>#[MapInput]</h3><p>DTO validation like <code>#[MapRequestPayload]</code>.</p></div>
</div>
                <div class="slidewire-fragment" data-fragment-index="2" data-fragment="data-fragment">
    <div class="dw-card"><h3>#[AsCommand] on methods</h3><p>Multiple commands per class · shared constructor deps.</p></div>
</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | console-dx" />
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
Workers · AI · orchestration
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
8.1 aligns the worker stack end-to-end.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-6 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  CLI[CLI commands\nresolvers + MapInput] --&gt; BUS[Messenger bus]
  BUS --&gt; W[HTTP-less consumer\nfetch-size · no-reset]
  W --&gt; S[Services via DI\nenv Closure · DeepCloner]
  W --&gt; OUT[JSON stream / polyglot type header]
  AI[AI / RAG pipelines] --&gt; CLI
  AI --&gt; W</pre>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="15 | workers" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Validator
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Deterministic rules and safer nested validation.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <ul class="mt-10 dw-list">
                <li><code>#[Assert\Xml]</code> - XSD validation with line-numbered violations</li>
                <li>Clock-aware date constraints - <code>MockClock</code> in tests</li>
                <li><code>validateInContext()</code> - reentrant validators (nested DTOs)</li>
                <li><code>enablePropertyMetadataExistenceCheck()</code> - catch property typos</li>
            </ul>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="16 | validator" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Most impactful
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Five changes worth adopting first.
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-10 dw-map">
                <div><strong>01</strong><span>HTTP-less kernel for workers</span></div>
                <div><strong>02</strong><span>Messenger fetch-size &amp; decode recovery</span></div>
                <div><strong>03</strong><span>Console argument resolvers</span></div>
                <div><strong>04</strong><span>Env vars as Closure in DI</span></div>
                <div><strong>05</strong><span>#[Serialize] + payload mapping gaps closed</span></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="17 | impact" />
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
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Takeaway
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Symfony 8.1 in practice
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-12 dw-flow">
                <div class="dw-node">Lean workers</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Reliable async</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">CLI = first-class</div>
            </div>
            <p class="dw-lead">Upgrade path is incremental - adopt per entry point, not big-bang.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="18 | practice" />
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
  'class' => 'sw-heading dw-heading',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Conclusion
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">Symfony 8.1</span> sharpens boundaries - HTTP, CLI, and async each get the right kernel.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
Less boilerplate. Faster compiles. Production Messenger fixes. Same Symfony mental model.
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="19 | conclusion" />
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
References · Darkwood
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Links &amp; resources
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap" style="text-align:center">


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

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="20 | merci" />
</x-darkwood.slide>
</x-darkwood.deck>
