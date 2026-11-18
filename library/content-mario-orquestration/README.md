# Darkwood original level generator

Symfony AI generates an original platformer level as JSON. The official PHP MCP SDK exposes `generate_original_level` over stdio. This submission path uses one named agent, `level_generator`. The existing multi-agent and Godot experiments remain exploratory.

## Install

Use PHP 8.5 with ctype, iconv, mbstring and the extensions required by Composer. From the public monorepo, enter `library/content-mario-orquestration`.

```sh
composer install --no-scripts
composer validate --no-check-publish
composer check-platform-reqs
```

The committed lock pins Symfony AI to 1.0.0-BETA1 and mcp/sdk to 0.10.0. Composer plugins generate the Symfony runtime loader. Automatic application scripts are disabled during installation for reproducibility.

Provide `OPENAI_API_KEY` through your process environment or your own ignored `.env.local`. Never commit a key. The real generator calls OpenAI using gpt-5-mini and requires network access and provider credit.

## Run

```sh
php bin/console app:mario:orchestrate --prompt='Create an original forest platformer level as json' --dry-run
php bin/mcp.php
```

The CLI displays the generated JSON and geometry validation. Omit `--dry-run` to export to `var/mario-orchestration/level.json`. The MCP process uses newline-delimited JSON-RPC over stdio. Configure the host to launch `php bin/mcp.php` from this directory with `OPENAI_API_KEY` in its environment. It offers one tool with a prompt of 1–2000 characters and returns the validated level without writing files.

An absent key or invalid provider response fails explicitly. The MCP process reads its key from the environment. The CLI also loads Symfony environment files.

For an explicit offline fixture only, use `php bin/console app:mario:orchestrate --fake --dry-run`. This fixture proves local execution only. Real generation is required for the hackathon video.

## Scope and limits

Validation checks required fields, dimensions, coordinates and terrain bounds. It does not establish that the level is playable. The Godot importer and scene require further work before a playable game can be demonstrated. The alternate deterministic level command is separate from the real generation path.

This project is licensed under MIT, as declared in LICENSE and composer.json. Nintendo assets and level copies must not be included.

## 90-second demonstration

- 0–15 seconds — Show the existing project, the locked Symfony AI beta and official MCP SDK versions.
- 15–30 seconds — Initialize the MCP server and list its single generation tool.
- 30–65 seconds — Call the tool with an original level request and show the real response, named agent and validation.
- 65–80 seconds — Show coordinates, terrain and goal in the resulting JSON. Explain the geometry checks and playability limit.
- 80–90 seconds — Show installation instructions and the source paths implementing AI generation and the SDK tool.

Record only an actual successful run. Review terminal output for credentials before recording. Public publication and final submission require owner approval.
