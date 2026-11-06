# Design system Slidewire

Slidewire possède une identité native unique : **Darkwood**. Toutes les présentations utilisent les composants `resources/views/components/darkwood/`, les styles `resources/css/themes/darkwood.css` et les primitives de contenu/interactions `components.css`. Le compilateur standard Slidewire lit directement ces sources Blade. Aucun adaptateur de présentation n'est nécessaire.

## Tokens

Le token CSS `--dw-blue` est l'unique définition du bleu **#007BFF**. Les composants utilisent ce token ou son alias `--darkwood-blue`. Les autres couleurs de présentation sont le blanc et le noir. Les SVG de filtre des logos sont dérivés du token au build par `bin/design-tokens.cjs` ; leurs couleurs ne constituent pas une seconde palette à maintenir.

- `--dw-content-font-size: 36px` : sous-titre et contenu principal.
- `--dw-border-width: 2.5px` : bordures existantes, encadrements, séparateurs et traits des diagrammes contrôlés par Slidewire.
- `--dw-body-inset: 8px` : respiration symétrique de la zone de contenu ; les blocs éditoriaux sans encadrement n'ajoutent pas de padding de carte.
- `--dw-heading-overline-gap: 20px` : surtitre vers titre.
- `--dw-heading-subtitle-gap: 28px` : titre vers sous-titre.
- `--sw-code-background` et `--sw-code-foreground` : surface VS Code Dark Plus, réservée au code et aux terminaux.

Les images éditoriales conservent leurs couleurs intrinsèques. Les tokens syntaxiques du code et les surfaces de terminal sont les autres exceptions à la palette. Les accents, liens, contrôles et éléments SVG contrôlés par le design restent tricolores. Les opacités ne servent pas à fabriquer de nouvelles couleurs ; les animations fonctionnelles conservent leurs états de visibilité et leurs transformations.

## Typographie et composition

Hiérarchie : `p.dw-overline` facultatif, un `h1` principal, `h2` facultatif, puis contenu. Les titres de contenu sont noirs, centrés en haut. Couvertures et sections bleues utilisent le blanc ; une couverture noire est également disponible. Les en-têtes réinitialisent les marges et n'ajoutent aucun espace pour un élément absent.

À 1440 px de largeur, Verdana Regular est utilisé pour les titres : titre de contenu/section **67,5 px**, Hero **135 px**, surtitre **25,5 px** ; sous-titre **36 px**. Les titres sont calculés à partir d'un canevas 1920 points (90 points pour le titre, 180 pour Hero), avec hauteur de ligne 1 et approches respectives de 2,99 et 5,98 points source. Le sous-titre et tout le contenu restent explicitement à 36 CSS px. Les coupures de ligne sont naturelles et les longues chaînes peuvent être repliées dans les colonnes et tableaux.

Les variantes conservent leurs séparations propres : titre → sous-titre Hero `100vw × 41.1402 / 1920` (30,85515 px à 1440), section `100vw × 86.5437 / 1920` (64,907775 px). L'approche du sous-titre vaut `100vw × 1.59 / 1920` (1,1925 px à 1440). Ces valeurs ne sont pas arrondies dans les tokens.

Paragraphes, listes de tous niveaux, citations, légendes, titres éditoriaux secondaires, texte des colonnes et tableaux utilisent le même corps de 36 px. Les listes de premier niveau ont une puce pleine ; les niveaux imbriqués ont une puce creuse. Aucun niveau imbriqué n'est réduit.

Le composant `body` centre son contenu dans l'espace restant sous l'en-tête. Le footer occupe sa zone inférieure réservée. Une slide dense conserve le début de son contenu accessible et un défilement interne plutôt qu'un débordement hors écran ou une réduction locale de police. Ces cas doivent être revus avant un export PDF : l'exporteur les refuse tant qu'une composition complète ne tient pas sur une page. Le nombre de slides et les textes ne sont pas modifiés automatiquement.

Les glyphes internes des plateaux, dés, graphes et schémas gardent une taille intrinsèque adaptée à leur géométrie. Il s'agit de labels graphiques, pas d'une réduction du texte éditorial. Les contrôles d'un jeu et la navigation conservent également des dimensions fonctionnelles. Les règles, événements et états des jeux sont indépendants de leur habillage.

## Footer, logos et navigation

Les deux extrémités du footer utilisent Verdana Regular **34 points source** (25,5 px à 1440). Logo : **80 × 80 points source**, soit 60 × 60 CSS px, contour commun de 2,5 px et rayon de 10 px à 1440. Les fichiers sources ne sont pas modifiés :

- Fond bleu : `dw512x512-uni-blue.png`.
- Fond blanc : `dw512x512-uni-dark.png`.
- Fond noir : `dw512x512-uni-light.png`.

`logo.blade.php` choisit la variante selon le fond. Le filtre d'affichage applique la palette native. Les libellés numérotés utilisent `12 | memory` ; le séparateur n'a pas de balise dédiée. Les chemins et textes contenant d'autres `/` ne sont pas réécrits.

La navigation est réellement transparente, sans flou, ombre ou compteur. Ses commandes restent contrastées sur les trois fonds. Les états désactivés sont identifiés par une bordure en tirets, sans gris ni opacité réduite. La progression supérieure est une ligne bleue unie reflétant le numéro réel de slide.

## Code et terminaux

Le rendu est local : **Phiki Dark Plus**, palette VS Code Dark, police **JetBrains Mono** auto-hébergée avec sa licence OFL, corps de code commun 22 px et hauteur de ligne 1,5. La police est fournie dans `public/darkwood/fonts/JetBrainsMono/`. Aucun service de capture distant n'est requis.

La barre de titre, lorsqu'un fichier ou un contexte est fourni, et la zone de contenu ont exactement le même fond opaque. Les barres sans information et les boutons de fenêtre décoratifs sont omis. Pas de dégradé, flou, ombre ou fond externe décoratif.

Composants : `code`, `code-capture`, `terminal`, `terminal-capture`. Ils partagent la même surface. Exemple avec une variable contenant le code brut :

```blade
<x-darkwood.code :source="$source" language="php" title="example.php" />
<x-darkwood.terminal :source="$commands" title="Terminal" />
```

Les sources de code dans les présentations sont des chaînes nowdoc lisibles, transmises au composant ; elles ne sont jamais exécutées. Les captures éditoriales déjà constituées d'images restent des images : leur contenu n'est pas retranscrit ni recolorisé arbitrairement.

La référence [Codeshot](https://codeshot.io/) inspire la présentation locale des fenêtres, la typographie monospace et les espacements. Le thème syntaxique retenu est celui de Phiki Dark Plus, sans mélange avec d'autres thèmes. Police : [JetBrains Mono](https://github.com/JetBrains/JetBrainsMono), licence incluse.

## Diagrammes et bordures

Les diagrammes Mermaid conservent leur source et leur fonctionnement. Le contrat de surface des diagrammes fixe leurs textes, fonds, traits et flèches à la palette native, y compris lorsque la bibliothèque génère des sélecteurs d'ID ou des styles inline. Les règles prioritaires sont limitées à ce rendu tiers ; les autres composants n'utilisent pas une surcouche de peinture par présentation.

Les traits SVG contrôlés utilisent `vector-effect: non-scaling-stroke`. On n'ajoute pas de bordure à un élément qui n'en a pas, et on ne change pas les traits internes des images éditoriales. Les captures se font à un viewport **1440 × 900**, avec `--force-device-scale-factor=2` et DPR 2 pour représenter précisément les demi-pixels, puis sont sauvegardées à l'échelle CSS : les PNG font bien 1440 × 900.

Les traits constitutifs des pictogrammes de navigation et les glyphes ne sont pas épaissis comme des cadres. Les bordures de leurs boutons utilisent le token de 2,5 px.

## Sources, validation et exports

28 présentations et 487 slides actives. Les UUID de `resources/presentations.json`, leurs associations externes, les contenus, les liens et les événements restent stables. Les tests de conservation comparent les contenus canoniques ; les tests des jeux vérifient également que les calculs et règles ne changent pas.

Commandes à lancer dans l'environnement Nix du projet :

```sh
npm run build
php artisan test
node --test tests/Browser/darkwood-rules.mjs tests/Browser/darkwood-functional-css.cjs
node tests/Browser/design-system.cjs
node tests/Browser/darkwood-interactions.cjs
node bin/render-presentations.cjs
node tests/Browser/content-bounds.cjs
node tests/Browser/design-results.cjs
node tests/Browser/darkwood-dense.cjs
node bin/contact-sheets.cjs
node tests/Browser/pdf-readiness.cjs
node tests/Browser/publication-design.cjs
```

Les audits navigateur contrôlent les styles calculés, les pseudo-éléments, les SVG, les listes, les surfaces de code, les bordures, les titres, le centrage et les débordements. Les limites de densité sont consignées dans le rapport de validation ; elles ne sont pas masquées par une diminution du corps.

Les schémas utilisent leur hauteur naturelle dans des conteneurs sans masque fixe. Leur taille maximale dépend de la zone de contenu réelle grâce aux unités de conteneur CSS ; leur ratio est conservé. Les boîtes de mesure Mermaid partagent la règle de retour à la ligne des identifiants longs avec les boîtes affichées. Le contrôle vérifie aussi les masques internes des paragraphes et du code, et l'export PDF refuse tout contenu rogné. Le tableau de bord local partage le bleu des contrôles natifs, du focus et de la sélection ; ses tests ne soumettent aucun formulaire.

Le PDF canonique utilise un viewport 1440 × 810 et un canevas final **1920 × 1080 points**, ratio 16:9, à échelle uniforme. Les polices sont incorporées et les 20 pages du pilote sont contrôlées. Les exports ne déclenchent aucune publication. Voir [le workflow de publication](publications.md). Toute publication externe attend une validation visuelle des nouveaux exports.

Verdana doit être disponible sur l'hôte de rendu. Le contrôle du PDF vérifie la police réellement incorporée dans les deux extrémités du footer, au-delà du nom déclaré en CSS. Jost et JetBrains Mono sont fournis localement. Le [bilan de validation](design-validation.md) recense les rendus, les compositions denses et les dépendances de démonstration à démarrer séparément.

## Générateur natif

`php artisan make:slidewire my-talk --title="Mon titre"` crée une couverture et une slide de contenu dans un fichier Blade. `--multi` crée un dossier avec `deck.blade.php`, `01-intro.blade.php` et `02-content.blade.php`. `--md` conserve cette structure native et utilise Markdown uniquement pour le contenu principal. Les modèles locaux sont dans `stubs/slidewire/` ; la commande applicative remplace le générateur fourni par le paquet.

Le titre est traité comme texte littéral, y compris les caractères HTML, les expressions Blade et les emoji. La date est figée lors de la création. Les sources existantes ne sont pas écrasées implicitement ; `--force` ne permet pas de changer leur format et ne supprime aucun fichier additionnel.

Le générateur ne modifie ni les associations de publication ni le registre canonique : attribuer une fois un UUID stable dans `resources/presentations.json`, puis lancer `publications:catalog`. Les renommages conservent cet UUID. Les tests compilent les deux formats, vérifient le texte littéral et la protection des sources et du registre.

## Catalogue et routes

La route `/` est un catalogue HTML responsive, sans lecteur ni JavaScript nécessaire. `PresentationCatalog::entries()` lit et valide `resources/presentations.json` sans synchroniser la base de données. Ce même registre déclare les routes nommées `slidewire.SLUG`, à l'adresse `/slides/SLUG`. Les 28 chemins et leurs noms existants restent inchangés ; les paramètres de requête et fragments du lecteur continuent de fonctionner. Les titres affichés sont les métadonnées existantes du registre ; les dates et nombres de slides non déclarés ne sont pas inventés.

Pour ajouter une présentation : créer la source native, ajouter une seule entrée avec UUID stable, `source_slug` et `title` au registre, puis lancer `publications:catalog` pour le suivi persistant. Aucune seconde liste de liens ni déclaration de route individuelle n'est nécessaire. Ne pas changer les UUID lors d'une retouche graphique ou d'un changement de titre.

## Flèches et portraits

Les flèches d'interface et les composants directionnels héritent du token `--dw-arrow-color` : bleu sur blanc, blanc sur bleu. La navigation adapte sa couleur à la slide active tout en restant transparente. Les SVG utilisent `currentColor` pour leur remplissage ou leur trait ; les états hover/focus/disabled conservent le contraste, la bordure de 2,5 px et les dimensions existantes. Les caractères directionnels dans le code et les phrases éditoriales restent inchangés. Les contrôles et règles des jeux ne sont pas réécrits.

`config/authors.php` associe un identifiant d'auteur à son nom et à son portrait local. `matyo91` utilise l'unique fichier `public/images/authors/matyo91.jpg`, copié à l'identique depuis la ressource historique. Le composant `<x-darkwood.author-portrait author-id="matyo91" />` génère une image carrée sans filtre ni déformation. Un auteur absent du registre conserve le logo adapté au fond, avec l'attribut explicite `data-author-fallback="logo"`.

Le composant `conclusion` accepte `author-id` ; à défaut il résout le contact déjà déclaré, sans attribuer une photographie à un contact inconnu. La conclusion Symfony utilise le portrait dans le disque existant. La signature de la dernière slide Holding the Core utilise le même composant à la taille de son ancien logo ; son illustration et sa signature sont disposées en colonnes pour rester visibles ensemble. Les 26 autres fins ne possèdent aucun logo de conclusion à remplacer. Tous les logos des footers et les autres compositions restent conservés.

`CatalogueTest` vérifie les routes, l'absence de doublons, les deux emplacements de portrait et les 487 logos de footer. `npm run test:catalogue` vérifie les styles calculés desktop/mobile, les flèches SVG et leurs états, les 28 fins et produit deux épreuves PDF locales. L'audit partagé de chaque slide contrôle aussi les couleurs des composants directionnels et des pseudo-éléments concernés.

Les deux épreuves PDF issues de `test:catalogue` sont assemblées et vérifiées par `python tests/Browser/catalogue-pdf.py`, avec un runtime contenant pypdf, pdfplumber et pypdfium2. Ce contrôle conserve aussi l’annotation du lien de jeu après la mise à l’échelle du canevas. Ces épreuves sont des extraits de validation, distincts des PDF canoniques complets destinés à la publication.
