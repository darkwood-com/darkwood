# Slidewire

Présentations Darkwood natives : **28 présentations, 487 slides**, composants Blade partagés, bleu unique, contenu à 36 px et code VS Code Dark.

## Démarrer localement

Utiliser l'environnement Nix existant, sans Docker ni installation système :

```sh
cd /Users/math/Sites/darkwood/slidewire
nix --extra-experimental-features 'nix-command flakes' develop ../darkwood-com
composer install
test -f .env || cp .env.example .env
test -f database/database.sqlite || touch database/database.sqlite
php artisan migrate
npm ci --ignore-scripts
npm run build
php artisan serve --host=127.0.0.1 --port=8017
```

Au tout premier lancement uniquement, générer la clé Laravel avec `php artisan key:generate` si elle n'est pas configurée. Ne pas régénérer une clé existante et ne jamais versionner `.env`.

Ouvrir `http://127.0.0.1:8017/` pour le catalogue des présentations. Le registre `resources/presentations.json` alimente automatiquement le catalogue et les routes `/slides/SLUG`, sans accès en écriture à la base ; voir [l'inventaire](docs/publication-inventory.json). `npm run dev` lance le développement des assets dans un second terminal Nix.

## Design et composants

[Design system](docs/design-system.md) : tokens, typographie, composants de contenu, code/terminal, navigation, logos et PDF. Le design natif est l'unique identité du projet. Les sources historiques externes ne sont pas modifiées.

## Créer une présentation

```sh
php artisan make:slidewire my-talk --title="Mon titre"
php artisan make:slidewire my-talk --multi --md
```

Choisir une des deux formes : le générateur écrit des composants Blade natifs ; `--md` place le contenu Markdown dans ces composants. Enregistrer une fois un UUID stable dans `resources/presentations.json`, puis exécuter `publications:catalog`. Un changement de titre ou de chemin ne doit pas recréer cet UUID. Les sources existantes sont protégées ; `--force` ne remplace que les fichiers du squelette dans le même format.

## Tests et rendus

Les tests navigateur utilisent Playwright installé par npm et un Chrome local ; préciser `CHROME` si son chemin diffère de `/Applications/Google Chrome.app/Contents/MacOS/Google Chrome`.

```sh
php artisan test
npm test
npm run test:design
npm run test:interactions
npm run render
npm run test:bounds
npm run test:render-results
npm run render:reading
npm run contact-sheets
npm run test:pdf-layout
npm run test:ui
npm run test:catalogue
```

Rendus et mesures calculées : `storage/app/design-system/`. La galerie locale est `index.html`, avec une planche contact par présentation. Les PNG font 1440 × 900. Les compositions trop denses sont signalées pour décision éditoriale sans réduire le corps à 36 px.

Voir le [bilan de validation](docs/design-validation.md) pour les résultats, les PDF contrôlés et la liste des exceptions par présentation.

## PDF et publication

Slidewire est la source canonique. Pour exporter toutes les frames, utiliser le pipeline validé plutôt que l'impression directe du lecteur :

```sh
php artisan publications:catalog
php artisan publications:export ced2e361-eb17-4e83-a695-3213d156a876
```

Le PDF utilise le format 1920 × 1080 points (16:9), avec polices incorporées et SHA-256. Les médias interactifs et contenus défilants sont refusés plutôt que coupés. Les outils Python nécessitent pypdf ; pypdfium2 et Pillow servent aux aperçus. `PUBLICATION_PYTHON` peut désigner un runtime existant contenant ces modules.

[Publication assistée](docs/publications.md) · [Providers](docs/publication-providers.md) · [Associations existantes](docs/publications-status.md). Le tableau de bord `/publications` est local et ne surcharge pas le lecteur. Les UUID et associations restent stables lors des changements graphiques. Aucun export ne publie automatiquement sur les comptes externes.

## Auteurs et conclusions

Les portraits sont associés à leur auteur dans `config/authors.php`. Le composant `darkwood.author-portrait` et la conclusion réutilisable sélectionnent l'image connue ; un auteur inconnu conserve un logo de repli et ne reçoit jamais le portrait d'un autre auteur. Les logos des footers, couvertures et sections restent présents. Voir le [contrôle du catalogue et des conclusions](docs/catalogue-validation.md).
