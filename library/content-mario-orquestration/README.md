# Darkwood original level designer

An existing Darkwood Symfony application interprets a level brief with a **Symfony AI Agent**, calls its existing **official PHP MCP SDK server**, checks the returned geometry and produces a reviewable local artifact only after explicit approval.

This is a level design and inspection workflow, **not a playable game**. No Nintendo assets, characters or copied levels are included. The historical Godot importer and multi-agent experiments are not the submitted execution path.

## Five-minute quickstart

Requirements: Git, Nix with flakes, network access and your own OpenAI API key with provider credit. Nix supplies PHP 8.5, Composer and required extensions; no Docker, database, private package, Navi installation or system PHP is needed.

```sh
git clone --depth=1 --filter=blob:none --sparse https://github.com/darkwood-com/darkwood.git
cd darkwood
git sparse-checkout set library/content-mario-orquestration library/darkwood-php/src/Darkwood/Component/Navi/tools/env/shell.nix
cd library/content-mario-orquestration
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix
composer install --no-scripts --no-interaction
composer check-platform-reqs
php tests/generation-regression.php
php tests/design-regression.php
```

Provide `OPENAI_API_KEY` through your process environment or your own ignored `.env.local`. The committed `.env` contains no provider credential. Never paste a key into source files, transcripts or recordings. A key is not required for the two explicit offline regression scripts.

```sh
php bin/console app:mario:design \
  --prompt='Create a small original forest platformer level for a beginner with two low platforms and three collectible lights.'
```

This makes real model calls and prints the result. It does **not** write any level, preview or execution evidence. To explicitly approve new local artifacts:

```sh
php bin/console app:mario:design \
  --prompt='Create a small original forest platformer level for a beginner with two low platforms and three collectible lights.' \
  --approve-export
```

Open the printed directory's `preview.html` in a browser. `level.json` contains the actual generated data; `evidence.json` records the actual MCP tool call, model summary and elapsed time. Each approved export uses a new directory under `var/mario-design/`, never overwriting a previous run. No publishing capability is exposed to the model.

The five-minute path is an installation guide, not a guaranteed network or model latency. A failed request exits nonzero, never silently substitutes a fixture and never claims a successful design.

## Actual architecture

1. `DesignLevelCommand` starts the Symfony AI Agent named `level_designer` with one typed tool in the official `Toolbox`.
2. The adapter forwards the real SDK input schema (including its 2000-character prompt bound) to the official Toolbox. The agent interprets the user's brief and invokes `generate_original_level` exactly once.
3. `McpLevelTool` connects the official `Mcp\Client\Client` to the existing `bin/mcp.php` over SDK `StdioTransport`, discovers its tool and calls it.
4. That existing server uses `Mcp\Server\Server` and the Symfony AI `level_generator` agent to generate original JSON through OpenAI `gpt-5-mini`.
5. PHP validates the actual payload. The designer explains it; the optional exporter produces JSON, evidence and a geometry preview after CLI approval.

The `symfony/ai-agent`, `symfony/ai-platform`, OpenAI bridge and AI Bundle are locked to **1.0.0-BETA1**; **mcp/sdk is locked to 0.10.0**. The Symfony application keeps its existing AI Bundle and services. Navi and Flow remain installed historical dependencies, **not execution stages claimed for this demonstration**.

### Why a small adapter

The published `symfony/ai-mcp-tool` 1.0.0-BETA1 requires `mcp/sdk ^0.9` and its bridge targets the former `Mcp\Client` API. It cannot be combined with the existing locked SDK 0.10.0 client namespace. Rather than downgrade or alias the SDK, this card uses the official Symfony `Toolbox` with a single adapter calling the official SDK client. It implements no JSON-RPC, transport or MCP server of its own.

Sources checked on 18 November 2026: [official bridge manifest](https://github.com/symfony/ai/blob/v1.0.0-BETA1/src/agent/src/Bridge/Mcp/composer.json), [bridge implementation](https://github.com/symfony/ai/blob/v1.0.0-BETA1/src/agent/src/Bridge/Mcp/ClientToolset.php), [official PHP SDK](https://github.com/modelcontextprotocol/php-sdk).

## Validation and limits

```sh
composer validate --no-check-publish
composer check-platform-reqs
php bin/console lint:container
php tests/generation-regression.php
php tests/design-regression.php
```

Regressions cover required geometry, terrain bounds, prompt validation, missing-key failure, real SDK discovery and invocation against an **explicit fixture** server, Symfony Toolbox registration, repeated generation rejection, unique exports and HTML escaping. Those offline tests do not prove a live model call. Real runs and recordings must use `app:mario:design`, which has no fixture switch.

Geometry checks cover required fields, coordinates, dimensions and terrain bounds. They do **not** prove reachability, gameplay balance, playability or legal clearance. The HTML preview is a projection of the returned coordinates, not a game simulation. A bad provider response can fail validation; repeat a new request explicitly if needed. No hidden retry generates extra levels.

## Existing lower-level entry points

`php bin/mcp.php` exposes the existing `generate_original_level` tool to external MCP clients. Supply the provider key in the process environment. `php bin/mcp.php --fixture` is solely for explicit offline testing and returns `mode: fixture`.

`app:mario:orchestrate --prompt='...' --dry-run` directly calls the generation agent; its explicit `--fake` option is an offline fixture. This older command is separate from the agent → MCP client → server demonstration. The other multi-agent/Godot paths remain exploratory.

## Workshop and submission

[Darkwood Symfony AI MCP Hackathon](https://ai-mcp-symfony-hackathon-content.darkwood.com/) presents the verified path. Its canonical source and distribution are in `library/content-darkwood-ai-mcp-symfony-hackathon`, independent of Intent.

Suggested 90-second demonstration: show this quickstart and pinned packages, run a real design, observe MCP discovery/tool execution, open the resulting geometry and evidence, then show the approval boundary and playability limit. Record an actual run; do not present fixture tests or a replay as live execution. Submission status is reported separately; no registration or acceptance is implied by this repository.

License: MIT, as declared in `composer.json` and `LICENSE`.

## Optional existing DarkwoodGame observation

This secondary demonstration uses the existing Darkwood Portal MCP server; it does not replace the independent level-design submission. No action tool is exposed to its Symfony AI observer.

```sh
# Supply these through the process environment or your own ignored .env.local:
# OPENAI_API_KEY, DARKWOOD_GAME_API_KEY, DARKWOOD_GAME_JWT
php bin/console app:mario:observe-darkwood --endpoint=https://YOUR_AUTHORIZED_API_HOST/mcp
```

The server must expose the qualified `get_darkwood_state` read-only tool. It refuses player initialization through observation and enforces existing beta/premium/action permissions. The command reports only returned facts and cannot approve or execute a game action. It makes a real provider call and has no fixture fallback. For an isolated local Portal demonstration, use an HTTP loopback endpoint; `DARKWOOD_GAME_LOCAL_HOST` can supply its configured local Host header. Never use production game mutations to test the integration. See the [Portal contract and consent boundary](../darkwood-portal/docs/darkwood-mcp.md).

`nyholm/psr7` supplies the PSR-17 factories required by the official SDK HTTP transport, with Symfony's existing HTTP client as PSR-18 implementation. The existing AI and MCP versions are unchanged.

## Shared development shell

`flake.nix` and the native `shell.nix` entry point use the canonical Navi PHP
shell builder. This card shares the canonical Navi Nixpkgs revision and keeps its mbstring requirement.
Prefer `nix develop --file ./shell.nix` to avoid taking the monorepo as a flake
source. No application, vendor directory or secret is passed to the builder.
The submitted execution, Composer manifests and recorded evidence are unchanged.
