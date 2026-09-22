# Darkwood AI

Darkwood AI is an independently versioned PHP component repository for the Darkwood ecosystem.

It relates to Darkwood the same way Symfony AI relates to Symfony: installable AI packages that integrate with Darkwood applications, without living inside the Darkwood framework monorepo.

This repository does **not** depend on `symfony/ai-platform`. Platform contracts are first-party (`Darkwood\AI\Platform`).

## Current components

| Package | Composer name | Role |
|---------|---------------|------|
| Platform | `darkwood/ai-platform` | Invoke API, models, catalog, HTTP results, token usage |
| Jev | `darkwood/ai-jev-platform` | TypeSafe System One bridge (`noul`, `choice`, `score`) |

There is no chat, tool-calling, streaming, embeddings, or agent layer yet.

## Requirements

- PHP 8.5+
- `symfony/http-client` ^8.1 (Jev)

## Installation

Packages are not on Packagist yet. From this clone:

```bash
composer require darkwood/ai-jev-platform
```

The workspace `composer.json` uses path repositories for local development only. Published package metadata does not include filesystem paths.

```json
{
    "require": {
        "darkwood/ai-jev-platform": "^0.1"
    }
}
```

## Jev usage

Jev evaluates typed questions against a `state`. It is not a chat-completion model.

```php
use Darkwood\AI\Platform\Bridge\Jev\Factory;
use Darkwood\AI\Platform\Bridge\Jev\Output\EvaluationResult;

$platform = Factory::createPlatform($_ENV['TYPESAFE_API_KEY']);

$result = $platform->invoke('jev-latest', 'Customer: I was charged twice.', [
    'questions' => [
        'urgent' => [
            'type' => 'noul',
            'instructions' => 'Escalate to a human now?',
        ],
        'topic' => [
            'type' => 'choice',
            'instructions' => 'What is the issue about?',
            'criteria' => [
                'billing' => 'money problems',
                'bug' => 'broken product',
            ],
        ],
    ],
])->asObject();

assert($result instanceof EvaluationResult);
$result->getNoul('urgent');
$result->getChoice('topic');
$result->getProbabilities('topic');
```

Catalog models: `jev-latest`, `jev-preview`, `jev-1.13.0`. Capability: `INPUT_TEXT` only.

Create a key at the [TypeSafe console](https://console.typesafe.ai/settings/keys) and expose it as `TYPESAFE_API_KEY`.

See `examples/jev/evaluate.php`.

```bash
php examples/jev/evaluate.php --mock
TYPESAFE_API_KEY=... php examples/jev/evaluate.php
```

## Development and testing

```bash
composer update
vendor/bin/phpunit
vendor/bin/phpstan analyse --memory-limit=512M
vendor/bin/php-cs-fixer fix
```

CI runs PHP 8.5 only.

## License and attribution

MIT. See `LICENSE` and `NOTICE`.

The Jev bridge was adapted from work originally prepared for Symfony AI. Adapted files keep their Symfony MIT copyright notices.

## Current limitations

- Single-provider `Platform` only (no router, no multi-provider)
- No Symfony bundle / Darkwood integration package
- No chat, tools, streaming, embeddings, or agents
- Jev is the only bridge
- Packages are not published to Packagist yet
