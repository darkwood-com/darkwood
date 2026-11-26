# DarkwoodBundle

Le monorepo public est la source de développement. Le package se trouve dans
`library/darkwood-php/src/Darkwood/Bundle/DarkwoodBundle/`. Il fournit le footer
et la navigation Darkwood Identity, les logos Darkwood, Darkwaar, HeroClip, DJ Matyo, Geo, Flow et Uniflow, les
traductions FR, EN et DE, les tokens, les icônes de thème, Jost sous licence
OFL et le lecteur YouTube à chargement volontaire. Les palettes et composants
propres aux produits restent dans leurs applications.

Symfony enregistre le namespace Twig `@Darkwood`. Le bundle déclare le chemin
AssetMapper `darkwood` pour son dossier `assets/`. Les applications rendent
les chemins comme `asset('darkwood/styles/identity.css')`, puis génèrent
les fichiers versionnés sous `public/assets/darkwood/` en production. Les dépendances couvrent Symfony 7.4
et 8.1, PHP 8.4 ou ultérieur et les composants Twig, Asset et Translation.
Le catalogue `data/identity.json` définit les liens et l’ordre du footer.
Le template reçoit ses options à l’inclusion.
Les deux premiers consommateurs tournent sous Symfony 8.1. Composer a aussi
résolu le tag avec Symfony 7.4 et une plateforme PHP 8.4 simulée.

| Application | Commit de première adoption | Version |
|---|---|---|
| Darkwood Portal | `fbc1a89f7` | `v0.1.0` |
| Darkwaar | `ce4aca381` | `v0.1.0` |

## Publication

DarkwoodBundle suit la [procédure commune](distribution.md). La branche
`main` du proxy alimente `dev-main` sur Packagist. Les tags stables
s’alignent sur une version Symfony validée à partir de `v8.1.12`.

L’ancien tag `v0.1.0` reste disponible. Il correspond au commit source
`744278585181df7a44c753bc7551c1b9870f7353` et au commit de distribution
`db4a7385d7a9dd530c1550352adf7a6c55c014cc`.

## Adoption et retour arrière

Installer la version stable publiée ou `dev-main`, vérifier le diff du lockfile, puis enregistrer
le bundle, compiler les actifs et vérifier le rendu du footer sur desktop
et mobile. Chaque produit garde son propre `composer.lock`.

- **Portal** conserve sa chaîne Encore pour les assets du site. Les assets
  du footer du bundle suivent AssetMapper et ne nécessitent aucun build Encore.
- **Darkwaar** charge son logo de navigation et son footer depuis le bundle
  et conserve son pipeline AssetMapper et les styles propres aux jeux.
- **HeroClip**, **Geo** et **DJ Matyo** utilisent les marques SVG, le footer
  et les styles partagés. Les interactions vidéo, audio et cartographiques
  restent dans les applications. L’export statique DJ Matyo embarque les
  fichiers versionnés générés par AssetMapper sous son préfixe de publication.
- **Uniflow** charge le logo et le footer depuis AssetMapper dans le layout
  de la boutique. Les assets spécifiques gardent leur chaîne Sass/Encore.
- **Flow** lit le catalogue et copie les assets dans sa build Hugo existante
  pour GitHub Pages.
- **Slides** est une application Laravel et peut référencer les ressources statiques.

Sur VPS-4, avancer avec `git pull --ff-only`, installer les dépendances
verrouillées, reconstruire le conteneur et les actifs avec `APP_ENV=prod php bin/console asset-map:compile`, puis contrôler la
version installée et les pages publiques. Pour revenir en arrière, rétablir
dans un commit le précédent `composer.lock`, l’enregistrement du bundle et
les templates associés, puis déployer normalement. Les tags publiés restent
immuables.

La propagation peut être préparée par Navi en lançant une mise à jour Composer
ciblée et les contrôles par produit. Une publication du bundle ne déclenche
aucun déploiement consommateur.

## Anciennes sources

`library/darkwood-php/composer.json` déclarait l’agrégat historique
`darkwood/darkwood`. Les dépôts Darkwood inspectés utilisent directement les
packages séparés. Son ancien dépôt de distribution ne répond plus et le
registre Packagist ne déclare aucun dépendant. Les manifests indépendants de
Navi, Flow, MediaBundle et IaExceptionBundle continuent à servir leurs
distributions.

Les guides et ressources de Darkwood Design sont dans le bundle. Le catalogue
public redirige l’ancienne entrée vers Darkwood PHP et les sites statiques
lisent directement les fichiers versionnés dans le monorepo.
