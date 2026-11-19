# Hackathon review — 18 November 2026

This review separates repository observations, project descriptions and editorial
judgment. It is not a ranking, an execution certification of competitors or an
announcement of a submitted/accepted Darkwood entry.

## Official requirements

The [event](https://symfony.com/hackathon) and the [Symfony announcement](https://symfony.com/blog/symfony-ai-1-0-0-beta1-ai-and-mcp-hackathon)
confirm an online event from 26 October through **22 November 2026, 18:00 CET**.
Individuals and teams of two can participate. The delivery is a public executable
repository and a 90-second video of real software using Symfony AI and the official
PHP MCP SDK. Providers and storage are not fixed. The beta is also an opportunity
to document reproducible component problems. Registration/submission uses
SymfonyConnect. Opening the public submit link is not evidence that an entry has
been recorded. Final submission remains an owner action requiring approval.

The four stated criteria are creative idea, working software, what the components
unlocked, and craft. No official numeric weighting was found or invented here.

## Competitor comparison

Evidence: official project pages, repository README files, Composer manifests and
selected implementation files, consulted on 18 November. No competitor code was
executed. Linked YouTube pages exposed only login/navigation material through the
documentation browser; Vimeo exposed metadata; the NativePHP Drive link exposed
sign-in. Therefore **video legibility, audio quality and end-to-end runtime claims
are not independently verified**. A 90-second link alone does not prove them.

| Project and problem | Functions and code observed | Symfony AI / MCP evidence | Reproduction and limits | Editorial assessment |
|---|---|---|---|---|
| [Podcast Studio](https://symfony.com/hackathon/projects/podcast-studio): producing a sourced podcast | README describes drafting, revision, voiced dialogue, publishing and listener questions; `src/Mcp/EpisodeTools.php` is the MCP entry point. | [Manifest](https://github.com/anthonyhacart/podcast-studio/blob/d13284e8f9a5883c2ee66c12339a22048881fb46/composer.json): AI Agent/Platform/Mistral/Store and MCP Bundle; several AI packages use `dev-main as 1.0.0-BETA2`, SDK is `*@beta`. | Documented Mistral key and Postgres/pgvector environment. A cassette mode is explicitly a replay, separate from live generation. Video linked, not watched here. | Strong complete-content workflow and component feedback story. More services and moving dependency refs than our narrow demonstration. Runtime and contribution status are claims to verify before relying on them. |
| [DevOps AI Investigator](https://symfony.com/hackathon/projects/devops-ai-investigator): evidence-based incident diagnosis | `IncidentInvestigationAgent` builds a Symfony Agent with a diagnostic toolbox; README separates evidence categories, tool budgets and reports. | [Manifest](https://github.com/MahmoudMoh1499/devops-ai-investigator/blob/1b0beb63ad7041764283a8b641cd7cee18718196/composer.json) locks AI BETA1, SDK `^0.9.0`, MCP Bundle and AI MCP tool. | PHP/Symfony, provider credentials, Postgres and Redis are documented. Gemini is prominent in the README; the direct root requirements inspected include Hugging Face, so a README provider label is insufficient proof of installed bridges. | Clear operational problem and observable evidence discipline. A useful model for separating current evidence from historical context. Deployment and real infrastructure permissions were not tested. |
| [NativePHP Symfony Desktop Bridge](https://symfony.com/hackathon/projects/nativephp-symfony-native-ai-mcp-desktop-bridge): native OS capabilities for agents | The public `demo/src/Mcp/DesktopTools.php` contains notification, clipboard, native window and Symfony-command tools. Root repo is a multi-package bridge, not a single root Composer app. | [Demo manifest](https://github.com/sadiqk2/nativephp-symfony/blob/1a05be5ecc22a11f2c46b5eb72465f87a4f5f3b8/demo/composer.json): AI Bundle, provider bridges and MCP Bundle. SDK use is visible in MCP attributes; do not invent a root installed SDK version. | Desktop/Electron and platform setup are required. README distinguishes packaged desktop evidence from mobile code verified by tests, not on a device. The supplied Drive video could not be inspected. | Strong OS integration differentiation. Permissions and portability create broader risks than local JSON export. No native capability was exercised in this review. |
| [MyGenoTree](https://symfony.com/hackathon/projects/mygenotree-ai-assistant): exploring a family tree and documents | `backend/config/packages/ai.yaml` configures tree/moderation agents and stores; MCP config exposes `TreeReadTools` and `TreeWriteTools`. | [Backend manifest](https://github.com/andreybotanic/my-geno-tree/blob/fefa6555cfefd107b6635bcc17b58537f03cd906/backend/composer.json): AI `^1.0@beta`, MCP Bundle `^0.15`, Qdrant/similarity tooling. | README documents Docker Compose, frontend/backend installs and five environment settings. It does not alone prove document retrieval or confirmation behavior. No personal genealogy data was accessed. | Coherent domain-specific RAG and review flow. Its separation of read and write tools is relevant to approval design. The video and complete installation were not tested. |
| [Domain Tracing](https://symfony.com/hackathon/projects/domain-tracing): understanding executed application behavior | `src/Mcp/TraceMcpServer.php` imports SDK server, resources, tools and MCP Apps types. README explains Xdebug traces, live spans and AI narration. | [Manifest](https://github.com/valtzu/symfony-domain-tracing-bundle/blob/05f591483d6bb172215de460f9ee3ca4a67e6d87/composer.json): AI Agent/Platform, with SDK `^0.9` in require-dev and SDK also suggested. SDK is not an unconditional runtime root requirement. | Requires Xdebug and a compatible Symfony application; demo documents a Claude Code setup. A tracing tool can expose arguments and return values, so redaction matters. | Strong link between actual execution and explanation. More instrumentation requirements than a standalone card. Video playback was unavailable in this review. |

Additional original approaches, read from their official descriptions (less deeply
reviewed than the five repositories above):

- [Cadenza](https://symfony.com/hackathon/projects/cadenza): typed website changes,
  draft/approval/publication boundaries and restorable versions. Its useful lesson
  is to enforce approval in code, rather than only asking the model to be careful.
- [Airlock](https://symfony.com/hackathon/projects/airlock-mcp-elicitation-as-a-permission-system-for-agent-shell-commands):
  MCP elicitation for command-specific permissions, sandboxing and audit. Its scope
  and test counts are author claims, not independently reproduced here.
- [Draw It If You Can](https://symfony.com/hackathon/projects/draw-it-if-you-can):
  multimodal drawing/guessing with external MCP clients. Its playful interaction
  is a stronger differentiator than showing raw generated JSON alone.

## Existing Darkwood candidates

Current manifests and READMEs were reexamined. Canonical card status is not changed
by this selection. Installation constraints are not the same as installed versions.

| Existing card | Current observable state | Decision |
|---|---|---|
| `content-mario-orquestration` | Existing Symfony app, AI BETA1 and SDK 0.10.0 lock; a real generation path and regression scripts. The new designer calls the actual MCP server and offers an inspectable, approved local export. | **Single principal demonstration.** Smallest public, real, reproducible path. |
| `darkwood-publish-article-mcp-apps` | Custom protocol implementation; its draft/publication example contains simulated behavior and lacks the required official AI/SDK path. | Context only; no change. |
| `content-composio-mcp` | Old `symfony/ai-agent dev-main` constraint, external Composio setup and older MCP integration. | Not selected; execution/compatibility qualification would consume the short delivery window. No change. |
| `content-connect-flow` | AI BETA1 and Navi/Flow requirements, language-to-execution experiment, no official SDK requirement in the inspected manifest. | Context only; no change. |
| `content-recherche-entreprise-mcp` | Company search example and custom MCP code; no Symfony AI or official SDK in root requirements. | Not selected; no change. |
| `flowvox` | AI BETA1, voice worker and Flow pipeline; complete voice execution depends on devices/models/services. | Not selected; avoid mixing a second demonstration. No change. |
| `uniflow` and Darkwood PHP components | Useful workflow/library context, not an existing complete AI+MCP contest path by themselves. | Do not add a new application or force AI into unrelated code. |

Operational services and private products are excluded from the public demo. A
working private service is not automatically publishable or reproducible by the
jury. No private source, credentials or production dependency is required here.

## Retained scenario and implementation

A level designer writes a brief. Symfony AI's `level_designer` agent invokes its
single typed tool. That tool uses the official SDK client and stdio transport to
discover and call `generate_original_level` on the existing SDK server. The existing
`level_generator` agent produces the payload. PHP checks geometry; the designer
explains the actual result. `--approve-export` controls creation of fresh local
JSON, evidence and preview files. The model cannot write a chosen path or publish.

This is an adaptation of one existing card, not a new MCP service. The separate
workshop card owns its page/assets/`dist/`; Slides owns its presentations; Navi owns
its narration scripts and video assembly. These presentation tools are not falsely
attributed as runtime dependencies of the candidate.

### Component incompatibility found

The [official AI MCP bridge BETA1 manifest](https://github.com/symfony/ai/blob/v1.0.0-BETA1/src/agent/src/Bridge/Mcp/composer.json)
requires SDK `^0.9`; [ClientToolset](https://github.com/symfony/ai/blob/v1.0.0-BETA1/src/agent/src/Bridge/Mcp/ClientToolset.php)
uses the older `Mcp\Client` API. The existing card locks SDK 0.10.0 and uses
`Mcp\Client\Client`. The bridge was not installed by disguising versions or
silently downgrading the SDK. A small typed adapter in the official Symfony
Toolbox calls the official SDK. No custom JSON-RPC or transport was implemented.
This is a version-compatibility observation, not a filed or acknowledged upstream
bug. No issue was automatically submitted.

## Assessment against the four criteria

- **Creative idea — interpretation:** a reviewable original-level design workflow
  is more specific than a generic chatbot. A geometry preview makes it inspectable.
  Differentiation is still modest against interactive game or native OS entries;
  do not claim likely victory or invent scores.
- **It actually works — evidence:** the real run on 18 November produced Forest
  Clearing - Beginner, 48×15 tiles, four terrain pieces and three objects. Its measured
  47.558-second duration is one observation, not a benchmark. Separate explicit
  fixtures prove regressions, not live generation. The 89.68-second narrated video includes the complete actual process capture at
  source commit e55d2593165b82cbb495d3c14341d610d85a63be, with no wait removed.
- **What it unlocked — interpretation grounded in code:** the natural-language
  designer calls an externally discoverable SDK tool; the same server can be used
  by another MCP client. Actual structured data and PHP validation constrain the
  model's explanation and export. These are concrete component roles, not a claim
  that MCP is necessary for every local function.
- **Craft — evidence and remaining gates:** the card has a public Nix environment,
  locked packages, no private provider key, a clean-install path, meaningful offline
  regressions, a file-approval boundary and a documented playability limit. The video has been decoded and inspected for readable frames and audio signal;
  the article and both decks use the same recorded result. Owner approval of
  final contest submission remains a separate gate.

## Submission boundary

The public workshop and repository do not establish registration, submission or
acceptance. The final submission must be reviewed by the owner. A form hidden behind
SymfonyConnect must not be described as completed or have its field schema invented.
The two verified video files are published as static workshop assets. No external
media platform upload, Replicate generation, social publication or contest
submission is performed by this review document.

### Tool-schema failure found during recording

An actual failed attempt sent more than 2000 characters in the generated tool
argument. The first adapter's reflection-derived metadata had omitted the remote
SDK length constraint. It now forwards the actual SDK schema through the official
ToolFactoryInterface; regression checks assert the bound and rejection of extra
properties. The routing prompt asks for a concise brief, not level JSON. This was
our adapter defect, not an asserted upstream SDK bug. Failed attempts were retained
and never presented as successful captures.

## Iteration 2 — 19 November 2026

Complete original editorial notes were read for Mario, MCP Apps, Composio/Connect
Flow and both Flowvox articles, plus related company-search context. Private
note bodies and planning are not distributed. Public canonical references are
linked from the six workshop cards. The company-search article is related
context, not proof of every Astra/Arize/Langflow feature in that card. The
RapidAPI scraping article concerns another card and was excluded.

The historical four-agent/Godot Mario article does not prove the contest path;
current evidence covers actual level geometry. MCP Apps still simulates drafting
and publishing. No fresh complete external Composio/Ollama connector run or
microphone/mobile Flowvox run is claimed. Recherche Entreprise uses custom
JSON-RPC/SSE, not the official SDK. Canonical card statuses are unchanged.

### Supplemental DarkwoodGame evidence

Four tools were already declared on the existing Portal MCP server. This
iteration qualified schemas, API forwarding, authentication, permissions, quotas
and action approval without creating another server. A real Symfony AI observer
and official SDK HTTP client read a synthetic player through the actual local
API. Only `user`, `state`, `mode`, `display` are returned; no detailed inventory
is claimed. The agent receives only the state tool. Public evidence:
`public/hackathon/darkwood-game-proof.json`.

All four operations were exercised locally. An unapproved action was refused;
an approved main-menu request succeeded. A free-key quota of two accepted two
requests then refused the third, with persisted count two. Premium archives were
refused for the free key. No combat was executed. The approval digest binds user
and sorted query within a request; it is not a secret, expiry, signature or
replay protection. A trusted client must obtain consent per action and must not
inject a global approval header or retry mutations automatically.

Production retains SDK 0.8.1 and MCP Bundle 0.14.1; relevant APIs were checked
without upgrading dependencies. Four-tool discovery and refusal without an API
key passed. No authenticated production player read or game action was executed.
OIDC metadata stayed identical and login/API docs remained accessible; a complete
SSO authentication journey was not tested. Mario remains independently executable
without private Portal access and is still the single contest candidate.

### Avatar and recording provenance

Navi reuses its existing MATYO91 portrait as a static overlay, not a talking head
or lip-synced avatar. No Replicate call was made. In the short film it appears
only in the narrated introduction/conclusion. The entire actual capture remains
the 18 November run at commit `e55d2593165b82cbb495d3c14341d610d85a63be`.
Slides and presentation treatment are updated on 19 November. This retained
capture is not presented as a new generation. The supplementary real DarkwoodGame
observer was independently run on 19 November in an isolated local database.

## Iteration 3 — Darkwood MCP Experience

The audiovisual story now guides a visitor through the workshop, a request, a
real MCP call and its result. The six selected cards remain distinct from the
supplementary Portal/DarkwoodGame path. The latter is an independent read-only
demonstration using synthetic local data, not a Mario-to-game integration. The
existing canonical card statuses, sources and editorial references are preserved.

The official English edit retains the actual request and successful result from
18 November at `e55d2593165b82cbb495d3c14341d610d85a63be`. Unlike iteration 2, it
condenses idle waiting, clearly disclosed in narration and recording captions.
It does not describe the edited duration as generation latency. The original
full capture and earlier film remain recoverable; the JSON evidence is unchanged.
DarkwoodGame's recorded 19 November response is shown as such; no new live call
or production player interaction is implied.

Existing Navi scenes and narration are reused in the standard FFmpeg editing
step, with short cross-dissolves and a real browser capture of workshop discovery.
The fixed portrait is shown on guided scenes, removed over execution, and makes
no claim of animation or lip synchronization. The extra fade treatment removes
black-frame edges in this editorial copy without changing Navi's global defaults.
No application runtime, SDK dependency, external account or game data is changed.
Final contest submission is still reserved for owner approval.

## Iteration 4 — continuous narration and YouTube

Previous sections describe their dated iterations. The current English film is
[Darkwood MCP Experience](https://youtu.be/IbljHH4wj0s), 86.21 seconds, unlisted;
the [French companion](https://youtu.be/Ytts4XFgTxI) is 232.88 seconds. One complete
TTS take supplies the master audio. ASR segment timestamps align slide cues;
within-segment positions are interpolated and ambiguous cues fail. Silent visuals
are muxed around the intact master, without audio crossfades or voice acceleration.
The actual recorded Mario request/response and local DarkwoodGame provenance
remain unchanged. No final contest submission or promotional social post occurred.

The workshop no longer deploys local MP4s. IDs, canonical links and legacy-path
mapping are in `experience-edit.json`. GitHub Pages has no native custom HTTP
redirects for those file paths; known active references are updated and the 404
page gives explicit YouTube alternatives. Original local films and Git history
are preserved. Untracking artifacts does not shrink historical Git objects.
