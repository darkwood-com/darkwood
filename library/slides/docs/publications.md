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

- `resources/presentations.json` contient les UUID stables ;
- `resources/data/publications.json` est la projection publique portable ;
- la base Laravel conserve les associations déployées ;
- les artefacts PDF restent sous `storage/` et hors de Git.

Le manifeste refuse les champs privés, doublons, UUID inconnus, URLs contenant
des credentials et réattributions d'identité distante. Son import est
transactionnel et idempotent. Il ne prouve pas que le PDF distant correspond au
hash déclaré : cette attestation relève du processus d'exploitation extérieur.

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
ou l'import d'un manifeste approuvé suffit à restituer les liens publics.
