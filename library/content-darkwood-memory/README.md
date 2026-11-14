# Mémoire : ce qui rend la suite possible

Du logiciel à l’IA, des jeux vidéo à la musique et à l’image : ce que nous conservons pour continuer à créer.

Six démonstrations autonomes, un atelier interactif et trois ambiances visuelles. Ces expériences accompagnent un article Darkwood ; elles ne constituent pas des annonces de fonctionnalités des services.

**Site : [memory.content.darkwood.com](https://memory.content.darkwood.com)**

## Lancer le projet Symfony

Prérequis : Nix avec `nix-command` et `flakes`. Le flake épinglé fournit PHP 8.5, Composer et Node pour les tests et la prévisualisation, sans Docker ni installation système supplémentaire.

```sh
git clone git@github.com:matyo91/darkwood-memory.git
cd darkwood-memory
nix develop -c composer install
nix develop -c php -S 127.0.0.1:8097 -t public
```

Ouvrir `http://127.0.0.1:8097`. Si les fonctionnalités Nix ne sont pas activées, utiliser `nix --extra-experimental-features 'nix-command flakes' develop -c ...`.

Symfony Skeleton 8.1, Twig, formulaires et validation natifs. Aucun compte, base de données, modèle téléchargé, clé API ou dépendance à une application de production.

## Générer et essayer la version statique

```sh
nix develop -c php bin/console app:export-static
nix develop -c node bin/serve-static.mjs
```

Ouvrir `http://127.0.0.1:8098`. Ce serveur de fichiers n’exécute aucun PHP. Le dossier `dist/`, ignoré par Git, contient sept pages HTML et uniquement les assets publics autorisés. L’export refuse les fichiers inattendus et les liens symboliques dans son dossier de sortie.

Pour vérifier une publication sous le chemin d’un projet GitHub Pages :

```sh
nix develop -c php bin/console app:export-static --base-path=/darkwood-memory
nix develop -c node bin/serve-static.mjs /darkwood-memory 8099
```

Le domaine personnalisé utilise l’export sans préfixe. Chaque démonstration possède son propre `demo/<service>/index.html` : la navigation et le rechargement direct ne nécessitent aucune réécriture de route côté serveur.

## Les six expériences

- **Darkwood** : retrouver MEM-001 ou MEM-002, une décision fictive datée, sa provenance et ses révisions. Les mêmes données JSON versionnées alimentent les deux versions.
- **Flow** : distinguer variable temporaire, calcul mémoïsé et résultat conservé. Symfony exécute du PHP et utilise une session fichier. Le site statique exécute du JavaScript et utilise localStorage : il ne crée aucune session PHP. Un second exemple exécute réellement `darkwood/flow` v8.1.6 : trois transformations partagent un total calculé une seule fois. Son code et son résultat PHP sont affichés dans les deux versions ; GitHub Pages reçoit ce résultat pendant le build. Flow ne fournit ici aucune mémoire persistante.
- **Uniflow** : distinguer définition et exécutions. La pause conserve un identifiant et un curseur ; la reprise termine la même exécution sans rejouer les étapes. Douze exécutions maximum, sans moteur distant ni effets externes.
- **Darkwaar** : lire une inscription, revenir au début et avancer grâce au carnet conservé. « Tout oublier » efface monde et carnet. Le mécanisme du carnet reste local. Aftertrace enrichit cette section avec un jeu original publié sur Darkwaar et itch.io, un teaser de gameplay réel et une iframe vers une release Web vérifiée. Sa carte survit aux retours dans une même partie, sans sauvegarde après rechargement.
- **DJMatyo** : composer huit pas, écouter une synthèse originale, créer une variation, conserver le motif et exporter ses notes et son tempo en JSON. Aucun morceau ni sample tiers.
- **Heroclip** : assembler six plans avec ou sans rappel du personnage, de la palette et de l’accessoire. Les illustrations et règles sont déterministes ; aucune vidéo ni génération IA n’est produite.

Le site statique garde l’état uniquement dans le navigateur. Effacer les données du site efface cette mémoire ; aucun partage multi-appareils n’est fourni. Le stockage refusé est signalé et les actions restent utilisables pendant la page courante. En mode Symfony, les actions serveur utilisent les sessions fichiers dans `var/`. Une session de démonstration ne constitue pas une archive durable.

## Trois thèmes, une navigation

Le layout reprend les conventions Darkwaar : barre supérieure de 61 px, sidebar de 180/240/300 px selon les breakpoints, menu mobile, polices Jost et variables CSS `--text-color`, `--bg-color`, `--input-color`, `--border-color` et `--primary-color`.

Le logo est placé dans le header ; GitHub et le bouton de thème unique utilisent des SVG locaux selon les conventions Uniflow. La sidebar conserve uniquement la navigation ; sur mobile, elle occupe toute la largeur avec fermeture explicite et verrouillage du scroll. Les indicateurs utilisent une pastille bleue de 8 px, sans modifier les bordures de focus ou de séparation. Le corps Jost est de 18 px avec une hauteur de ligne de 1,5.

Les classes `theme-light`, `theme-dark` et `theme-sepia`, la clé locale `theme` et le cycle clair/sombre/sépia suivent la même convention. La préférence est appliquée avant l’affichage puis conservée entre les pages, sans session PHP. Le logo noir Darkwood original est utilisé sur une surface claire dans les trois thèmes. Les icônes sont attribuées dans `LICENSE-icons.txt`.

## Vérifier

```sh
nix develop -c php bin/console lint:container
nix develop -c php bin/console lint:twig templates
nix develop -c php bin/phpunit
nix develop -c node --test tests/memory-model.test.mjs
```

Les tests couvrent les routes PHP, les sessions et leur isolation, la protection CSRF, la reprise d’exécution, le carnet, le storyboard, les modèles navigateur et l’export avec et sans préfixe. Les thèmes, le responsive et la synthèse audio nécessitent aussi une vérification navigateur.

## Publication

Le workflow canonique `.github/workflows/publish-github-pages.yml` du monorepo installe les dépendances verrouillées dans Nix, exécute les tests, génère `dist/` puis publie uniquement l’artefact dans le repository technique `darkwood-com/memory-pages`. Les sources, issues et contributions restent dans le monorepo ; le repository Pages n’est pas une source de développement.

Le domaine `memory.content.darkwood.com` pointe vers GitHub Pages en DNS uniquement. Son certificat HTTPS est géré par GitHub pour ce nom exact. Le site n’utilise aucun serveur PHP permanent.

## Licence et assets

Le code et les séquences musicales originales sont sous licence MIT. Les polices Jost reprises des assets Darkwaar sont distribuées sous SIL Open Font License, conservée dans `public/fonts/OFL.txt` (source : dépôt officiel Google Fonts, `ofl/jost/OFL.txt`).

Le logo officiel noir est repris sans modification de l’asset Darkwood `public/hello/logo-uni-light.png`. Les identités et logos Darkwood conservent leurs droits respectifs ; la licence du code n’accorde aucun droit supplémentaire sur les marques. Aucun asset n’est chargé depuis une URL temporaire.

## Exécuter Flow en PHP

```sh
nix develop -c php examples/flow-memory.php
```

`FlowFactory`, `Ip` et `await()` transmettent un état entre trois closures. Entrée `[4,7,9]`, total `20`, moyenne `20/3`, reste `80`, un seul calcul du total. Chaque invocation recrée son état. PHP 8.5 minimum est requis par le package.

Le thème sombre reprend les tokens Uniflow (`#091a28`, `#0e2233`, `#0f2b3d`, bleu `#0066cc`). Le header gris foncé est un mélange du fond et du secondaire Uniflow `#576068` ; les petits textes bleus utilisent une teinte dérivée plus claire pour leur contraste, tandis que pastilles, bordures et actions partagent le bleu principal. Les thèmes clair et sépia restent inchangés.

Aftertrace est intégré par iframe vers une release immuable vérifiée sur Darkwaar ; les mises à jour du jeu demandent d’actualiser cette URL. Seuls une capture et le teaser sont copiés dans ce dépôt. La page renvoie exclusivement vers [la page officielle du jeu sur Darkwaar](https://darkwaar.com/games/aftertrace).

Les liens éditoriaux et médias de Memory privilégient les domaines officiels Darkwood. Les seules exceptions de navigation vers une plateforme tierce sont les dépôts sources officiels Memory et Flow sur GitHub. Les plateformes de distribution externes ne sont pas liées depuis cette vitrine.
