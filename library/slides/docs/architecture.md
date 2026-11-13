# Architecture

## Catalogue et lecteur

`resources/presentations.json` définit chaque présentation par un UUID, un `source_slug` et un titre. `PresentationCatalog::entries()` valide ce catalogue sans écrire dans la base. `routes/web.php` l'utilise pour le catalogue `/` et les routes `/slides/SLUG`.

Les sources sont dans `resources/views/pages/slides/`. Le compilateur du paquet `wendelladriel/slidewire` assemble les composants Blade et fournit les frames du lecteur. Les composants partagés résident dans `resources/views/components/darkwood/`, les styles dans `resources/css/` et les interactions dans `resources/js/`.

Les UUID identifient les présentations, indépendamment des titres, slugs et IDs internes de frames. Un renommage conserve toujours l'UUID.

## Données publiques de publication

La base Laravel restitue les associations publiques :

- `presentations` : catalogue compilé et nombre de slides ;
- `external_publications` : association d'une présentation à un provider ;
- `publication_attempts` : compatibilité avec les données historiques.

Les contraintes SQL imposent une association par `(slide_id, provider)` et un
propriétaire canonique par `(provider, remote_id)`.

`resources/data/publications.json` est une projection publique explicitement
exportée depuis la base. Les procédures fournisseur et les journaux opérateur
appartiennent au plan de contrôle privé. Voir [l'interface publique](publications.md).

`resources/data/presentation-metadata.json` fournit les titres de couverture, auteurs et autres métadonnées publiques utilisés par les rapports. Ces informations ne portent aucune identité de publication distante.

## Exports

`publications:export` compile une présentation, active ses frames dans le lecteur local et produit les pages via Playwright/Chromium. `bin/publication-merge.py` assemble le document, contrôle les polices et calcule son SHA-256.

Les artefacts sont conservés sous `storage/app/publications/UUID/EXPORT/`. Un manifeste de révision empêche la préparation d'un PDF obsolète. Les pages supplémentaires d'une projection statique restent reliées aux slides par `page-map.json`.

## Frontières d'accès

Le catalogue et les présentations sont publics. Aucune route HTTP de pilotage
des fournisseurs n'est enregistrée. Les commandes Artisan de catalogue,
d'export, de manifeste, d'association et de diagnostic constituent l'interface
locale explicite. Elles ne réalisent aucun upload et n'acceptent aucun cookie ou
credential fournisseur. Les logs standards utilisent `RedactSensitiveData`.

## Bibliothèque d’accueil

L’accueil réutilise exclusivement `PresentationCatalog::entries()` et l’ordre du registre. Le paramètre `?presentation=SLUG` sélectionne une présentation connue et fournit une URL partageable ; une valeur inconnue ou structurée est refusée. Un seul iframe, limité aux routes locales enregistrées, affiche le lecteur natif en 16:9. La sélection fonctionne sans JavaScript par navigation HTTP ; le script de l’accueil permet aussi le changement de lecteur sans rechargement et gère l’historique du navigateur.

`resources/css/catalogue.css` et `resources/js/catalogue.js` appliquent uniquement le shell de bibliothèque : header de 61 px, sidebar de 180/240/300 px, Jost, pastille active, thèmes clair/sombre/sépia et icônes soleil/lune/tasse repris des conventions Uniflow. Le menu mobile sous 576 px occupe la largeur disponible, verrouille le scroll, gère Échap et le focus et se referme après sélection ou passage desktop. Le thème persisté concerne le shell ; les présentations intégrées conservent leur identité graphique et leurs contrôles.
