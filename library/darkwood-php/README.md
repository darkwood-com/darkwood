<p align="center"><a href="https://darkwood.com" target="_blank">
    <img src="https://darkwood.com/logo.svg" width="auto" height="128px" alt="Darkwood Logo">
</a></p>

Development repository for [Darkwood](https://github.com/darkwood-com) PHP libraries and Symfony bundles.

Navi, Flow, MediaBundle, and IaExceptionBundle share a global version line. One tag
(`v8.1.0`, `v8.1.1`, …) applies to the monorepo and to every satellite
repository published on Packagist.

- Default branch: `8.1`  
- Changelog: [CHANGELOG-8.1.md](CHANGELOG-8.1.md)
- Upgrading from pre-unified tags: [UPGRADE-8.1.md](UPGRADE-8.1.md)

## Packages

| Path | Composer name | Public repository |
|------|---------------|-------------------|
| `src/Darkwood/Component/Navi` | `darkwood/navi` | [darkwood-com/darkwood-php-component-navi](https://github.com/darkwood-com/darkwood-php-component-navi) |
| `src/Darkwood/Component/Flow` | `darkwood/flow` | [darkwood-com/darkwood-php-component-flow](https://github.com/darkwood-com/darkwood-php-component-flow) |
| `src/Darkwood/Bundle/MediaBundle` | `darkwood/media-bundle` | [darkwood-com/darkwood-php-bundle-media](https://github.com/darkwood-com/darkwood-php-bundle-media) |
| `src/Darkwood/Bundle/IaExceptionBundle` | `darkwood/ia-exception-bundle` | [darkwood-com/darkwood-php-bundle-ia-exception](https://github.com/darkwood-com/darkwood-php-bundle-ia-exception) |
| `src/Darkwood/Bundle/DarkwoodBundle` | `darkwood/darkwood-bundle` | [darkwood-com/darkwood-bundle](https://github.com/darkwood-com/darkwood-bundle) |

Package-specific documentation, installation, and usage live in each package `README.md`.
DarkwoodBundle follows independent SemVer. See the [release and rollout guide](docs/darkwood-bundle.md).

## Requirements

- PHP **8.5+** for Navi, Flow, MediaBundle, and IaExceptionBundle
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

Install each package from Packagist and commit the application's Composer lockfile.

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

GitHub Actions at `.github/workflows/ci.yml` runs on every pull request and push to `8.1`:

- **Fabbot** — coding standards and license headers
- **QA** — only for packages touched by the PR (PHP CS Fixer, PHPStan, PHPUnit, and Symfony LSP check for bootable apps per `packages.json`)

On push to `8.1`, all packages are tested. Changing files under `.github/` triggers a full run.

Run the same checks locally for one package:

```bash
.github/ci-run-package.sh navi
```

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

## License

MIT — see [LICENSE](LICENSE).
