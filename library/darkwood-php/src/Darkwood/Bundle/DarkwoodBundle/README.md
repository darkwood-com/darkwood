# DarkwoodBundle

Shared Darkwood Identity links, assets and Twig templates for Symfony. The
development source is in the [Darkwood monorepo](https://github.com/darkwood-com/darkwood/tree/main/library/darkwood-php/src/Darkwood/Bundle/DarkwoodBundle).
The distribution repository contains the released package at its root.

```sh
composer require darkwood/darkwood-bundle:^0.1
APP_ENV=prod php bin/console asset-map:compile
```

Register `Darkwood\Bundle\DarkwoodBundle\DarkwoodBundle::class => ['all' => true]`
in `config/bundles.php` if Symfony Flex has not registered it. Enable the
Symfony translator to use the bundled locale labels.

```twig
<link rel="stylesheet" href="{{ asset('darkwood/styles/identity.css') }}">
{% include '@Darkwood/components/footer.html.twig' with {
    active_product: 'darkwaar',
    product_keys: ['darkwood'],
} only %}
```

`product_keys` is an ordered list of public product identifiers.
`portal_url` and `contact_url` override the canonical defaults.
The footer includes Contact and Legal mentions. Its Bootstrap utilities
align the links on the right and let them wrap on narrower screens.

Symfony installs the CSS, SVGs, fonts and JavaScript as public bundle assets.
This works with both Encore and AssetMapper. Applications can keep their own
styles and optionally load `darkwood/styles/tokens.css` or use the
[design reference](docs/design.md). Jost is included under its OFL license.

Consumers install released versions from Packagist and commit their lockfile.
Release and rollback instructions live in the canonical monorepo.

## Assets partagés

Le bundle enregistre son dossier `assets/` sous le préfixe AssetMapper `darkwood`.
Les chemins logiques sont `darkwood/styles/identity.css`,
`darkwood/styles/product.css`, `darkwood/styles/tokens.css`,
`darkwood/images/products/*.svg` et `darkwood/js/youtube.js`.
Utiliser `asset()` dans Twig et recompiler les assets à chaque mise à jour du bundle.
`product.css` charge les tokens et la police Jost. Les sites conservent leurs
feuilles spécialisées pour le lecteur vidéo, le studio audio et la cartographie.
Le fichier [design.md](docs/design.md) documente la provenance des logos.

Le catalogue `data/identity.json` définit l’ordre, les liens et les marques
du sélecteur Darkwood Identity. La documentation Hugo de Flow le lit
également pendant sa compilation statique.
