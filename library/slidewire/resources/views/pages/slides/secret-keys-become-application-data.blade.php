{{-- Secret Keys Become Application Data --}}
{{-- Title: Secret Keys Become Application Data --}}
{{-- Description: BYOK turns an AI API key from an env var into application data. A Symfony 8.2 experiment encrypts it with KeyManagement, decrypts it only at execution time, and hands it to Navi and Symfony AI. --}}
{{-- Slug: secret-keys-become-application-data --}}
{{-- Keywords: Symfony 8.2, KeyManagement, BYOK, AWS KMS, Sodium, Darkwood Navi, Symfony AI, API keys --}}
{{-- Source: tasks/#68b803_secret-keys/article.md · Companion: content/navi-key-management --}}
{{-- Route: /slides/secret-keys-become-application-data --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

<style>
    .sk-chain {
        font-family: var(--font-mono);
        font-size: 20px;
        line-height: 1.35;
        color: #d7ecff;
        white-space: pre;
        margin: 0;
    }
    .sk-chain.is-lg { font-size: 22px; }
    .sk-label {
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--dw-cyan);
        font-size: 14px;
        font-weight: 700;
    }
    .sk-status {
        display: inline-block;
        margin-top: 12px;
        padding: 6px 12px;
        border-radius: 999px;
        border: 1px solid var(--dw-line);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }
    .sk-status.is-tested {
        color: var(--dw-lime);
        border-color: rgba(184, 255, 106, .45);
    }
    .sk-status.is-configured,
    .sk-status.is-wired {
        color: #ffb4a8;
        border-color: rgba(255, 120, 100, .4);
    }
    .sk-path {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 8px;
        margin-top: 22px;
    }
    .sk-path span {
        border: 1px solid var(--dw-line);
        border-radius: 8px;
        background: var(--dw-panel);
        padding: 14px 8px;
        text-align: center;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.3;
    }
    .sk-path span em {
        display: block;
        margin-top: 6px;
        font-style: normal;
        font-size: 18px;
        color: var(--dw-lime);
    }
    .sk-path span.is-stop {
        border-color: rgba(255, 120, 100, .45);
        opacity: .78;
    }
    .sk-path span.is-stop em { color: #ffb4a8; }
    .sk-eq {
        font-family: var(--font-mono);
        font-size: 18px;
        line-height: 1.55;
        color: #d7ecff;
        white-space: pre;
        margin: 0;
    }
</style>

    {{-- 1 · TITLE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · BYOK · Symfony 8.2 · Navi</p>
            <h1 class="dw-title">SECRET KEYS<br>BECOME<br>APPLICATION DATA</h1>
            <p class="dw-lead">BYOK × Symfony 8.2 × Navi</p>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · THE OLD MODEL --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The old model</p>
            <pre class="dw-code mt-8" style="font-size:clamp(28px,3.2vw,48px);padding:36px 40px;">ANTHROPIC_API_KEY=...</pre>
            <p class="dw-takeaway">The secret belongs to the deployment.</p>
            <p class="dw-note" style="font-family:var(--font-mono);">operator → deployment → application</p>
        </section>
        <footer class="dw-footer"><span>01 / .env</span></footer>
    </x-slidewire::slide>

    {{-- 3 · BYOK CHANGES OWNERSHIP --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Ownership</p>
            <h2 class="dw-question" style="margin-top:8px;">What happens when the key<br>belongs to the <span class="dw-accent">user</span>?</h2>
            <div class="dw-split" style="margin-top:28px;">
                <div class="dw-split-panel" style="min-height:0;gap:10px;">
                    <h3>Deployment</h3>
                    <p style="margin:0;font-size:22px;line-height:1.45;">.env<br>static<br>one credential<br>boot-time<br>configuration</p>
                </div>
                <div class="dw-split-panel is-ok" style="min-height:0;gap:10px;">
                    <h3>User</h3>
                    <p style="margin:0;font-size:22px;line-height:1.45;color:var(--dw-text);">HTTP<br>dynamic<br>many credentials<br>runtime<br>application data</p>
                </div>
            </div>
            <p class="dw-takeaway mt-6"><code>.env</code> is no longer the answer.</p>
        </section>
        <footer class="dw-footer"><span>02 / BYOK</span></footer>
    </x-slidewire::slide>

    {{-- 4 · A SECRET NOW HAS A LIFECYCLE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">The experiment</p>
            <h2 class="dw-heading-slide">A secret now has a lifecycle</h2>
            <div class="dw-split" style="margin-top:24px;align-items:stretch;">
                <div class="dw-split-panel" style="min-height:0;justify-content:center;">
                    <pre class="sk-chain is-lg">User
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
Anthropic</pre>
                </div>
                <div class="dw-split-panel is-ok" style="min-height:0;gap:14px;">
                    <h3>Four verbs</h3>
                    <div class="dw-chip">INPUT</div>
                    <div class="dw-chip">STORE</div>
                    <div class="dw-chip">USE</div>
                    <div class="dw-chip">OBSERVE</div>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>03 / lifecycle</span></footer>
    </x-slidewire::slide>

    {{-- 5 · KEEP EVERY LAYER SMALL --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Boundaries</p>
            <h2 class="dw-heading-slide">Keep every layer small</h2>
            <div class="dw-split" style="margin-top:20px;">
                <div class="dw-split-panel" style="min-height:0;gap:8px;justify-content:flex-start;">
                    <h3>Responsibilities</h3>
                    <p style="margin:0;font-size:18px;line-height:1.55;"><strong>FORM</strong> — accept credential<br><strong>KEY MANAGEMENT</strong> — encrypt / decrypt<br><strong>DATABASE</strong> — persist ciphertext<br><strong>NAVI</strong> — orchestrate<br><strong>SYMFONY AI</strong> — talk to provider</p>
                </div>
                <div class="dw-split-panel" style="min-height:0;">
                    <pre class="sk-chain">AiConnection
    ↓
CredentialProtector::decrypt()
    ↓
WorkflowRunner
    ↓
TestAiConnectionAction
    ↓
Anthropic\Factory::createPlatform()</pre>
                </div>
            </div>
            <p class="dw-takeaway mt-6">Navi is a workflow shell, not an LLM SDK.</p>
        </section>
        <footer class="dw-footer"><span>04 / layers</span></footer>
    </x-slidewire::slide>

    {{-- 6 · SYMFONY GETS THE MISSING PRIMITIVE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Symfony 8.2 · experimental</p>
            <h2 class="dw-heading-slide">Symfony gets the missing primitive</h2>
            <pre class="dw-code mt-5" style="font-size:17px;margin:0;">$ciphertext = $this->encrypter->encrypt(
    $this->keyId,
    $apiKey,
);

$connection->setCiphertextBlob(
    base64_encode($ciphertext->blob),
);
$connection->setKmsKeyId($ciphertext->keyId);</pre>
            <div class="mt-5 dw-grid dw-grid-3" style="gap:10px;">
                <div class="dw-chip">Ciphertext = blob + key id</div>
                <div class="dw-chip">8.2.x-dev · experimental</div>
                <div class="dw-chip">direct encrypt · not envelope</div>
            </div>
            <div class="dw-split" style="margin-top:18px;">
                <div class="dw-split-panel is-ok" style="min-height:0;padding:20px;">
                    <h3>dev / test</h3>
                    <p style="margin:0;font-size:22px;">Sodium</p>
                    <span class="sk-status is-tested">Tested</span>
                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;padding:20px;">
                    <h3>prod</h3>
                    <p style="margin:0;font-size:22px;">AWS KMS</p>
                    <span class="sk-status is-configured">Configured only</span>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>05 / KeyManagement</span></footer>
    </x-slidewire::slide>

    {{-- 7 · THE FORM BECOMES A SECURITY BOUNDARY --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">HTTP input</p>
            <h2 class="dw-heading-slide">The form becomes a security boundary</h2>
            <pre class="dw-code mt-5" style="font-size:17px;">#[AsFormType]
final class AiConnectionInput
{
    #[FormField(PasswordType::class, [
        'always_empty' => true,
        'attr' => ['autocomplete' => 'new-password'],
    ])]
    public ?string $apiKey = null;
}</pre>
            <div class="mt-5 dw-grid dw-grid-3" style="gap:10px;">
                <div class="dw-chip">password field</div>
                <div class="dw-chip">never redisplayed</div>
                <div class="dw-chip">blank edit = keep ciphertext</div>
            </div>
            <p class="dw-takeaway mt-6">The key used to enter through dotenv. Now it enters through HTTP.</p>
        </section>
        <footer class="dw-footer"><span>06 / form</span></footer>
    </x-slidewire::slide>

    {{-- 8 · WHAT SQLITE ACTUALLY CONTAINS --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Evidence</p>
            <h2 class="dw-heading-slide">What SQLite actually contains</h2>
            <pre class="dw-code mt-5" style="font-size:17px;">$ sqlite3 var/data.db \
  "SELECT kms_key_id,
          length(ciphertext_blob),
          instr(ciphertext_blob, 'sk-ant')
   FROM ai_connection;"

app-key|96|0</pre>
            <p class="dw-takeaway mt-6" style="font-size:clamp(28px,3vw,44px);">PLAINTEXT FOUND: <span class="dw-accent">0</span></p>
            <p class="dw-note">Evidence, not a cryptographic audit.</p>
        </section>
        <footer class="dw-footer"><span>07 / ciphertext</span></footer>
    </x-slidewire::slide>

    {{-- 9 · DECRYPT AT THE LAST RESPONSIBLE MOMENT --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Execution boundary</p>
            <h2 class="dw-heading-slide">Decrypt at the last responsible moment</h2>
            <div class="dw-split" style="margin-top:18px;">
                <div class="dw-split-panel" style="min-height:0;padding:18px;">
                    <pre class="sk-chain" style="font-size:14px;white-space:pre-wrap;">$apiKey = $this->credentials->decrypt($connection);

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
}</pre>
                </div>
                <div class="dw-split-panel is-ok" style="min-height:0;gap:12px;">
                    <h3>Navi Context</h3>
                    <p style="margin:0;font-size:20px;">connection_id<br>provider<br>model</p>
                    <p style="margin:0;font-size:22px;color:var(--dw-lime);font-weight:700;">SECRET<br>NOT HERE</p>
                </div>
            </div>
            <p class="dw-takeaway mt-5"><code>context_has_secret: no</code></p>
        </section>
        <footer class="dw-footer"><span>08 / decrypt</span></footer>
    </x-slidewire::slide>

    {{-- 10 · FAILURE IS STILL EVIDENCE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Live probe</p>
            <h2 class="dw-heading-slide">Failure is still evidence</h2>
            <div class="dw-split" style="margin-top:16px;">
                <div class="dw-split-panel" style="min-height:0;padding:18px;">
                    <pre class="sk-chain" style="font-size:17px;">plaintext_in_ciphertext: no
decrypt_matches: yes
context_has_secret: no
test_ok: false

Anthropic:
credit balance too low</pre>
                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;padding:18px;gap:8px;">
                    <h3>How far it travelled</h3>
                    <pre class="sk-chain" style="font-size:16px;">encrypt          ✓
persist          ✓
decrypt          ✓
Navi             ✓
Symfony AI       ✓
Anthropic        ✓
completion       ✕</pre>
                </div>
            </div>
            <p class="dw-takeaway mt-5">A billing error still tells us how far the request travelled.</p>
            <p class="dw-note" style="margin-top:14px;">Proves a usable credential reached the provider. Not completion, token usage, or a cost receipt.</p>
        </section>
        <footer class="dw-footer"><span>09 / probe</span></footer>
    </x-slidewire::slide>

    {{-- 11 · A KEY CAN SPEND MONEY --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Observability · Cost of a diff</p>
            <h2 class="dw-heading-slide">An AI credential has an<br><span class="dw-accent">economic blast radius</span></h2>
            <div class="mt-5 dw-grid dw-grid-4" style="gap:10px;">
                <div class="dw-chip">API KEY</div>
                <div class="dw-chip">MODEL ACCESS</div>
                <div class="dw-chip">TOKENS</div>
                <div class="dw-chip">$</div>
            </div>
            <div class="dw-split" style="margin-top:18px;">
                <div class="dw-split-panel" style="min-height:0;padding:20px;">
                    <pre class="sk-eq">cost =
  input × input_rate
+ output × output_rate
+ cache_write × rate
+ cache_read × rate

per 1M tokens</pre>
                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;padding:20px;gap:10px;">
                    <h3>Status</h3>
                    <p style="margin:0;font-size:20px;">Implemented<br>Not validated against<br>a successful provider receipt</p>
                    <span class="sk-status is-wired">42 / 8 = simulated counts</span>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>10 / cost</span></footer>
    </x-slidewire::slide>

    {{-- 12 · PROVIDER ≠ ENDPOINT ≠ CREDENTIAL STORAGE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Transport</p>
            <h2 class="dw-heading-slide">Provider ≠ endpoint ≠ credential storage</h2>
            <div class="dw-split" style="margin-top:20px;">
                <div class="dw-split-panel" style="min-height:0;">
                    <pre class="sk-chain">Anthropic bridge
      │
      ├── api.anthropic.com
      │
      └── Azure AI compatible
          endpoint</pre>
                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;gap:10px;">
                    <h3>Reported Azure shape</h3>
                    <p style="margin:0;font-family:var(--font-mono);font-size:16px;line-height:1.45;">&lt;resource&gt;.services.ai.azure.com<br>/anthropic/v1/messages</p>
                    <span class="sk-status is-wired">Azure baseUrl: wired, not called</span>
                </div>
            </div>
            <p class="dw-takeaway mt-6">Sometimes compatibility is configuration, not another abstraction.</p>
        </section>
        <footer class="dw-footer"><span>11 / endpoint</span></footer>
    </x-slidewire::slide>

    {{-- 13 · SODIUM LOCALLY, KMS OUTSIDE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Key custody</p>
            <h2 class="dw-heading-slide">Sodium locally, KMS outside</h2>
            <div class="dw-split" style="margin-top:22px;">
                <div class="dw-split-panel is-ok" style="min-height:0;">
                    <h3>Sodium</h3>
                    <pre class="sk-chain">application
    ↓
encryption key
    ↓
ciphertext</pre>
                    <span class="sk-status is-tested">Tested</span>
                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;">
                    <h3>AWS KMS</h3>
                    <pre class="sk-chain">application
    ↓
KMS API
    ↓
master key outside app</pre>
                    <span class="sk-status is-configured">Configured · not called</span>
                </div>
            </div>
            <p class="dw-takeaway mt-6">External KMS improves key management. It does not make a compromised runtime safe.</p>
        </section>
        <footer class="dw-footer"><span>12 / KMS</span></footer>
    </x-slidewire::slide>

    {{-- 14 · WHAT ACTUALLY RAN --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Scoreboard</p>
            <h2 class="dw-heading-slide">What actually ran</h2>
            <div class="dw-split" style="margin-top:18px;">
                <div class="dw-split-panel is-ok" style="min-height:0;justify-content:flex-start;gap:8px;padding:22px;">
                    <h3>Verified</h3>
                    <p style="margin:0;font-size:17px;line-height:1.5;color:var(--dw-text);">container / Twig / YAML lint<br>PHPUnit · 2 tests · 15 assertions<br>Browser BYOK flow<br>encrypted badge · blank key on edit<br>invalid-key error<br>SQLite plaintext search = 0<br>Live Anthropic reached<br>billing error</p>
                </div>
                <div class="dw-split-panel is-warn" style="min-height:0;justify-content:flex-start;gap:8px;padding:22px;">
                    <h3>Not verified</h3>
                    <p style="margin:0;font-size:17px;line-height:1.5;">AWS KMS call<br>Azure Anthropic call<br>successful completion<br>real token receipt<br>provider-validated cost</p>
                    <p class="sk-label" style="margin-top:12px;">Statuses</p>
                    <p style="margin:0;font-size:16px;">implemented · configured<br>tested · verified</p>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>13 / evidence</span></footer>
    </x-slidewire::slide>

    {{-- 15 · OWNERSHIP IS THE ARCHITECTURE --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Ownership is the architecture</p>
            <pre class="dw-code" style="font-size:22px;padding:18px 24px;">ANTHROPIC_API_KEY=...</pre>
            <div class="dw-split" style="margin-top:16px;">
                <div class="dw-split-panel" style="min-height:0;padding:20px;">
                    <pre class="sk-chain">user
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
rotation</pre>
                </div>
                <div class="dw-split-panel is-ok" style="min-height:0;gap:12px;">
                    <p style="margin:0;font-size:20px;line-height:1.4;"><code>.env</code> is enough when the secret belongs to the deployment.</p>
                    <p style="margin:0;font-size:22px;font-weight:700;color:var(--dw-text);">Once it belongs to a user, it needs an application lifecycle.</p>
                    <p style="margin:0;font-size:17px;line-height:1.5;">KeyManagement → protect it<br>Navi → orchestrate it<br>Symfony AI → use it</p>
                </div>
            </div>
            <p class="dw-takeaway mt-5">Each layer can stay small.</p>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
