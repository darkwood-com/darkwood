# Tests et maintenance

Exécuter les commandes dans l'environnement Nix décrit dans le [guide d'installation](installation.md). Les tests navigateur exigent le serveur local et un Chrome/Chromium disponible ; `CHROME` permet d'en choisir le chemin.

## Contrôles courants

```sh
php artisan test
npm test
npm run build
```

Les tests Laravel couvrent le catalogue, le générateur, les composants, les identités, les contraintes de mapping, les réservations, l'import, la sauvegarde privée et les contrôles d'accès. Les tests JavaScript couvrent les règles de jeux et les surfaces fonctionnelles.

## Lecteur et rendu

```sh
npm run test:design
npm run test:interactions
npm run test:bounds
npm run test:render-results
npm run test:ui
npm run test:catalogue
```

Ces contrôles vérifient styles calculés, contraste, SVG, typographie, code, bordures, navigation et débordements. Les fixtures de `tests/fixtures/` sont des références de régression nécessaires aux tests, pas des journaux d'intervention.

Pour générer des vues de lecture et des planches contact locales :

```sh
npm run render
npm run render:reading
npm run contact-sheets
```

Les résultats sont écrits dans `storage/app/`, hors de Git. Conserver dans le dépôt uniquement les images éditoriales nécessaires au lecteur, pas les captures de validation.

## Export PDF

```sh
node tests/Browser/static-pagination.cjs
npm run test:pdf-layout
```

Le test de pagination vérifie le texte complet, l'ordre, les limites de pages, les captures multiples et la conservation des diagrammes statiques. Pour contrôler un export réel, suivre le [guide PDF](publications.md) et inspecter les aperçus du fichier final.

## Mapping et confidentialité

```sh
php artisan publications:diagnose --json
php artisan publications:manifest --export --dry-run
nix --extra-experimental-features 'nix-command flakes' shell --inputs-from ../darkwood-com nixpkgs#gitleaks nixpkgs#python3 --command python3 bin/audit-public-repository.py
```

Le contrôle local analyse l'historique accessible, l'index et les fichiers candidats avec Gitleaks. Les rapports détaillés sont masqués et conservés en stockage privé. Le contrôle refuse également les conteneurs privés ajoutés à Git. Aucun contenu n'est envoyé à un service de détection externe.

Avant commit, vérifier `git diff --cached`, les liens de documentation et l'absence de `.env` réels, credentials, dumps, sessions, logs ou rapports temporaires. Un fichier déjà suivi n'est pas protégé par une nouvelle règle d'ignore.

Si un secret publié est identifié, ne pas afficher sa valeur : révoquer ou faire tourner le credential suivant une procédure approuvée, puis examiner l'historique. Une suppression courante ne retire pas le secret des anciens commits ; toute réécriture d'historique demande une autorisation explicite.
