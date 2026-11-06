# Publications externes depuis Slidewire

## Identité et persistance

`resources/presentations.json` attribue une fois pour toutes un UUID à chacune des 28 présentations. **Ne jamais régénérer ce registre.** Pour renommer une présentation, conserver son `id`, modifier `source_slug` et renommer sa source/route. Les titres peuvent changer sans changer l'UUID. Hors pilote, les titres de métadonnées initiaux issus des noms de fichiers sont à relire avant publication ; aucune description éditoriale n'est inventée.

`presentations` persiste le registre et le nombre de frames compilées. `external_publications.slide_id` référence cet UUID de présentation, pas l'ID d'une frame. Contraintes : une association par `(slide_id, provider)` et une seule présentation canonique par `(provider, remote_id)`. Les suppressions en cascade sont interdites.

`publication_attempts` réserve l'action assistée avant l'upload. La contrainte unique `(external_publication_id, active_slot)` avec `active_slot = 1` interdit plusieurs tentatives actives. Les tentatives terminées ont une valeur nulle. Une interruption ne libère jamais automatiquement la réservation.

Une association d'un ancien deck garde `source_hash`, `published_at` et `last_synced_at` inconnus : confirmer la correspondance éditoriale ne prouve pas la date de publication ni l'identité du PDF distant.

## Utilisation locale

Utiliser l'environnement Nix existant, sans installation système :

```sh
nix --extra-experimental-features 'nix-command flakes' develop ../darkwood-com
php artisan migrate
php artisan publications:catalog
php artisan serve --host=127.0.0.1 --port=8017
```

Tableau de bord : `http://127.0.0.1:8017/publications`. Accès limité à l'environnement local et aux requêtes loopback ; formulaires CSRF et données échappées. Ajouter une authentification administrateur avant toute exposition en production. Le lecteur reste inchangé.

## Export canonique

L'impression du lecteur documentée précédemment ne garantissait pas la capture des frames cachées. La nouvelle commande exporte chaque frame active dans l'ordre du compilateur, avec les fragments dans leur état final :

```sh
php artisan publications:export UUID
```

Prérequis : Node, Playwright, Chrome, Python et pypdf ; pypdfium2 pour les aperçus. `PLAYWRIGHT_MODULE`, `CHROME` et `PUBLICATION_PYTHON` peuvent désigner des runtimes existants. L'exporteur ne lit aucun cookie ni navigateur connecté et visite exclusivement `http://127.0.0.1:PORT`.

Rendu d'export **1440 × 810 CSS px**, pages Chromium **1080 × 607,5 points** (96 px = 72 points), agrandies uniformément en vecteurs à **1920 × 1080 points**, ratio **16:9**. Le lecteur s'adapte également à un écran 1440 × 900. L'échelle native du navigateur est fixée à 2 pour calculer exactement les bordures de 2,5 px ; les captures sont sauvegardées à l'échelle CSS. Navigation exclue, design/progression/footer conservés. Contrôles : compte compilé/navigateur/PDF, images et polices, erreurs réseau/JS, palette calculée, débordements, défilement, médias interactifs, texte sélectionnable, polices incorporées (dont glyphes Type3), dimensions et SHA-256 final.

Une iframe, vidéo, canvas ou zone défilante provoque un refus : demander une décision sur sa représentation statique sans supprimer de contenu, réduire arbitrairement les polices ou ajouter des pages. Un PDF ne reproduit pas les interactions de Slidewire.

Les exports sont isolés sous `storage/app/publications/UUID/EXPORT_UUID/` : manifeste, captures fraîches, pages intermédiaires, PDF et validations JSON. Métadonnées temporelles et IDs aléatoires du PDF final sont normalisés. Comparer deux exports frais pour vérifier leur reproductibilité dans le même environnement. Une empreinte conservatrice des sources, compilateur, configuration, assets, renderer et locks interdit de préparer un export obsolète. Une modification sans effet visuel peut imposer une régénération ; seul le hash du PDF final détermine son identité publiée.

Contrôle du PDF réel (pas seulement des captures avant export) :

```sh
python bin/publication-preview.py CHEMIN/presentation.pdf
```

## Publication assistée

Consulter [l'audit des providers](publication-providers.md) puis les comptes pour exclure les doublons. Aucune API d'écriture ni cookie extrait n'est utilisé.

```sh
php artisan publications:prepare UUID speakerdeck CHEMIN/presentation.pdf
```

Cette commande réserve une tentative, **sans transmettre le fichier**. Téléverser le fichier exact dans l'interface officielle, relire les métadonnées, publier, vérifier les pages et l'accès public. Relever l'ID réel (Speaker Deck : ID hexadécimal du player ; SlideShare : ID numérique de l'URL canonique).

```sh
php artisan publications:complete TENTATIVE_UUID REMOTE_ID URL \
  --confirmed-exact-artifact --confirmed-public-access
```

La validation enregistre l'association, le hash du fichier téléversé et les dates puis clôt la tentative. Il s'agit d'une **attestation explicite de l'opérateur**, pas d'une comparaison avec le hash d'un PDF distant. L'oEmbed Speaker Deck vérifie compte et ID publics via `SpeakerDeckProvider::getPublication`, pas les octets du document. SlideShare exige une vérification web.

## Association et mises à jour

Formulaire local ou :

```sh
php artisan publications:associate UUID PROVIDER REMOTE_ID URL --confirmed-correspondence
```

Ne jamais déduire une correspondance d'un titre ou slug. L'association existante ne peut pas être remplacée par cette commande. Un PDF identique à l'empreinte attestée ne crée aucune tentative ; un PDF modifié retourne `update_available`, sans doublon. Sur Speaker Deck, `publications:prepare ... --confirmed-replacement` réserve un remplacement après confirmation de l'opération publique. Utiliser Reupload et conserver l'ID existant. Aucun remplacement non confirmé SlideShare ni suppression distante n'est implémenté.

## Erreurs et reprises

```sh
php artisan publications:reconcile TENTATIVE_UUID --error=upload_failed
```

Erreurs admises : `interrupted`, `authentication_failed`, `upload_failed`. La tentative reste active et bloque une nouvelle création. Vérifier le compte : si la publication existe, reprendre `complete` avec son ID, sans recréer. Si la création n'a réellement produit aucune publication, l'opérateur peut clôturer avec `--confirmed-no-remote-publication` après contrôle. Ce drapeau est interdit pour un remplacement. Un timeout ne prouve jamais l'absence de publication.

## Inventaire et validation

[Inventaire compilé](publication-inventory.json) : 28 UUID, 487 frames actives. Le suivi de publication ne réécrit pas les sources ; les évolutions du design conservent les associations. Aucun test ne publie réellement : réponses HTTP simulées, contrôles d'unicité, stabilité, erreurs, interruptions et prévention des doublons.

La référence de composition `symfony-ai-in-action` compte 20 pages. Les nouveaux exports doivent appliquer le [design system natif](design-system.md) et être validés avant toute publication. [L'état des publications](publications-status.md) conserve les identifiants et empreintes des fichiers effectivement publiés. Une intervention graphique locale ne modifie pas les comptes externes.
