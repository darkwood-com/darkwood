# Intention : de l’objectif au résultat

Six expériences interactives montrent comment une intention est interprétée dans un contexte, devient une décision et produit un résultat à évaluer.

**Site : [intent-content.darkwood.com](https://intent-content.darkwood.com)**

Migration du 18 novembre 2026 validée : DNS uniquement vers GitHub Pages, certificat approuvé et HTTPS forcé. À la demande du propriétaire, `intent.content.darkwood.com` ne bénéficie pas de rétrocompatibilité ni de redirection. Les données localStorage restent dans l’ancienne origine et ne sont pas transférées automatiquement.

Les démonstrations sont pédagogiques. Elles n’annoncent pas de nouvelles fonctions des services Darkwood et ne constituent pas des benchmarks.

## Architecture

- Symfony 8.1 et Twig pour la conception et les contrôles locaux ;
- JavaScript natif pour les interactions publiques ;
- export statique strict, sept pages et une liste fermée d’assets ;
- aucun compte, base de données, backend permanent ou service IA ;
- choix enregistrés uniquement dans `localStorage` ;
- trois thèmes issus de Memory et bleu Darkwood `#007BFF` ;
- GitHub Pages reçoit uniquement le contenu de `dist/`.

## Les six expériences

- **Darkwood** : une intention est orientée vers le service adapté au résultat recherché. La démonstration ne simule aucune intégration entre les services.
- **Flow** : la simplicité conduit à PHP inline, les I/O indépendantes à Amp/Revolt, et les calculs CPU à une étude mesurée de workers ou kernels. Il s’agit d’une simulation, pas d’un benchmark.
- **Uniflow** : un besoin et son contexte donnent une définition concise. Aucune exécution durable, paiement ou opération distante n’est simulée.
- **Darkwaar** : explorer une rive ou transporter une braise ne conduit pas à la même décision. Aftertrace est la référence réelle ; le jeu n’est pas modifié.
- **Heroclip** : l’objectif narratif et la contrainte de continuité déterminent trois plans locaux. Aucune vidéo ni API IA n’est utilisée.
- **DJ Matyo** : ouverture, montée ou pic produisent une courbe d’énergie et une transition. Le motif Web Audio est original et illustratif, sans morceau ni sample tiers.

## Lancer localement

Prérequis : Nix avec `nix-command` et `flakes`. Le flake épinglé fournit PHP 8.5, Composer et Node.

```sh
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix -c composer install
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix -c php -S 127.0.0.1:8097 -t public
```

Ouvrir `http://127.0.0.1:8097`.

## Export statique

Cet export appartient uniquement à Intention et produit ses sept pages dans `dist/`.
L’atelier Symfony AI MCP possède désormais sa [propre card](../content-darkwood-ai-mcp-symfony-hackathon/) et sa distribution indépendante.

```sh
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix -c php bin/console app:export-static
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix -c node bin/serve-static.mjs
```

Ouvrir `http://127.0.0.1:8098`. Le serveur ne lance aucun PHP. Les routes directes sont matérialisées par `demo/<domaine>/index.html`.

Pour simuler un chemin GitHub Pages :

```sh
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix -c php bin/console app:export-static --base-path=/darkwood-intent
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix -c node bin/serve-static.mjs /darkwood-intent 8099
```

## Vérifier

```sh
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix -c php bin/console lint:container
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix -c php bin/console lint:twig templates
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix -c php bin/phpunit
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix -c node --test tests/content-darkwood-intent.test.mjs
```

Les tests couvrent les routes, les limites éditoriales, les décisions déterministes, les préfixes d’URL et la composition de l’artefact statique. Le responsive, le clavier, les thèmes et Web Audio demandent également une vérification navigateur.

## Publication

Le workflow canonique `.github/workflows/publish-github-pages.yml` construit et teste ce dossier dans Nix, puis synchronise exclusivement `dist/` vers `darkwood-com/content-darkwood-intent`. Le domaine exact `intent-content.darkwood.com` est écrit dans `CNAME`. Les sources restent dans le monorepo.

## Sources et limites

Les médias Aftertrace proviennent de l’atelier Memory et renvoient uniquement vers la page officielle Darkwaar. Les polices Jost conservent leur licence OFL dans `public/fonts/OFL.txt`. L’atelier ne contacte ni Darkwaar, Heroclip, DJ Matyo, Flow ni Uniflow pendant l’utilisation.

## Atelier Symfony AI et MCP

L’atelier est maintenu dans [content-darkwood-ai-mcp-symfony-hackathon](../content-darkwood-ai-mcp-symfony-hackathon/)
et publié sur <https://ai-mcp-symfony-hackathon-content.darkwood.com/>.
Son build ne dépend plus d’Intention. La vidéo et la soumission au concours restent
un chantier distinct soumis à validation.

## Hébergement du teaser — S47

Le teaser original Aftertrace est conservé localement mais exclu du suivi Git.
Les deux expériences utilisent le même [upload non répertorié](https://www.youtube.com/watch?v=Mm7xHmnJnKo).
Le composant commun Darkwood Design charge le lecteur nocookie après un clic,
avec un lien direct de secours. Les exports ne dépendent plus du MP4 local.
L’ancienne URL de fichier est signalée comme déplacée par la page 404.

## Environnement PHP partagé

Les entrées `flake.nix` et `shell.nix` réutilisent le constructeur canonique Navi
(`library/darkwood-php/src/Darkwood/Component/Navi/tools/env/shell.nix`). Le verrou
Nixpkgs suit la même révision que Navi ; les outils spécifiques restent explicites. Depuis la card, privilégier
`nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix`
pour ne pas capturer le monorepo comme source de flake. Aucun service n'est démarré.
