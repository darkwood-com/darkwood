# DarkwoodBundle

Shared Darkwood Identity links, assets and Twig templates for Symfony. The
development source is in the [Darkwood monorepo](https://github.com/darkwood-com/darkwood/tree/main/library/darkwood-php/src/Darkwood/Bundle/DarkwoodBundle).
The distribution repository contains the released package at its root.

```sh
composer require darkwood/darkwood-bundle:^0.1
php bin/console assets:install public
```

Register `Darkwood\Bundle\DarkwoodBundle\DarkwoodBundle::class => ['all' => true]`
in `config/bundles.php` if Symfony Flex has not registered it. Enable the
Symfony translator to use the bundled locale labels.

```twig
<link rel="stylesheet" href="{{ asset('bundles/darkwood/identity.css') }}">
{% include '@Darkwood/components/footer.html.twig' with {
    active_product: 'darkwaar',
    product_keys: ['darkwood'],
} only %}
```

`product_keys` is an ordered list of public product identifiers.
`portal_url` and `contact_url` override the canonical defaults.
Use `show_contact: false` when the surrounding footer already has its own
contact link. The snippet fits inside the host application's footer.

Symfony installs the CSS, SVGs, fonts and JavaScript as public bundle assets.
This works with both Encore and AssetMapper. Applications can keep their own
styles and optionally load `bundles/darkwood/tokens.css` or use the
[design reference](docs/design.md). Jost is included under its OFL license.

Consumers install released versions from Packagist and commit their lockfile.
Release and rollback instructions live in the canonical monorepo.
