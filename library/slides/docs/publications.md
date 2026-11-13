# PDF et publications externes

Slides est la source canonique. Speaker Deck et SlideShare sont des canaux de diffusion ; les exports, builds et consultations du lecteur ne déclenchent aucun upload.

## Mapping et persistance

La base Laravel est le journal opérationnel :

- `presentations` : UUID, slug, titre et nombre de slides ;
- `external_publications` : `slide_id`, `provider`, `remote_id`, `remote_url`, `source_hash`, dates et état de synchronisation ;
- `publication_attempts` : fichier exact, opération, réservation active et état de reprise.

L'association est fondée sur **UUID Slides + provider → ID distant**. Les contraintes SQL garantissent l'unicité du couple `(slide_id, provider)` et de `(provider, remote_id)`. Une tentative active empêche une seconde réservation de la même destination.

[resources/data/publications.json](../resources/data/publications.json) est la projection publique de la base : identifiants, URLs canoniques, SHA-256 et dates publiques. Les champs supplémentaires, doublons, UUID inconnus et URLs contenant des credentials ou paramètres sont refusés. Les titres et slugs ne sont jamais des clés primaires.

```sh
php artisan publications:diagnose --json
php artisan publications:manifest
php artisan publications:manifest --export --dry-run
php artisan publications:manifest --export
```

Le diagnostic affiche les associations, les correspondances manquantes, les orphelines, les réservations et la dérive du manifeste sans erreurs brutes ni chemins privés. L'export public est explicite pour éviter des modifications automatiques de fichiers suivis par Git.

## Sauvegarde et restauration

Le journal complet est sauvegardé atomiquement après commit Eloquent dans `storage/app/private/publications/registry.json` : fichier `0600`, répertoire `0700`. Les écritures SQL directes exigent une sauvegarde explicite.

```sh
php artisan publications:registry
php artisan publications:registry --dry-run
```

La sortie de ces commandes reste limitée aux données publiques. Conserver la base, le journal complet et les artefacts PDF dans une sauvegarde privée. Une sauvegarde du journal ne remplace pas celle de PostgreSQL ou SQLite.

Pour une restauration complète, utiliser une base de récupération dont les trois tables de suivi sont vides. Effectuer les migrations de schéma, puis restaurer **avant** de lancer `publications:catalog` :

```sh
php artisan migrate
php artisan publications:registry --restore=storage/app/private/publications/registry.json --dry-run
php artisan publications:registry --restore=storage/app/private/publications/registry.json
```

Après un changement de machine, restaurer également les artefacts et vérifier leurs chemins avant de réconcilier les tentatives. Un fichier manquant ne justifie jamais une nouvelle création distante.

### Nouvelle installation depuis les fichiers publics

La base de publication et la sauvegarde privée ne sont pas nécessaires pour reconstruire les associations publiques. Le dépôt fournit les UUID stables dans `resources/presentations.json`, les sources des présentations et le manifeste `resources/data/publications.json`.

Après configuration d'une base neuve :

```sh
php artisan migrate
php artisan publications:manifest --import=resources/data/publications.json --dry-run
php artisan publications:manifest --import=resources/data/publications.json
php artisan publications:diagnose --json
```

L'import crée aussi le catalogue en base ; `publications:catalog` n'est pas requis auparavant. Il restaure les IDs distants, URLs, hashes et dates présents dans le manifeste. Il est transactionnel et peut être relancé sans créer de doublons. Les lignes restaurées portent l'état `imported_public_manifest` : l'import ne vérifie pas les services distants et n'atteste pas que le PDF courant correspond au hash publié.

Le manifeste ne contient ni PDFs, ni tentatives en cours, ni erreurs privées. Leur récupération complète exige une sauvegarde privée. Les associations absentes du manifeste restent manquantes ; elles ne sont jamais déduites d'un titre ou d'un slug. Aucun import n'est déclenché automatiquement par une consultation ou un build.

Pour une base existante, suivre les règles de conflit de l'[import de déploiement](deployment.md). La base reste la source de vérité opérationnelle ; le manifeste est son export public portable et approuvé, suffisant pour reconstruire les associations, pas un second journal de jobs.

## Générer un PDF

Démarrer le serveur local, synchroniser le catalogue puis exporter un UUID :

```sh
php artisan publications:catalog
php artisan publications:export UUID --static --interactive-base=https://slides.darkwood.com
```

Sans `--static`, un contenu qui exige une pagination ou une représentation statique est refusé. `--interactive-pending` permet d'indiquer qu'une version interactive n'est pas encore accessible ; ne l'utiliser que si cette mention correspond à la destination réelle.

Le rendu Chromium utilise 1440 × 810 CSS px ; l'assemblage agrandit uniformément les pages à **1920 × 1080 points**, ratio 16:9. Les polices sont incorporées et le SHA-256 porte sur le fichier final. `PUBLICATION_PYTHON`, `CHROME` et `PLAYWRIGHT_MODULE` permettent de sélectionner les runtimes locaux.

Chaque export est isolé sous `storage/app/publications/UUID/EXPORT/` avec son manifeste de révision, son PDF, `pdf-validation.json` et `page-map.json`. Le mapping de pages relie chaque page à la slide canonique et à son numéro de partie. Une modification de source, d'asset, de compilateur ou de renderer invalide la préparation d'un ancien export.

La projection statique répartit les contenus denses sans réduire le corps éditorial et capture les plateaux, tableaux et médias dont la disposition porte une information. Les images conservent leur ratio. Les captures de composants sont réutilisées par révision pour limiter les variations d'animation.

La copie d'export retire les hooks de reconstruction Mermaid afin de préserver les SVG figés. Si le diagramme est refusé par le lecteur, sa notation source reste lisible avec une mention explicite. Une démonstration externe indisponible reçoit un avertissement et un lien ; compléter sa capture réelle avant validation éditoriale.

Vérifier le document final, pas seulement les captures du navigateur :

```sh
python3 bin/publication-preview.py CHEMIN_DU_PDF
```

Contrôler ordre, nombre de pages, images, polices, dimensions et absence de contenu vide ou coupé. Le nombre de pages PDF peut différer du nombre de slides ; le mapping doit rester complet et ordonné.

## Publier et enregistrer

Avant tout upload, examiner le compte cible et les correspondances existantes. Une ressemblance de titre ou de slug ne prouve pas une association.

```sh
php artisan publications:prepare UUID PROVIDER CHEMIN_DU_PDF --dry-run
php artisan publications:prepare UUID PROVIDER CHEMIN_DU_PDF
```

La préparation vérifie l'UUID, la révision, le hash et la pagination puis réserve une tentative, sans transmettre le fichier. Téléverser le PDF exact dans l'interface officielle, vérifier titre, description, compte, visibilité publique, pagination et fichier servi, puis :

```sh
php artisan publications:complete TENTATIVE_UUID REMOTE_ID URL --confirmed-exact-artifact --confirmed-public-access
php artisan publications:manifest --export
```

Pour associer une publication existante après vérification indépendante du contenu :

```sh
php artisan publications:associate UUID PROVIDER REMOTE_ID URL --confirmed-correspondence
```

Cette confirmation éditoriale ne prouve pas le hash du PDF distant. Les valeurs inconnues restent nulles.

### Providers

**Speaker Deck** : utiliser le compte prévu et le parcours officiel d'upload PDF. L'oEmbed public permet de vérifier compte, titre et ID hexadécimal du player, mais ne fournit pas le hash du PDF. Le parcours Reupload permet un remplacement sur la même identité ; les limites et options du compte doivent être vérifiées avant chaque lot. Relire les métadonnées et la visibilité après traitement du fichier.

**SlideShare** : utiliser le parcours web officiel et conserver l'ID numérique de l'URL canonique. L'adaptateur déclare ses capacités dans `SlideShareProvider` ; une capacité indisponible ou un remplacement non confirmé impose une action manuelle. Ne pas utiliser d'anciens tokens ou endpoints privés.

Les capacités applicatives sont déclarées dans `PublicationProviders` et les services de provider. Aucune API d'écriture ni extraction de cookies n'est implémentée. Consulter les aides officielles de [Speaker Deck](https://help.speakerdeck.com/) et [SlideShare](https://support.scribd.com/hc/en-us/categories/360001743332-Slideshare) pour les contraintes du service.

## Mise à jour et reprise

Si le hash change, préparer un remplacement avec `--confirmed-replacement` lorsque le provider le permet. Conserver l'ID et l'URL existants. Sans remplacement disponible, signaler une action manuelle ; ne pas créer automatiquement de doublon. Une incertitude liée au cache doit être vérifiée sans nouvel upload.

```sh
php artisan publications:reconcile TENTATIVE_UUID --error=interrupted
php artisan publications:diagnose --json
```

Une tentative interrompue reste bloquante. Si la publication existe, terminer son mapping sans la recréer. Une création réellement absente peut être clôturée avec `--confirmed-no-remote-publication` après vérification du compte ; ce drapeau ne s'applique pas aux remplacements.

Les rapports générés restent privés et hors de Git :

```sh
php artisan publications:report --output=storage/app/private/publications/report.json
php artisan publications:report --html --output=storage/app/private/publications/report.html
```
