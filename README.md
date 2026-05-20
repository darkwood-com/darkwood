<p align="center"><a href="https://darkwood.com" target="_blank">
    <img src="https://darkwood.com/logo.svg" width="auto" height="128px" alt="Darkwood Logo">
</a></p>

Development repository for [Darkwood](https://github.com/darkwood-com) PHP libraries and Symfony bundles.
Each package lives under `src/` and is published independently on Packagist / GitHub.

**Default branch:** `1.x`

## Packages

| Path | Composer name | Public repository |
|------|---------------|-------------------|
| `src/Component/Navi` | `darkwood/navi` | [darkwood-com/navi](https://github.com/darkwood-com/navi) |
| `src/Component/Flow` | `darkwood/flow` | [darkwood-com/flow](https://github.com/darkwood-com/flow) |
| `src/Bundle/MediaBundle` | `darkwood/media-bundle` | [darkwood-com/media-bundle](https://github.com/darkwood-com/media-bundle) |
| `src/Bundle/IaExceptionBundle` | `darkwood/ia-exception-bundle` | [darkwood-com/ia-exception-bundle](https://github.com/darkwood-com/ia-exception-bundle) |

Package-specific documentation, installation, and usage live in each package `README.md`.

## Requirements

- PHP **8.5+** for Navi, Flow, MediaBundle, and IaExceptionBundle
- [Composer](https://getcomposer.org/)
- OpenSwoole extension for Navi and Flow CI runtime tests

## Local development

Work inside the package directory you are changing:

```bash
cd src/Component/Navi   # or Flow, MediaBundle, IaExceptionBundle
composer install
```

### Quality assurance (per package)

Execute targets via `make`:

```bash
make php-cs-fixer
make phpstan
make phpunit
```

### Link monorepo packages into another project

After `composer install` in a consuming application, symlink local clones from this monorepo:

```bash
./link /path/to/your/symfony-app
```

Use `./link --rollback /path/to/your/symfony-app` to restore vendor copies, then run `composer install` in the app.

See [CONTRIBUTING.md](CONTRIBUTING.md) for the full contribution workflow.

## Repository layout

```text
darkwood/
├── src/
│   ├── Component/     # Standalone libraries (Navi, Flow)
│   └── Bundle/        # Symfony bundles
├── link               # Symlink darkwood/* packages from vendor to this monorepo
├── splitsh.json       # Monorepo path → satellite Git repository mapping
├── CONTRIBUTING.md
└── .github/           # CI, fabbot, PR template, packages manifest
```

### Continuous integration

GitHub Actions at `.github/workflows/ci.yml` runs on every pull request and push to `1.x`:

- **Fabbot** — coding standards and license headers
- **QA** — only for packages touched by the PR (PHP CS Fixer, PHPStan, PHPUnit per `packages.json`)

On push to `1.x`, all packages are tested. Changing files under `.github/` triggers a full run.

Run the same checks locally for one package:

```bash
.github/ci-run-package.sh navi
```

## Split repositories

Published packages are mirrored to standalone GitHub repositories (see `splitsh.json`).
Develop here in `darkwood-com/darkwood`, then sync subtrees to the satellite repos before tagging releases.

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

## License

MIT — see [LICENSE](LICENSE).
