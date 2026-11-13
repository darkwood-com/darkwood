# Installation et configuration

## Prérequis

- PHP 8.3 ou supérieur, avec les extensions demandées par Composer et le pilote de base choisi.
- Composer et Node.js 22 avec npm.
- SQLite en local ou PostgreSQL pour une base persistante de serveur.
- Pour les rendus : Playwright et un navigateur Chrome/Chromium local.
- Pour les PDF : Python avec `pypdf` ; `pypdfium2`, Pillow et `pdfplumber` pour les aperçus et contrôles.

Utiliser Nix pour fournir les dépendances. Dans le workspace Darkwood, l'environnement de développement est disponible avec :

```sh
nix --extra-experimental-features 'nix-command flakes' develop ../darkwood-com
```

## Première installation

Depuis la racine de Slides :

```sh
composer install
npm ci --ignore-scripts
test -f .env || cp .env.example .env
test -f database/database.sqlite || touch database/database.sqlite
```

Configurer `.env` pour l'environnement local : `APP_ENV=local`, `APP_URL=http://127.0.0.1:8017` et `DB_CONNECTION=sqlite`. Générer une clé avec `php artisan key:generate` uniquement si `APP_KEY` n'est pas encore configurée ; conserver toute clé existante.

```sh
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8017
```

Le lecteur et le catalogue n'exigent pas de synchronisation en écriture de la base. Pour préparer les fonctions de publication, suivre la procédure de [catalogue et récupération du mapping](publications.md) adaptée à une installation neuve ou à une restauration.

## Développement

`npm run dev` lance Vite dans un second terminal. Les variables de configuration habituelles sont décrites dans `.env.example` ; les valeurs réelles restent dans `.env`, hors de Git.

Les outils de rendu acceptent `CHROME` pour le navigateur, `PLAYWRIGHT_MODULE` pour le module Playwright et `PUBLICATION_PYTHON` pour le runtime Python. Le serveur local doit rester accessible pendant les tests navigateur et les exports.

Le stockage généré est sous `storage/`. Ne pas versionner bases, sessions, logs, captures ou exports de diagnostic. Les [commandes de maintenance](testing.md) permettent de vérifier le projet et les fichiers destinés au dépôt public.
