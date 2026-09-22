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
```

Use Symfony AI for platforms, agents, stores, and the AI Bundle. Use Darkwood AI
when you need an extra bridge on top of that infrastructure.

## Requirements

* PHP 8.2 or later
* [Composer](https://getcomposer.org/)
* The relevant [Symfony AI](https://github.com/symfony/ai) packages

## Packages

The first package will be [`darkwood/ai-jev-platform`](https://github.com/darkwood-com/ai),
a TypeSafe Jev platform bridge for `symfony/ai-platform`.

## License

This project is licensed under the [MIT License](LICENSE).

## Authors

Mathieu Ledru <matyo91@gmail.com>
