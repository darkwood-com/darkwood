# Distribution des packages Darkwood PHP

Le code se développe sur `main` du [monorepo Darkwood](https://github.com/darkwood-com/darkwood).
Le manifeste [distribution-cards.json](../../../.github/distribution-cards.json)
définit la source, le dépôt de distribution et les contrôles de chaque package.

| Package Packagist | Source dans `src/Darkwood` | Proxy GitHub | PHP et Symfony |
|---|---|---|---|
| [darkwood/darkwood-bundle](https://packagist.org/packages/darkwood/darkwood-bundle) | `Bundle/DarkwoodBundle` | [darkwood-php-bundle-darkwood-bundle](https://github.com/darkwood-com/darkwood-php-bundle-darkwood-bundle) | PHP ≥ 8.4, Symfony ^7.4 ou ^8.1 |
| [darkwood/ia-exception-bundle](https://packagist.org/packages/darkwood/ia-exception-bundle) | `Bundle/IaExceptionBundle` | [darkwood-php-bundle-darkwood-ia-exception-bundle](https://github.com/darkwood-com/darkwood-php-bundle-darkwood-ia-exception-bundle) | PHP ≥ 8.5, Symfony ^8.1 et Symfony AI ^0.12.0 |
| [darkwood/media-bundle](https://packagist.org/packages/darkwood/media-bundle) | `Bundle/MediaBundle` | [darkwood-php-bundle-darkwood-media-bundle](https://github.com/darkwood-com/darkwood-php-bundle-darkwood-media-bundle) | PHP ≥ 8.5, Symfony ^8.1, Flow ^8.1.6 |
| [darkwood/flow](https://packagist.org/packages/darkwood/flow) | `Component/Flow` | [darkwood-php-component-flow](https://github.com/darkwood-com/darkwood-php-component-flow) | PHP ≥ 8.5 sur `8.1`, PHP ^8.6 sur `dev-main`, Symfony ^8.1 |
| [darkwood/navi](https://packagist.org/packages/darkwood/navi) | `Component/Navi` | [darkwood-php-component-navi](https://github.com/darkwood-com/darkwood-php-component-navi) | PHP ≥ 8.5, Symfony ^8.1 |

## Synchroniser `main`

1. Valider les changements du composant et pousser sur `main` du monorepo.
2. Contrôler le job du composant dans
   [Sync distribution mirrors](https://github.com/darkwood-com/darkwood/actions/workflows/sync-distribution-mirrors.yml).
   Pour relancer un seul package, choisir son identifiant dans `workflow_dispatch`
   et désactiver `dry_run` pour pousser. Le workflow utilise la GitHub App
   existante lors du push.
3. Vérifier que l’arbre Git de `main` du proxy est identique au sous-répertoire
   de la révision source et que Packagist expose la même référence dans
   `dev-main`. Le webhook GitHub actualise Packagist.

## Publier un tag stable

Consulter le [calendrier Symfony](https://symfony.com/releases) ou son
[JSON](https://symfony.com/releases.json). Au 26 novembre 2026, Symfony
`8.1.12` est stable. La LTS en cours est `7.4.24`, avec des correctifs
jusqu’en novembre 2028 et des correctifs de sécurité jusqu’en novembre 2029.
Symfony `8.2.0-RC1` est une préversion. Une branche de maintenance Darkwood
répond à un besoin de compatibilité identifié.

Valider les packages sur la version choisie. Les manifestes conservent des
contraintes de compatibilité plutôt qu’un patch Symfony exact. Depuis la
racine du monorepo, après la synchronisation et les tests :

```bash
bash .github/release-php-package.sh darkwood/darkwood-bundle v8.1.12 <revision-source-validee>
```

Le script vérifie l’arbre du proxy et pousse uniquement le tag annoté. Il
refuse un tag existant dirigé vers un autre commit. Vérifier le tag et son
apparition sur Packagist. Pour un correctif Darkwood urgent entre deux
versions Symfony, utiliser un quatrième segment numérique, par exemple
`v8.1.12.1`, et documenter le correctif. Les commits ordinaires sur
`main` restent consommables sans tag.

Flow `v8.1.12` a été publié depuis l’ancienne branche `8.1` et le commit
source `ee5c052249a51c0b3ff80024d790f8fa92a8c8c1`. Flow `dev-main`
contient les évolutions PHP 8.6 et ne satisfait pas la contrainte
`^8.1.6` de MediaBundle. Les branches retirées restent récupérables
depuis les tags GitHub `archive/packagist-2026-11-26/*`. Les seules
branches conservées sur les proxies sont `main` et, pour la documentation
Flow, `gh-pages`.

## Consommer et déployer

Externe : choisir un tag validé, par exemple
`composer require darkwood/darkwood-bundle:8.1.12`. Dans un produit interne
compatible, déclarer `dev-main` pour le package concerné. Le verrou
Composer reste la référence de déploiement.

Après publication, lancer une mise à jour ciblée du package en local,
contrôler le diff du verrou, puis tester, committer et pousser. Sur VPS-4,
vérifier l’état Git, exécuter `git pull --ff-only` et
`composer install --no-dev --optimize-autoloader`, puis reconstruire les
assets et le cache du produit selon sa procédure. En cas de régression,
rétablir par un commit le manifest et le verrou précédents, puis redéployer
ces dépendances verrouillées.
