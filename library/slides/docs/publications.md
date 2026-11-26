# PDF et manifeste public

Slides est la source canonique des présentations. Les consultations, builds et
exports n'effectuent aucun upload vers un fournisseur externe.

## Interface publique stable

```sh
php artisan publications:catalog
php artisan publications:export UUID --static --interactive-base=https://slides.darkwood.com
php artisan publications:manifest
php artisan publications:manifest --export
php artisan publications:manifest --import=resources/data/publications.json --dry-run
php artisan publications:associate UUID PROVIDER REMOTE_ID URL --confirmed-correspondence
php artisan publications:diagnose --json
```

Ces commandes inventorient les présentations, construisent un PDF local,
valident le manifeste public et enregistrent une association explicitement
confirmée. Elles ne se connectent pas à un compte fournisseur, n'acceptent ni
token ni cookie et ne téléversent aucun document.

## Identités et persistance

- `resources/presentations.json` fixe les UUID et les sources Blade.
- `resources/data/publications.json` fait foi pour les associations publiques.
- Les artefacts PDF restent sous `storage/`, hors de Git.

`publications:manifest` et `--export` lisent et impriment le manifeste validé.
`--import=FICHIER --dry-run` prévisualise la fusion. Enlever `--dry-run` écrit
sur le poste d’édition sous verrou et par remplacement atomique. Vérifier le
diff, puis committer et déployer le fichier. Le serveur de publication accepte
les lectures et simulations.

L’import est idempotent. Il refuse les UUID inconnus, fournisseurs ou URLs
invalides, champs privés, doublons par présentation ou par identité distante,
ainsi que tout changement d’ID. Un changement d’URL ou de hash déjà connu exige
`--accept-metadata-update`. Une date ou un hash omis conserve la valeur connue.
Un hash publié ne peut pas être effacé. L’attestation du PDF distant relève
du processus d’exploitation privé.


## Export PDF

L'export compile chaque frame dans le lecteur local, valide la pagination et
produit un PDF sous `storage/app/publications/UUID/EXPORT/`. Avec `--static`, le
mapping des pages doit couvrir toutes les slides canoniques. Une modification
des sources pendant l'export invalide l'artefact.

Vérifier le document final avec :

```sh
python3 bin/publication-preview.py CHEMIN_DU_PDF
```

Le nombre de pages PDF peut différer du nombre de slides ; le mapping doit
rester complet, ordonné et lié à la révision courante.

## Publication externe

La sélection du compte, la vérification Speaker Deck/SlideShare, l'upload, la
reprise et les procédures opérateur ne font pas partie de l'application
publique. Après une publication contrôlée, l'interface `publications:associate`
ou l’import d’un manifeste approuvé enregistre les liens publics dans le fichier à versionner.
