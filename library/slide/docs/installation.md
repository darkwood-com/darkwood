# Installation et configuration

## Prérequis

- PHP 8.3 ou supérieur, avec les extensions demandées par Composer.
- Composer et Node.js 22 avec npm.
- Pour les rendus : Playwright et un navigateur Chrome/Chromium local.
- Pour les PDF : Python avec `pypdf` ; `pypdfium2`, Pillow et `pdfplumber` pour les aperçus et contrôles.

Utiliser Nix pour fournir les dépendances. Dans le workspace Darkwood, l'environnement de développement est disponible avec :

```sh
nix --extra-experimental-features 'nix-command flakes' develop ../darkwood-portal
```

## Première installation

Depuis la racine de Slide :

```sh
composer install
npm ci --ignore-scripts
test -f .env || cp .env.example .env
```

Configurer `.env` pour l'environnement local : `APP_ENV=local`, `APP_URL=http://127.0.0.1:8017`. Générer une clé avec `php artisan key:generate` uniquement si `APP_KEY` n'est pas encore configurée ; conserver toute clé existante.

```sh
npm run build
php artisan serve --host=127.0.0.1 --port=8017
```

Le catalogue et les associations publiques sont déjà versionnés. Pour une
installation existante, retirer les variables de connexion SQL de `.env`,
utiliser `SESSION_DRIVER=file`, `CACHE_STORE=file` et `QUEUE_CONNECTION=sync`,
puis exécuter `php artisan config:clear`. Les réglages par défaut conviennent
aux installations neuves. Voir le [guide de publication](publications.md).

## Développement

`npm run dev` lance Vite dans un second terminal. Les variables de configuration habituelles sont décrites dans `.env.example` ; les valeurs réelles restent dans `.env`, hors de Git.

Les outils de rendu acceptent `CHROME` pour le navigateur, `PLAYWRIGHT_MODULE` pour le module Playwright et `PUBLICATION_PYTHON` pour le runtime Python. Le serveur local doit rester accessible pendant les tests navigateur et les exports.

Le stockage généré est sous `storage/`. Ne pas versionner sessions, logs, captures ou exports de diagnostic. Les [commandes de maintenance](testing.md) permettent de vérifier le projet et les fichiers destinés au dépôt public.
