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

## Thème Sass

Maintenir le thème dans `assets/sass/`. `_variables.scss` configure Bootstrap
avant sa compilation. Les autres partiels déclarent les palettes claire,
sombre et sépia, la typographie Jost, la disposition et les composants Identity.
Les styles spécifiques à un produit restent dans son application.

Le lockfile npm fixe Bootstrap 5.3.9, comme la boutique Uniflow et Portal,
et Dart Sass 1.77.6. Depuis la racine du bundle, dans le shell Nix du projet
avec Node.js 22, lancer les commandes suivantes.

```sh
npm ci
npm run build:css
npm run check:css
```

Committer les sources et la sortie du compilateur. Le contrôle recompilé
compare les fichiers octet par octet. La CI du monorepo et la publication
du proxy exécutent ce même contrôle. Sass et Node.js servent à la maintenance
du bundle. Les consommateurs Composer chargent le CSS distribué.

Une page qui utilise le Bootstrap du bundle peut charger
`asset('darkwood/styles/darkwood.css')` avant son CSS applicatif.
Une application qui fournit déjà son Bootstrap, comme Portal ou Uniflow,
charge `asset('darkwood/styles/darkwood-shared.css')`. Cette sortie utilise
les mêmes partiels Sass et comprend les tokens, Jost et Darkwood Identity.
Les anciens chemins `product.css`, `identity.css` et `tokens.css` sont des
sorties de compatibilité pour les consommateurs versionnés.

AssetMapper versionne les feuilles de style et la police. Le site Hugo
de Flow peut copier les fichiers compilés et les ressources associées vers
son dossier statique. Les fichiers Sass restent des sources de maintenance.
