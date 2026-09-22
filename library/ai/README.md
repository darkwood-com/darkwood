# Darkwood AI

Darkwood AI is a complementary extension ecosystem for [Symfony AI](https://github.com/symfony/ai).

It does **not** replace, fork, or reimplement Symfony AI. Packages in this
repository depend on Symfony AI components and add integrations that Symfony AI
does not currently provide, or that Darkwood maintains independently.

```text
darkwood/ai
    |
    +-- darkwood/ai-jev-platform
            |
            +-- symfony/ai-platform
            |
            +-- TypeSafe Jev API
```

Use Symfony AI for platforms, agents, stores, and the AI Bundle. Use Darkwood AI
when you need an extra bridge on top of that infrastructure.

## Requirements

* PHP 8.2 or later
* [Composer](https://getcomposer.org/)
* [symfony/ai-platform](https://github.com/symfony/ai) `^0.13`

## Packages

### `darkwood/ai-jev-platform`

TypeSafe Jev (System One) platform bridge. Jev evaluates typed questions
against a state and returns structured answers with probabilities.

```bash
composer require darkwood/ai-jev-platform
```

```php
use Darkwood\AI\Platform\Bridge\Jev\Factory;
use Darkwood\AI\Platform\Bridge\Jev\Output\EvaluationResult;

$platform = Factory::createPlatform($_ENV['TYPESAFE_API_KEY']);

$result = $platform->invoke('jev-latest', 'I was charged twice.', [
    'questions' => [
        'urgent' => [
            'type' => 'noul',
            'instructions' => 'Escalate to a human now?',
        ],
    ],
])->asObject();

assert($result instanceof EvaluationResult);
$result->getNoul('urgent');
```

See [src/platform/src/Bridge/Jev/README.md](src/platform/src/Bridge/Jev/README.md)
for authentication, typed questions, Symfony service configuration, and
limitations.

```bash
php examples/jev/evaluate.php --mock
```

## Development

```bash
composer update
vendor/bin/phpunit
vendor/bin/phpstan analyse --memory-limit=512M
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php
```

This monorepo uses a Composer path repository for local packages. Packages
are not published to Packagist yet.

## License

This project is licensed under the [MIT License](LICENSE).

Adapted Jev client files retain their Symfony copyright notices. See [NOTICE](NOTICE).

## Authors

Mathieu Ledru <matyo91@gmail.com>
