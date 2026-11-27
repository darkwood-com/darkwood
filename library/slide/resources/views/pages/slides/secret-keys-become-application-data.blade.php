<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="section" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--section',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-section-title dw-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead',
));
@endphp
<x-darkwood.heading variant="section" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Darkwood · BYOK · Symfony 8.2 · Navi
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
SECRET KEYS<br>BECOME<br>APPLICATION DATA
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
BYOK × Symfony 8.2 × Navi
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<style>
    .sk-chain {
        line-height: 1.35;
        white-space: pre;
        margin: 0;
    }
    .sk-label {
        letter-spacing: .1em;
        text-transform: uppercase;
        font-weight: 700;
    }
    .sk-status {
        display: inline-block;
        margin-top: 12px;
        padding: 6px 12px;
        border-radius: 999px;
        border: var(--dw-border-width) solid var(--dw-line);
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }
    .sk-path {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 8px;
        margin-top: 22px;
    }
    .sk-path span {
        border: var(--dw-border-width) solid var(--dw-line);
        border-radius: 8px;
        padding: 14px 8px;
        text-align: center;
        font-weight: 700;
        line-height: 1.3;
    }
    .sk-path span em {
        display: block;
        margin-top: 6px;
        font-style: normal;
    }
    .sk-path span.is-stop {
        opacity: .78;
    }
    .sk-eq {
        line-height: 1.55;
        white-space: pre;
        margin: 0;
    }
</style>
@endverbatim
@verbatim
<section class="dw-wrap">



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
            <p class="dw-kicker">The old model</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_1_0'
ANTHROPIC_API_KEY=...
SW_CODE_1_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="dw-takeaway">The secret belongs to the deployment.</p>
            <p class="dw-note">operator → deployment → application</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | .env" />
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
  'class' => 'sw-heading dw-question',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Ownership
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What happens when the key<br>belongs to the <span class="dw-accent">user</span>?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-split" style="margin-top:28px">
                <div class="dw-split-panel" style="min-height:0;gap:10px">
                    <h3>Deployment</h3>
                    <p style="margin:0;line-height:1.45">.env<br>static<br>one credential<br>boot-time<br>configuration</p>
                </div>
                <div class="dw-split-panel is-ok" style="min-height:0;gap:10px">
                    <h3>User</h3>
                    <p style="margin:0;line-height:1.45">HTTP<br>dynamic<br>many credentials<br>runtime<br>application data</p>
                </div>
            </div>
            <p class="dw-takeaway mt-6"><code>.env</code> is no longer the answer.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | BYOK" />
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
The experiment
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A secret now has a lifecycle
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-split" style="margin-top:24px;align-items:stretch">
                <div class="dw-split-panel" style="min-height:0;justify-content:center">

@endverbatim
@php
$snippet = <<<'SW_CODE_3_0'
User
 ↓
Symfony Form
 ↓
encrypt
 ↓
SQLite ciphertext
 ↓
decrypt
 ↓
Navi
 ↓
Symfony AI
 ↓
Anthropic
SW_CODE_3_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok" style="min-height:0;gap:14px">
                    <h3>Four verbs</h3>
                    <div class="dw-chip">INPUT</div>
                    <div class="dw-chip">STORE</div>
                    <div class="dw-chip">USE</div>
                    <div class="dw-chip">OBSERVE</div>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | lifecycle" />
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
Boundaries
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Keep every layer small
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-split" style="margin-top:20px">
                <div class="dw-split-panel" style="min-height:0;gap:8px;justify-content:flex-start">
                    <h3>Responsibilities</h3>
                    <p style="margin:0;line-height:1.55"><strong>FORM</strong> — accept credential<br><strong>KEY MANAGEMENT</strong> — encrypt / decrypt<br><strong>DATABASE</strong> — persist ciphertext<br><strong>NAVI</strong> — orchestrate<br><strong>SYMFONY AI</strong> — talk to provider</p>
                </div>
                <div class="dw-split-panel" style="min-height:0">

@endverbatim
@php
$snippet = <<<'SW_CODE_4_0'
AiConnection
    ↓
CredentialProtector::decrypt()
    ↓
WorkflowRunner
    ↓
TestAiConnectionAction
    ↓
Anthropic\Factory::createPlatform()
SW_CODE_4_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <p class="dw-takeaway mt-6">Navi is a workflow shell, not an LLM SDK.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | layers" />
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
Symfony 8.2 · experimental
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Symfony gets the missing primitive
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_5_0'
$ciphertext = $this->encrypter->encrypt(
    $this->keyId,
    $apiKey,
);

$connection->setCiphertextBlob(
    base64_encode($ciphertext->blob),
);
$connection->setKmsKeyId($ciphertext->keyId);
SW_CODE_5_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-5 dw-grid dw-grid-3" style="gap:10px">
                <div class="dw-chip">Ciphertext = blob + key id</div>
                <div class="dw-chip">8.2.x-dev · experimental</div>
                <div class="dw-chip">direct encrypt · not envelope</div>
            </div>
            <div class="dw-split" style="margin-top:18px">
                <div class="dw-split-panel is-ok" style="min-height:0;padding:20px">
                    <h3>dev / test</h3>
                    <p style="margin:0">Sodium</p>
                    <span class="sk-status is-tested">Tested</span>
                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;padding:20px">
                    <h3>prod</h3>
                    <p style="margin:0">AWS KMS</p>
                    <span class="sk-status is-configured">Configured only</span>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | KeyManagement" />
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
HTTP input
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The form becomes a security boundary
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_6_0'
#[AsFormType]
final class AiConnectionInput
{
    #[FormField(PasswordType::class, [
        'always_empty' => true,
        'attr' => ['autocomplete' => 'new-password'],
    ])]
    public ?string $apiKey = null;
}
SW_CODE_6_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-5 dw-grid dw-grid-3" style="gap:10px">
                <div class="dw-chip">password field</div>
                <div class="dw-chip">never redisplayed</div>
                <div class="dw-chip">blank edit = keep ciphertext</div>
            </div>
            <p class="dw-takeaway mt-6">The key used to enter through dotenv. Now it enters through HTTP.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | form" />
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
Evidence
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What SQLite actually contains
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_7_0'
$ sqlite3 var/data.db \
  "SELECT kms_key_id,
          length(ciphertext_blob),
          instr(ciphertext_blob, 'sk-ant')
   FROM ai_connection;"

app-key|96|0
SW_CODE_7_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="dw-takeaway mt-6">PLAINTEXT FOUND: <span class="dw-accent">0</span></p>
            <p class="dw-note">Evidence, not a cryptographic audit.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | ciphertext" />
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
Execution boundary
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Decrypt at the last responsible moment
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-split" style="margin-top:18px">
                <div class="dw-split-panel" style="min-height:0;padding:18px">

@endverbatim
@php
$snippet = <<<'SW_CODE_8_0'
$apiKey = $this->credentials->decrypt($connection);

try {
    $this->workflows->run(
        Context::fromArray([
            'connection_id' => $connection->getId(),
            'provider' => $connection->getProvider(),
            'model' => $connection->getModel(),
        ]),
        [new TestAiConnectionAction($apiKey, ...)],
    );
} finally {
    $apiKey = str_repeat("\0", strlen($apiKey));
}
SW_CODE_8_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok" style="min-height:0;gap:12px">
                    <h3>Navi Context</h3>
                    <p style="margin:0">connection_id<br>provider<br>model</p>
                    <p style="margin:0;font-weight:700">SECRET<br>NOT HERE</p>
                </div>
            </div>
            <p class="dw-takeaway mt-5"><code>context_has_secret: no</code></p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | decrypt" />
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
Live probe
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Failure is still evidence
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-split" style="margin-top:16px">
                <div class="dw-split-panel" style="min-height:0;padding:18px">

@endverbatim
@php
$snippet = <<<'SW_CODE_9_0'
plaintext_in_ciphertext: no
decrypt_matches: yes
context_has_secret: no
test_ok: false

Anthropic:
credit balance too low
SW_CODE_9_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;padding:18px;gap:8px">
                    <h3>How far it travelled</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_9_1'
encrypt          ✓
persist          ✓
decrypt          ✓
Navi             ✓
Symfony AI       ✓
Anthropic        ✓
completion       ✕
SW_CODE_9_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <p class="dw-takeaway mt-5">A billing error still tells us how far the request travelled.</p>
            <p class="dw-note" style="margin-top:14px">Proves a usable credential reached the provider. Not completion, token usage, or a cost receipt.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | probe" />
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
Observability · Cost of a diff
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
An AI credential has an<br><span class="dw-accent">economic blast radius</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="mt-5 dw-grid dw-grid-4" style="gap:10px">
                <div class="dw-chip">API KEY</div>
                <div class="dw-chip">MODEL ACCESS</div>
                <div class="dw-chip">TOKENS</div>
                <div class="dw-chip">$</div>
            </div>
            <div class="dw-split" style="margin-top:18px">
                <div class="dw-split-panel" style="min-height:0;padding:20px">

@endverbatim
@php
$snippet = <<<'SW_CODE_10_0'
cost =
  input × input_rate
+ output × output_rate
+ cache_write × rate
+ cache_read × rate

per 1M tokens
SW_CODE_10_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;padding:20px;gap:10px">
                    <h3>Status</h3>
                    <p style="margin:0">Implemented<br>Not validated against<br>a successful provider receipt</p>
                    <span class="sk-status is-wired">42 / 8 = simulated counts</span>
                </div>
            </div>
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
Transport
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Provider ≠ endpoint ≠ credential storage
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-split" style="margin-top:20px">
                <div class="dw-split-panel" style="min-height:0">

@endverbatim
@php
$snippet = <<<'SW_CODE_11_0'
Anthropic bridge
      │
      ├── api.anthropic.com
      │
      └── Azure AI compatible
          endpoint
SW_CODE_11_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;gap:10px">
                    <h3>Reported Azure shape</h3>
                    <p style="margin:0;line-height:1.45">&lt;resource&gt;.services.ai.azure.com<br>/anthropic/v1/messages</p>
                    <span class="sk-status is-wired">Azure baseUrl: wired, not called</span>
                </div>
            </div>
            <p class="dw-takeaway mt-6">Sometimes compatibility is configuration, not another abstraction.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | endpoint" />
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
Key custody
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Sodium locally, KMS outside
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-split" style="margin-top:22px">
                <div class="dw-split-panel is-ok" style="min-height:0">
                    <h3>Sodium</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_12_0'
application
    ↓
encryption key
    ↓
ciphertext
SW_CODE_12_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    <span class="sk-status is-tested">Tested</span>
                </div>
                <div class="dw-split-panel is-warn" style="min-height:0">
                    <h3>AWS KMS</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_12_1'
application
    ↓
KMS API
    ↓
master key outside app
SW_CODE_12_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    <span class="sk-status is-configured">Configured · not called</span>
                </div>
            </div>
            <p class="dw-takeaway mt-6">External KMS improves key management. It does not make a compromised runtime safe.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | KMS" />
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
Scoreboard
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
What actually ran
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">


            <div class="dw-split" style="margin-top:18px">
                <div class="dw-split-panel is-ok" style="min-height:0;justify-content:flex-start;gap:8px;padding:22px">
                    <h3>Verified</h3>
                    <p style="margin:0;line-height:1.5">container / Twig / YAML lint<br>PHPUnit · 2 tests · 15 assertions<br>Browser BYOK flow<br>encrypted badge · blank key on edit<br>invalid-key error<br>SQLite plaintext search = 0<br>Live Anthropic reached<br>billing error</p>
                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;justify-content:flex-start;gap:8px;padding:22px">
                    <h3>Not verified</h3>
                    <p style="margin:0;line-height:1.5">AWS KMS call<br>Azure Anthropic call<br>successful completion<br>real token receipt<br>provider-validated cost</p>
                    <p class="sk-label" style="margin-top:12px">Statuses</p>
                    <p style="margin:0">implemented · configured<br>tested · verified</p>
                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | evidence" />
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
            <p class="dw-kicker">Ownership is the architecture</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_14_0'
ANTHROPIC_API_KEY=...
SW_CODE_14_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="dw-split" style="margin-top:16px">
                <div class="dw-split-panel" style="min-height:0;padding:20px">

@endverbatim
@php
$snippet = <<<'SW_CODE_14_1'
user
 ↓
input
 ↓
encryption
 ↓
persistence
 ↓
execution
 ↓
observability
 ↓
rotation
SW_CODE_14_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok" style="min-height:0;gap:12px">
                    <p style="margin:0;line-height:1.4"><code>.env</code> is enough when the secret belongs to the deployment.</p>
                    <p style="margin:0;font-weight:700">Once it belongs to a user, it needs an application lifecycle.</p>
                    <p style="margin:0;line-height:1.5">KeyManagement → protect it<br>Navi → orchestrate it<br>Symfony AI → use it</p>
                </div>
            </div>
            <p class="dw-takeaway mt-5">Each layer can stay small.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>
</x-darkwood.deck>
