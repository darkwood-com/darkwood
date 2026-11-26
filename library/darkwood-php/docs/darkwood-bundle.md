# DarkwoodBundle

Le monorepo public est la source de développement. Le package se trouve dans
`library/darkwood-php/src/Darkwood/Bundle/DarkwoodBundle/`. Il fournit le footer
et la navigation Darkwood Identity, les logos Darkwood et Darkwaar, les
traductions FR, EN et DE, les tokens, les icônes de thème, Jost sous licence
OFL et le lecteur YouTube à chargement volontaire. Les palettes et composants
propres aux produits restent dans leurs applications.

Symfony enregistre le namespace Twig `@Darkwood` et installe les fichiers
publics sous `public/bundles/darkwood/`. Les dépendances couvrent Symfony 7.4
et 8.1, PHP 8.4 ou ultérieur et les composants Twig, Asset et Translation.
Le footer lit les liens dans le template et reçoit ses options à l’inclusion.
Les deux premiers consommateurs tournent sous Symfony 8.1. Composer a aussi
résolu le tag avec Symfony 7.4 et une plateforme PHP 8.4 simulée.

| Application | Commit de première adoption | Version |
|---|---|---|
| Darkwood Portal | `fbc1a89f7` | `v0.1.0` |
| Darkwaar | `ce4aca381` | `v0.1.0` |

## Publication

1. Valider le `composer.json`, le PHP, le rendu Twig et les actifs du bundle.
2. Committer la modification et la pousser sur `origin/main`.
3. Depuis la racine du monorepo, exécuter `bash .github/release-darkwood-bundle.sh v0.1.1` avec le tag SemVer voulu. Le script fait le split, compare exactement les arbres Git et pousse ensemble la branche et le tag sur le dépôt de distribution. Un tag existant est accepté uniquement pour le même commit.
4. Dans [Packagist](https://packagist.org/packages/darkwood/darkwood-bundle), déclencher **Update** si nécessaire et vérifier que Composer reçoit le nouveau tag et la référence du split.

La première version est `v0.1.0`. Elle correspond au commit source
`744278585181df7a44c753bc7551c1b9870f7353` et au commit de distribution
`db4a7385d7a9dd530c1550352adf7a6c55c014cc`.
Le test d’exécution distante de GitHub Actions n’a pas obtenu de jeton pour ce
nouveau dépôt. Le script utilise l’accès Git de l’opérateur et ne requiert
aucun nouveau secret. Le registre Packagist a été soumis avec le dépôt public
[darkwood-com/darkwood-php-bundle-darkwood-bundle](https://github.com/darkwood-com/darkwood-php-bundle-darkwood-bundle).

Les proxies des bundles suivent la convention
`darkwood-php-bundle-<nom-du-bundle>`. MediaBundle et IaExceptionBundle sont
synchronisés par le workflow `Sync distribution mirrors` à partir de
`.github/distribution-cards.json`. DarkwoodBundle conserve son script de
publication et son versionnement indépendant. Les trois packages conservent
leurs noms Composer. Voir le [catalogue PHP](../README.md#packages) pour les
URL canoniques.

## Adoption et retour arrière

Installer la version publiée, vérifier le diff du lockfile, puis enregistrer
le bundle, compiler les actifs et vérifier le rendu du footer sur desktop
et mobile. Chaque produit garde son propre `composer.lock`.

- **Portal** importe les tokens et icônes du package dans Encore. Installer
  Composer avant le build JavaScript. Après le premier déploiement, préparer
  le nouveau conteneur Symfony avant d’installer les actifs du bundle.
  Vérifier que le CSS Encore utilise les tokens et la police du package.
- **Darkwaar** installe les fichiers du bundle avant sa compilation AssetMapper
  et conserve les styles et les scripts propres aux jeux.
- **HeroClip**, **Geo** et **DJ Matyo** utilisent Symfony 8.1. Une résolution
  Composer de test accepte le tag dans les trois projets sans mettre à jour
  leurs dépendances déjà verrouillées. Leurs variantes de footer restent à valider.
- **Uniflow** utilise Sylius et AssetMapper. La résolution Composer est bloquée
  par un fichier `composer.lock` invalide dans le checkout public audité.
  **Slides** est une application Laravel et peut référencer les ressources statiques.

Sur VPS-4, avancer avec `git pull --ff-only`, installer les dépendances
verrouillées, reconstruire le conteneur et les actifs, puis contrôler la
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
