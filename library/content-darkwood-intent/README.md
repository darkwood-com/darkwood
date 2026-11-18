# Intention : de l’objectif au résultat

Six expériences interactives montrent comment une intention est interprétée dans un contexte, devient une décision et produit un résultat à évaluer.

**Domaine cible : [intent-content.darkwood.com](https://intent-content.darkwood.com)**

Migration préparée ; ne pas annoncer cette URL disponible avant validation DNS, HTTPS et redirection. À la demande du propriétaire, `intent.content.darkwood.com` ne bénéficie pas de rétrocompatibilité ni de redirection. Les données localStorage restent dans l’ancienne origine et ne sont pas transférées automatiquement.

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
- **DJ Matyo91** : ouverture, montée ou pic produisent une courbe d’énergie et une transition. Le motif Web Audio est original et illustratif, sans morceau ni sample tiers.

## Lancer localement

Prérequis : Nix avec `nix-command` et `flakes`. Le flake épinglé fournit PHP 8.5, Composer et Node.

```sh
nix --extra-experimental-features 'nix-command flakes' develop -c composer install
nix --extra-experimental-features 'nix-command flakes' develop -c php -S 127.0.0.1:8097 -t public
```

Ouvrir `http://127.0.0.1:8097`.

## Export statique

### Atelier Symfony AI MCP Hackathon

Le même générateur et le thème Darkwood exportent un atelier de présentation autonome, sans nouvelle application ni backend :

```sh
nix develop -c php bin/console app:export-static --workshop=hackathon
```

L’artefact est `dist-hackathon/`, séparé de `dist/` pour préserver Intention. Le manifeste de distribution relie cet export au domaine demandé `ai-mcp-symfony-hackathon-content.darkwood.com` et à son dépôt d’artefacts GitHub Pages. La disponibilité publique n’est acquise qu’après publication, résolution DNS et vérification HTTPS.

La trace `public/hackathon/mcp-proof.json` provient de l’essai réel du 17 novembre 2026 de la card Mario : initialisation, découverte, rejet d’un prompt vide et appel réel du modèle via le SDK officiel. Elle ne contient aucun accès fournisseur. La page ne déclenche pas d’appel IA. Les versions et les résultats décrivent les essais locaux ; vérifier leur correspondance avec le commit public avant diffusion.

Le profil par défaut reste Intention. Les mêmes contrôles PHP couvrent les deux exports et les ressources autorisées. Aucun média Replicate, article public ou formulaire de candidature n’est généré par cet export.

```sh
nix --extra-experimental-features 'nix-command flakes' develop -c php bin/console app:export-static
nix --extra-experimental-features 'nix-command flakes' develop -c node bin/serve-static.mjs
```

Ouvrir `http://127.0.0.1:8098`. Le serveur ne lance aucun PHP. Les routes directes sont matérialisées par `demo/<domaine>/index.html`.

Pour simuler un chemin GitHub Pages :

```sh
nix --extra-experimental-features 'nix-command flakes' develop -c php bin/console app:export-static --base-path=/darkwood-intent
nix --extra-experimental-features 'nix-command flakes' develop -c node bin/serve-static.mjs /darkwood-intent 8099
```

## Vérifier

```sh
nix --extra-experimental-features 'nix-command flakes' develop -c php bin/console lint:container
nix --extra-experimental-features 'nix-command flakes' develop -c php bin/console lint:twig templates
nix --extra-experimental-features 'nix-command flakes' develop -c php bin/phpunit
nix --extra-experimental-features 'nix-command flakes' develop -c node --test tests/content-darkwood-intent.test.mjs
```

Les tests couvrent les routes, les limites éditoriales, les décisions déterministes, les préfixes d’URL et la composition de l’artefact statique. Le responsive, le clavier, les thèmes et Web Audio demandent également une vérification navigateur.

## Publication

Le workflow canonique `.github/workflows/publish-github-pages.yml` construit et teste ce dossier dans Nix, puis synchronise exclusivement `dist/` vers `darkwood-com/content-darkwood-intent`. Le domaine exact `intent-content.darkwood.com` est écrit dans `CNAME`. Les sources restent dans le monorepo.

## Sources et limites

Les médias Aftertrace proviennent de l’atelier Memory et renvoient uniquement vers la page officielle Darkwaar. Les polices Jost conservent leur licence OFL dans `public/fonts/OFL.txt`. L’atelier ne contacte ni Darkwaar, Heroclip, DJ Matyo91, Flow ni Uniflow pendant l’utilisation.
