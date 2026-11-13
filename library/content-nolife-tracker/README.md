# Nolife Tracker

Nolife Tracker v0.1.1 is a terminal-first Symfony application for maintaining a local registry of AI provider accounts, subscriptions, billing arrangements, and product entitlements. It separates provider identities from commercial plans and never treats a local declaration as provider verification.

## Current features

- Static product catalog for OpenAI, Anthropic, Google, xAI, and Cursor.
- Multiple accounts per provider, with aliases, authentication metadata, monitoring preferences, and one provider-level default.
- Multiple subscriptions or billing arrangements per account.
- Free, recurring, pay-as-you-go, prepaid, enterprise, and unknown billing types.
- Explicit product entitlements with `unknown`, `declared`, or provider-reserved `verified` states.
- Guided terminal workflows and scriptable commands with versioned JSON output.
- Local, automatically migrated SQLite persistence without Doctrine.

Nolife Tracker does not contact AI providers in v0.1.1.

## Requirements and installation

The supported development environment is the repository Nix flake, which provides PHP 8.4, Composer, and PDO SQLite.

```bash
git clone git@github.com:matyo91/nolife-tracker.git
cd nolife-tracker
nix --extra-experimental-features 'nix-command flakes' develop
composer install
bin/nolife list
```

Run `bin/nolife` without arguments for the guided interface.

## Core model

```text
Provider
  ├── Products (static catalog)
  └── Accounts (provider identities)
        └── Subscriptions (plans or billing arrangements)
              └── Entitlements (access to catalog products)
```

A subscription may be free or have an unknown plan. A pay-as-you-go API arrangement is independent of a consumer subscription. An entitlement records access only: it does not imply a quota, remaining capacity, or successful authentication.

## Commands

```bash
# Provider catalog
bin/nolife providers:list
bin/nolife providers:show openai

# Provider accounts
bin/nolife accounts:list
bin/nolife accounts:add
bin/nolife accounts:show <id>
bin/nolife accounts:edit <id>
bin/nolife accounts:enable <id>
bin/nolife accounts:disable <id>
bin/nolife accounts:default <id>
bin/nolife accounts:remove <id>

# Subscriptions and billing arrangements
bin/nolife subscriptions:list
bin/nolife subscriptions:add
bin/nolife subscriptions:show <id>
bin/nolife subscriptions:edit <id>
bin/nolife subscriptions:remove <id>

# Product entitlements
bin/nolife subscriptions:products <id>
bin/nolife subscriptions:products:add <id>
bin/nolife subscriptions:products:remove <id>
```

Interactive commands prompt for missing values. Scripts must pass required values and `--no-interaction`:

```bash
bin/nolife accounts:add \
  --id=personal \
  --provider=openai \
  --alias=Personal \
  --auth-method=none \
  --monitor \
  --default=provider \
  --no-interaction

bin/nolife subscriptions:add \
  --id=chatgpt-plus \
  --account=personal \
  --name='ChatGPT Plus' \
  --billing-type=recurring \
  --status=active \
  --amount=23.00 \
  --currency=EUR \
  --interval=month \
  --no-interaction

bin/nolife subscriptions:products:add chatgpt-plus \
  --product=chatgpt \
  --access=declared \
  --no-interaction
```

Add an API arrangement separately with `--billing-type=pay_as_you_go`, then associate the provider's `api` product. Product associations are never inferred from plan names.

All list, show, add, and edit commands that expose structured data accept `--format=json`. JSON schema version 2 reflects the account/product separation introduced in v0.1.1.

## v0.1 migration

The database upgrades automatically and transactionally on first use. Existing account IDs, aliases, authentication metadata, monitoring preferences, timestamps, and provider defaults are retained.

Legacy account-to-product links are preserved in an unresolved migration record visible through `accounts:show`. They are not converted into subscriptions or entitlements because the old data cannot identify a plan or prove product access. Former product defaults are recorded only as historical migration metadata.

CLI compatibility changes:

- `accounts:add --product` is rejected with migration guidance.
- `accounts:default` is provider-scoped; `--scope=provider` remains accepted, while product defaults are rejected.
- `accounts:list --provider=<provider> --product=<product>` now filters through subscription entitlements.

## Local data

The SQLite database is outside the repository:

- macOS: `~/Library/Application Support/Nolife Tracker/nolife.sqlite`
- Linux: `$XDG_DATA_HOME/nolife-tracker/nolife.sqlite`, or `~/.local/share/nolife-tracker/nolife.sqlite`
- Windows: `%LOCALAPPDATA%\Nolife Tracker\nolife.sqlite`

Override it with an absolute directory for testing or portable use:

```bash
NOLIFE_DATA_DIR=/absolute/path bin/nolife accounts:list
```

## Security and trust states

- Never provide passwords, API key values, OAuth tokens, or raw cookies.
- Authentication references are non-secret locators such as CLI profile names, environment-variable names, or OS credential-store references.
- Account registration is not authentication verification.
- Billing details and `declared` entitlements are user-supplied metadata.
- Manual commands cannot create a `verified` entitlement. That state is reserved for future evidence-backed provider integrations.
- SQLite is local application storage, not a secret vault.

## Explicitly unsupported

- Authentication verification, login flows, and account switching.
- Automatic billing or subscription discovery.
- Quota, reset, token, credit, cost, or usage collection.
- Forecasting, optimization, notifications, and background services.
- Web, desktop, mobile, cloud synchronization, teams, and credential sharing.

## Development checks

Run checks inside the Nix shell:

```bash
composer validate --strict
php bin/console lint:container
php bin/console about
php bin/console cache:clear
bin/nolife list
```

Use a temporary absolute `NOLIFE_DATA_DIR` for workflow checks. Do not create demonstration records in the default user database.

## Commit convention

Use `<gitmoji> <imperative description>` with a Unicode Gitmoji and no Conventional Commit prefix, for example `✨ Add provider integration`.

## License

Nolife Tracker is released under the [MIT License](LICENSE).
