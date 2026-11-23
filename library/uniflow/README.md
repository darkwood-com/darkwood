Uniflow
-------

<p align="center">
  <a href="https://uniflow.io">
    <img src="library/uniflow-client/src/assets/images/logo.svg" height="128" alt="Uniflow">
  </a>
</p>

## Introduction

[Uniflow](https://uniflow.io/) is Darkwood’s workflow automation platform. Use it to design, organize and run workflows, including recurring tasks. Browse published examples in the [public flow catalogue](https://uniflow.io/flows).

[Darkwood Flow](https://flow.darkwood.com/) is the PHP library for composing and running asynchronous pipelines in code.

## Documentation

The official Uniflow documentation can be found under: [https://uniflow.io/docs](https://uniflow.io/docs)

Additional information and example workflows on the uniflow.io website: [https://uniflow.io/flows](https://uniflow.io/flows)

## Building shop assets

Run these commands in the existing Nix environment, after installing Composer
dependencies. The npm lockfile includes packages from Composer's `vendor/`
directory.

```sh
npm ci --ignore-scripts
npm run build:prod -- --config-name app.shop
php bin/console sass:build
php bin/console asset-map:compile
```

The shop Sass loads Bootstrap through the `bootstrap-shop` npm alias.
Its version is pinned separately from the Bootstrap dependencies of Sylius and
Tabler. Source packages stay in `node_modules/` and the public shop uses the
compiled CSS.

## Domain BDD tests

Run `behat.dist.php` with PHP 8.5 and Behat 4.0.0. The PHP 8.5 exception
applies to BDD tooling because this Behat release excludes PHP 8.6.
Use the existing development dependencies and a fresh PostgreSQL 15 database
with the project's Doctrine schema and test configuration. Set
`APP_ENV=test` and `DATABASE_URL` to that database before running the tests.
The product and coupon scenarios exercise foreign keys and row locking, so
SQLite cannot validate all six suites.

```sh
php vendor/bin/behat --config=behat.dist.php
php vendor/bin/behat --config=behat.dist.php --suite=domain_managing_shipping_methods
```

The PHP configuration runs the Sylius domain scenarios. The historical browser
configuration remains in `behat.yml.dist` and requires its browser drivers.

## Support

If you have problems or questions go to our contact page, we will then try to help you as soon as possible:

[https://uniflow.io/contact](https://uniflow.io/contact)

## License

Uniflow is [fair-code](http://faircode.io) distributed under [AGPL-3.0 License with Commons Clause](https://github.com/uniflow-io/uniflow/blob/1.x/LICENSE.md) license.

Additional information about license can be found in the [FAQ](https://uniflow.io/docs/faq#which-license-does-uniflow-use) page.
