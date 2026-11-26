<p align="center"><a href="https://darkwood.com" target="_blank">
    <img src="https://darkwood.com/logo.svg" width="auto" height="128px" alt="Darkwood Logo">
</a></p>

Development repository for [Darkwood](https://github.com/darkwood-com) PHP libraries and Symfony bundles.

The monorepo's `main` is the source for all five independently distributed
packages. The matching GitHub proxy branches provide `dev-main` on Packagist.
Stable tags follow a validated Symfony release. See the
[publication guide](docs/distribution.md) for compatibility, maintenance and
application rollout.

- Changelog: [CHANGELOG-8.1.md](CHANGELOG-8.1.md)
- Upgrading historical releases: [UPGRADE-8.1.md](UPGRADE-8.1.md)

## Packages

| Path | Composer name | Public repository |
|------|---------------|-------------------|
| `src/Darkwood/Component/Navi` | `darkwood/navi` | [darkwood-com/darkwood-php-component-navi](https://github.com/darkwood-com/darkwood-php-component-navi) |
| `src/Darkwood/Component/Flow` | `darkwood/flow` | [darkwood-com/darkwood-php-component-flow](https://github.com/darkwood-com/darkwood-php-component-flow) |
| `src/Darkwood/Bundle/MediaBundle` | `darkwood/media-bundle` | [darkwood-com/darkwood-php-bundle-darkwood-media-bundle](https://github.com/darkwood-com/darkwood-php-bundle-darkwood-media-bundle) |
| `src/Darkwood/Bundle/IaExceptionBundle` | `darkwood/ia-exception-bundle` | [darkwood-com/darkwood-php-bundle-darkwood-ia-exception-bundle](https://github.com/darkwood-com/darkwood-php-bundle-darkwood-ia-exception-bundle) |
| `src/Darkwood/Bundle/DarkwoodBundle` | `darkwood/darkwood-bundle` | [darkwood-com/darkwood-php-bundle-darkwood-bundle](https://github.com/darkwood-com/darkwood-php-bundle-darkwood-bundle) |

Package-specific documentation, installation, and usage live in each package `README.md`.
See the [DarkwoodBundle integration guide](docs/darkwood-bundle.md).

## Naming new bundles

| Item | Convention |
|---|---|
| Composer package | `darkwood/<package-name>` |
| GitHub distribution repository | `darkwood-com/darkwood-php-bundle-<bundle-name>` |
| Development source | `library/darkwood-php/src/Darkwood/Bundle/` in the monorepo |
| Distribution and versions | GitHub and Packagist, with Git tags on the distribution repository |

Publish all five packages from the monorepo through the mirror workflow.
The common release script verifies the proxy tree before creating a tag.

## Requirements

- PHP **8.4+** for DarkwoodBundle and **8.5+** for Navi, MediaBundle, IaExceptionBundle, and Flow 8.1
- PHP **8.6+** for Flow `dev-main`
- [Composer](https://getcomposer.org)
- OpenSwoole extension for Navi and Flow CI runtime tests

## Local development

Work inside the package directory you are changing:

```bash
cd src/Darkwood/Component/Navi   # or Flow, MediaBundle, IaExceptionBundle
composer install
```

### Quality assurance (per package)

Execute targets via `make`:

```bash
make php-cs-fixer
make phpstan
make phpunit
make symfony-lsp
```

### Application dependencies

Install packages from Packagist and commit the application's Composer lockfile.
Use a stable version such as `8.1.12` for external consumers. Internal
applications can opt into `dev-main` package by package.

## Repository layout

```text
darkwood/
└── .github/           # CI, fabbot, PR template, packages manifest
├── src/
│   └── Darkwood/
│       ├── Component/     # Standalone libraries (Navi, Flow)
│       └── Bundle/        # Symfony bundles
├── link               # Symlink darkwood/* packages from vendor to this monorepo
├── splitsh.json       # Monorepo path → satellite Git repository mapping
├── CHANGELOG-8.1.md   # Unified release changelog
├── RELEASING.md       # Release process for maintainers
├── UPGRADE-8.1.md     # Upgrade notes between unified versions
├── CONTRIBUTING.md
```

### Continuous integration

See the [Navi PHP quality guide](docs/php-quality.md) for the reusable
workflow, Castor conventions and dynamic monorepo matrix.

The monorepo workflow
[`Sync distribution mirrors`](../../.github/workflows/sync-distribution-mirrors.yml)
validates and synchronizes package subtrees on changes to `main`. The workflow
checks the complete package tree, including tracked files listed in a
`.gitignore`. It does not create release tags.

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

## License

MIT — see [LICENSE](LICENSE).
