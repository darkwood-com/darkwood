# Déploiement

## Environnement

Servir uniquement le répertoire `public/` comme racine HTTP. Configurer l'environnement privé avec `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://slides.darkwood.com`, une clé Laravel conservée et la connexion de base adaptée.

Ne pas stocker les secrets dans Git. Le fichier `.env` appartient au compte applicatif, en mode `0600`. Préserver la base et `storage/` entre les versions du code ; le compte applicatif doit pouvoir écrire dans `storage/` et `bootstrap/cache/`. Les credentials et données de session ne doivent pas être accessibles sous la racine publique.

## Mettre à jour le code

Avant toute mise à jour réelle, sauvegarder la base et les données persistantes, vérifier le commit à déployer et prévoir le retour au commit précédent. Ne jamais régénérer une clé applicative existante.

Dans l'environnement Nix de construction et sous le compte applicatif approprié :

```sh
git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci --ignore-scripts
npm run build
php artisan migrate --force
php artisan config:cache
php artisan view:cache
```

Redémarrer ou recharger le processus applicatif selon l'hébergement. Vérifier le catalogue, les routes `/slides/SLUG`, les assets et les médias. Les migrations, imports et redémarrages sont des opérations de déploiement explicites, pas des effets d'un affichage ou d'un export.

## Synchroniser les associations

Le plan de contrôle privé est le seul pilote des attestations. Il exporte et
versionne un manifeste public approuvé avec `publications:manifest --export`.
Le serveur reçoit ce manifeste et sert le lecteur ; il ne contacte aucun compte
fournisseur.

Un `git pull` ne remplace pas la base et n'importe aucun mapping automatiquement. Après sauvegarde de la base de destination :

```sh
php artisan publications:diagnose --json
php artisan publications:manifest --import=resources/data/publications.json --dry-run
php artisan publications:manifest --import=resources/data/publications.json
```

L'import est transactionnel et idempotent. Il ajoute les associations manquantes, ne crée aucun job et ne supprime aucune ligne. Il refuse une identité distante différente, un ID déjà occupé, une tentative non réconciliée et une divergence de hash ou d'URL connus.

Après revue des différences, l'opérateur peut autoriser l'actualisation des métadonnées d'une **même identité distante** :

```sh
php artisan publications:manifest --import=resources/data/publications.json --accept-metadata-update --dry-run
php artisan publications:manifest --import=resources/data/publications.json --accept-metadata-update
```

Ce drapeau ne réattribue jamais un ID distant et n'efface pas un hash connu. Une date inconnue ne supprime pas une date attestée. Ne pas exporter depuis le miroir serveur pour écraser les attestations du poste de publication.

Les artefacts et données d'exploitation privées ne transitent pas par le
manifeste Git.
