# Nolife Tracker

Nolife Tracker v0.1 is a terminal-first Symfony application for maintaining a local registry of AI provider accounts. It supports multiple providers, products, and accounts without claiming that a locally registered account is authenticated.

## Current features

- Static catalog for OpenAI, Anthropic, Google, xAI, and Cursor products.
- Explicit provider capability states: available, not implemented, unsupported, and unknown.
- Multiple local accounts for the same provider and product.
- Account aliases, authentication-method metadata, monitoring preferences, and provider/product defaults.
- Guided terminal workflows and scriptable commands.
- Local SQLite persistence with no Doctrine dependency.
- Versioned JSON output for provider and account list/show/add/edit commands.

Nolife Tracker v0.1 does not contact AI providers.

## Requirements

- Nix with the `nix-command` and `flakes` features enabled.
- Or, for a manual runtime: PHP 8.4+, Composer, PDO, and PDO SQLite.

The supported development environment is the repository's Nix flake. It provides PHP 8.4, Composer, and SQLite support without installing system packages.

## Installation

```bash
git clone git@github.com:matyo91/nolife-tracker.git
cd nolife-tracker
nix --extra-experimental-features 'nix-command flakes' develop
composer install
bin/nolife list
```

Run `bin/nolife` without arguments to open the guided menu.

## Provider commands

```bash
bin/nolife providers:list
bin/nolife providers:list --format=json
bin/nolife providers:show openai
bin/nolife providers:show openai --product=codex --format=json
```

Capability states describe Nolife Tracker's integration status. `not_implemented` does not mean a provider lacks the capability, and `unknown` is not treated as `unsupported`.

## Account commands

```bash
bin/nolife accounts:list
bin/nolife accounts:add
bin/nolife accounts:show <id>
bin/nolife accounts:edit <id>
bin/nolife accounts:enable <id>
bin/nolife accounts:disable <id>
bin/nolife accounts:default <id> --scope=provider
bin/nolife accounts:default <id> --scope=product
bin/nolife accounts:remove <id>
```

Interactive commands ask for missing values. In scripts, pass every required value and `--no-interaction`:

```bash
bin/nolife accounts:add \
  --id=openai-codex-work \
  --provider=openai \
  --product=codex \
  --alias=Work \
  --auth-method=existing_cli \
  --auth-reference=work-profile \
  --monitor \
  --default=product \
  --no-interaction \
  --format=json
```

For an official API account, `--auth-reference` accepts the name of an environment variable, such as `OPENAI_API_KEY`, never the key value.

Removal in a non-interactive process requires `--force`. It deletes only Nolife Tracker's local record and default assignments.

## Local data

The SQLite database is stored outside the repository:

- macOS: `~/Library/Application Support/Nolife Tracker/nolife.sqlite`
- Linux: `$XDG_DATA_HOME/nolife-tracker/nolife.sqlite`, or `~/.local/share/nolife-tracker/nolife.sqlite`
- Windows: `%LOCALAPPDATA%\Nolife Tracker\nolife.sqlite`

Set an absolute `NOLIFE_DATA_DIR` to override the directory:

```bash
NOLIFE_DATA_DIR=/absolute/path bin/nolife accounts:list
```

Back up the application by copying `nolife.sqlite` while Nolife Tracker is not writing to it. To delete all local Nolife Tracker state, remove that database and its containing Nolife Tracker data directory after confirming the path.

## Security model

- Account registration is local metadata; it is not authentication.
- Passwords, API key values, OAuth tokens, and raw session cookies must never be supplied or stored.
- Authentication references are non-secret locators such as CLI profile names, environment-variable names, or OS credential-store references.
- List output omits authentication references; account inspection shows only a redacted length.
- The data directory and database use owner-only permissions on Unix-like systems where supported.
- v0.1 does not execute provider CLIs, inspect existing credentials, or contact provider APIs.

SQLite is local application storage, not a secret vault. Do not put credentials in aliases or authentication references.

## Explicitly unsupported in v0.1

- Authentication verification, login flows, and account switching.
- Quota, reset, token, billing, credit, or historical usage collection.
- Forecasting, subscription optimization, and notifications.
- Background services, web/desktop/mobile interfaces, and cloud synchronization.
- Teams, account sharing, provider plugins, and marketplaces.

## Development checks

Run checks inside the Nix shell:

```bash
composer validate --strict
php bin/console lint:container
php bin/console about
php bin/console cache:clear
bin/nolife list
```

Use `NOLIFE_DATA_DIR` pointing to a temporary absolute directory for manual workflow verification. Do not create demonstration accounts in the default user database.

## License

Nolife Tracker is released under the [MIT License](LICENSE).
