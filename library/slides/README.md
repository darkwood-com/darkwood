# Slides

Présentations Darkwood en Laravel et Blade, avec lecteur interactif, design system partagé, exports PDF et suivi des publications Speaker Deck / SlideShare.

## Démarrer

Dans l'environnement Nix du workspace :

```sh
nix --extra-experimental-features 'nix-command flakes' develop ../darkwood-com
composer install
npm ci --ignore-scripts
npm run build
php artisan serve --host=127.0.0.1 --port=8017
```

Pour une première installation, préparer l'environnement et la base selon le [guide d'installation](docs/installation.md). Le catalogue est disponible sur `http://127.0.0.1:8017/` et les présentations sur `/slides/SLUG`.

## Documentation

- [Installation et configuration](docs/installation.md)
- [Architecture et données](docs/architecture.md)
- [Design system](docs/design-system.md)
- [Créer et modifier une présentation](docs/presentations.md)
- [PDF, publications et mapping](docs/publications.md)
- [Déploiement](docs/deployment.md)
- [Tests et maintenance](docs/testing.md)

## Identifiants et publications

[resources/presentations.json](resources/presentations.json) définit les UUID stables du catalogue. Le [manifeste public](resources/data/publications.json) conserve les correspondances avec les providers. Le journal opérationnel et ses sauvegardes restent dans la base et le stockage privé.

```sh
php artisan publications:diagnose --json
php artisan test
npm test
```

Les exports, builds et consultations du lecteur ne publient aucun document sur les comptes externes.
