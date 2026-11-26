# Déploiement

## Environnement

Servir uniquement `public/` comme racine HTTP. Conserver la clé Laravel existante
et configurer `APP_ENV=production`, `APP_DEBUG=false` et
`APP_URL=https://slides.darkwood.com` dans le fichier privé `.env`. Le compte
applicatif doit pouvoir écrire dans `storage/` et `bootstrap/cache/`. Les
valeurs normales sont `SESSION_DRIVER=file`, `CACHE_STORE=file` et
`QUEUE_CONNECTION=sync`. Les artefacts sous `storage/app/publications/`
demeurent sur le volume persistant.

## Déployer

Dans l’environnement Nix, vérifier le commit et sauvegarder les artefacts
persistants concernés avant déploiement.

```sh
git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci --ignore-scripts
npm run build
php artisan config:cache
php artisan view:cache
php artisan publications:diagnose --json
```

Contrôler l’accueil, les routes `/slides/SLUG`, le lecteur et les assets après
déploiement. Les associations publiques proviennent directement du fichier
versionné. Pour les modifier, utiliser les commandes dans le checkout
d’édition, faire relire le diff et le déployer par Git. Le serveur accepte
les lectures et simulations, avec les procédures fournisseur dans Slides Core.

## Transition depuis PostgreSQL

Comparer les associations et les attestations entre PostgreSQL et le
manifeste avant retrait des credentials. Sauvegarder et restaurer la base en
isolation. Une fois validé, désactiver l’accès du rôle applicatif à la base
historique. Les archives SQL et les anciennes migrations accessibles dans Git
permettent un retour au commit précédent si nécessaire. Les historiques privés
restent hors du manifeste public. Vérifier séparément les usages et la
rétention avant une suppression définitive de la base.
