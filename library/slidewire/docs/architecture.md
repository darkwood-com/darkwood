# Architecture

## Catalogue et lecteur

`resources/presentations.json` définit chaque présentation par un UUID, un `source_slug` et un titre. `PresentationCatalog::entries()` valide ce catalogue sans écrire dans la base. `routes/web.php` l'utilise pour le catalogue `/` et les routes `/slides/SLUG`.

Les sources sont dans `resources/views/pages/slides/`. Le compilateur du paquet `wendelladriel/slidewire` assemble les composants Blade et fournit les frames du lecteur. Les composants partagés résident dans `resources/views/components/darkwood/`, les styles dans `resources/css/` et les interactions dans `resources/js/`.

Les UUID identifient les présentations, indépendamment des titres, slugs et IDs internes de frames. Un renommage conserve toujours l'UUID.

## Données de publication

La base Laravel est le journal opérationnel :

- `presentations` : catalogue compilé et nombre de slides ;
- `external_publications` : association d'une présentation à un provider ;
- `publication_attempts` : réservation et reprise d'une opération assistée.

Les modèles sont dans `app/Models/`, les services dans `app/Support/` et les commandes dans `app/Console/Commands/`. Les contraintes SQL imposent une association par `(slide_id, provider)`, un propriétaire canonique par `(provider, remote_id)` et une seule tentative active par destination.

`resources/data/publications.json` est une projection publique explicitement exportée depuis la base. Le journal complet est sauvegardé après commit dans `storage/app/private/publications/registry.json`. Cette sauvegarde n'est pas une deuxième source de vérité. Voir [les procédures de publication](publications.md).

`resources/data/presentation-metadata.json` fournit les titres de couverture, auteurs et autres métadonnées publiques utilisés par les rapports. Ces informations ne portent aucune identité de publication distante.

## Exports

`publications:export` compile une présentation, active ses frames dans le lecteur local et produit les pages via Playwright/Chromium. `bin/publication-merge.py` assemble le document, contrôle les polices et calcule son SHA-256.

Les artefacts sont conservés sous `storage/app/publications/UUID/EXPORT/`. Un manifeste de révision empêche la préparation d'un PDF obsolète. Les pages supplémentaires d'une projection statique restent reliées aux slides par `page-map.json`.

## Frontières d'accès

Le catalogue et les présentations sont publics. Les routes de suivi des publications sont désactivées par défaut et interdites en production. L'activation locale exige un utilisateur authentifié explicitement autorisé, un hôte local et une IP loopback.

Les intégrations ne réalisent aucun upload automatique : l'opérateur utilise les interfaces officielles des providers et atteste le résultat. Les cookies et credentials ne font pas partie du registre. Les erreurs publiques sont filtrées ; les logs standards utilisent `RedactSensitiveData`.

## Bibliothèque d’accueil

L’accueil réutilise exclusivement `PresentationCatalog::entries()` et l’ordre du registre. Le paramètre `?presentation=SLUG` sélectionne une présentation connue et fournit une URL partageable ; une valeur inconnue ou structurée est refusée. Un seul iframe, limité aux routes locales enregistrées, affiche le lecteur natif en 16:9. La sélection fonctionne sans JavaScript par navigation HTTP ; le script de l’accueil permet aussi le changement de lecteur sans rechargement et gère l’historique du navigateur.

`resources/css/catalogue.css` et `resources/js/catalogue.js` appliquent uniquement le shell de bibliothèque : header de 61 px, sidebar de 180/240/300 px, Jost, pastille active, thèmes clair/sombre/sépia et icônes soleil/lune/tasse repris des conventions Uniflow. Le menu mobile sous 576 px occupe la largeur disponible, verrouille le scroll, gère Échap et le focus et se referme après sélection ou passage desktop. Le thème persisté concerne le shell ; les présentations intégrées conservent leur identité graphique et leurs contrôles.
