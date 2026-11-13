# Secret keys as application data

A small Symfony 8.2 experiment: accept a user-provided AI API key, encrypt it
with the experimental KeyManagement component (Sodium locally, AWS KMS as the
production/reference backend), persist only ciphertext, then decrypt at
execution time and hand the key to a Darkwood Navi action that calls Symfony AI.

This repository answers one question:

> How can a Symfony application safely accept, store and use user-provided AI
> API keys while keeping provider execution, security and cost observable?

**KeyManagement is experimental.** Its API may change in minor Symfony 8.2 versions.

## Requirements

- PHP 8.5+
- `ext-sodium` (local backend)
- Composer
- SQLite

`darkwood/navi` is required from the local monorepo path
`../../darkwood/src/Darkwood/Component/Navi`. Packagist `^1.0` still pins
Symfony 8.0 and cannot install on this 8.2 skeleton.

## Run

```bash
composer install
cp .env.example .env.local
# Generate a private Sodium key (do not reuse the committed demo key):
php -r 'require "vendor/autoload.php"; echo Symfony\Component\KeyManagement\Base64UrlSafe::encode(sodium_crypto_aead_xchacha20poly1305_ietf_keygen()), PHP_EOL;'
# Put the value in .env.local as DEV_KMS_KEY=...

php bin/console doctrine:migrations:migrate --no-interaction
symfony serve
```

Open `/`. Add a connection. The API key field is a password input and is never
redisplayed. SQLite lives at `var/data.db`.

```bash
sqlite3 var/data.db "SELECT id, label, provider, kms_key_id, ciphertext_blob FROM ai_connection;"
```

The blob is base64 of a KeyManagement `Ciphertext`. It must not contain the key
you typed.

## Switch key backends

| Environment | DSN shape | Who holds the master key |
|-------------|-----------|--------------------------|
| `dev` / `test` | `sodium://?keys[app-key]=%env(DEV_KMS_KEY)%` | the application (`DEV_KMS_KEY`) |
| `prod` | `aws-kms://default?region=%env(AWS_KMS_REGION)%` | AWS KMS |

The application code always injects `EncrypterInterface` / `DecrypterInterface`.
Set `KMS_BACKEND` to the label shown in the UI (`Sodium` or `AWS KMS`). Do not
put a DSN or key material in `KMS_BACKEND`.

AWS KMS was configured, not live-tested in this repository.

## Probe a live provider

```bash
ANTHROPIC_API_KEY=... php bin/console app:probe-connection
```

The command encrypts, decrypts, runs Navi, and prints token/cost metadata.
It never prints the key.

## Tests

```bash
composer test
```

## What this is not

Not a production SaaS. No login, no billing system, no second AI SDK.
Navi is the execution shell. Symfony AI is the provider client.
KeyManagement is the encryption abstraction.

See `study.md` for measurements and `article.md` for the write-up.
