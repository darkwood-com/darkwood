# Design system

Les composants Blade partagés sont dans `resources/views/components/darkwood/`. Les tokens et règles sont définis dans `resources/css/themes/darkwood.css` ; les primitives de contenu et d'interaction sont dans `resources/css/components.css`.

## Palette et tokens

- Bleu : `#007BFF`, token `--dw-blue` et alias `--darkwood-blue`.
- Fonds : blanc, bleu ou noir ; texte noir sur blanc et blanc sur les fonds de couverture/section.
- Corps principal : `--dw-content-font-size: 36px`.
- Bordures et traits contrôlés : `--dw-border-width: 2.5px`.
- Respiration de la zone de contenu : `--dw-body-inset: 8px`.
- Espacements d'en-tête : `--dw-heading-overline-gap: 20px` et `--dw-heading-subtitle-gap: 28px`.

Les images éditoriales gardent leurs couleurs. La coloration syntaxique et les surfaces de code sont les exceptions à la palette de présentation. Les filtres SVG des logos sont générés à partir des tokens par `bin/design-tokens.cjs`.

## Typographie et composition

Verdana Regular est utilisée pour les titres et le footer. À 1440 px de largeur : titre de contenu/section 67,5 px, titre Hero 135 px, surtitre 25,5 px et sous-titre 36 px. Les titres suivent les tokens calculés depuis le canevas 1920 points ; ne pas remplacer ces valeurs par des ajustements locaux.

Le contenu principal, les paragraphes, listes imbriquées, citations, légendes, colonnes et tableaux restent à 36 px. Les glyphes de jeux et schémas ont des dimensions intrinsèques adaptées à leur géométrie. Jost et JetBrains Mono sont fournis localement ; Verdana doit être disponible sur l'hôte de rendu.

La hiérarchie d'en-tête comprend un surtitre facultatif, un `h1` et un sous-titre facultatif. Les marges sont réinitialisées ; aucun espace n'est réservé à un élément absent. La hauteur de ligne des titres vaut 1. Les variantes Hero et section utilisent leurs espacements et approches propres, définis dans les tokens.

`body` centre le contenu dans l'espace disponible sous l'en-tête ; le footer dispose d'une zone réservée. Une composition dense peut défiler dans le lecteur, sans réduire arbitrairement la police. Pour le PDF, utiliser une projection paginée complète ou corriger la composition selon le [guide de publication](publications.md).

Les listes de premier niveau ont des puces pleines, les niveaux imbriqués des puces creuses. Les chaînes longues peuvent être repliées sans masquer leur contenu.

## Footer, logos et navigation

À 1440 px, les libellés du footer utilisent Verdana 25,5 px et le logo occupe 60 × 60 px, avec contour 2,5 px et rayon 10 px. `logo.blade.php` choisit la variante selon le fond : `uni-blue`, `uni-dark` ou `uni-light`.

La navigation est transparente, sans flou, ombre ni compteur. Les états désactivés utilisent une bordure en tirets. La ligne supérieure de progression reflète le numéro réel de slide.

Les flèches utilisent `--dw-arrow-color` et `currentColor` : bleu sur blanc, blanc sur bleu. Les SVG conservent leur contraste et les traits utilisent `vector-effect: non-scaling-stroke` lorsque nécessaire.

## Code et terminaux

Le rendu local utilise Phiki Dark Plus, la palette VS Code Dark et JetBrains Mono, corps 22 px avec hauteur de ligne 1,5. La police et sa licence sont dans `public/darkwood/fonts/JetBrainsMono/`.

Les composants `code`, `code-capture`, `terminal` et `terminal-capture` partagent la même surface opaque. Une barre de titre est affichée seulement si elle fournit un fichier ou un contexte. Éviter les boutons décoratifs, dégradés, flous et ombres.

Les sources de code sont des chaînes lisibles, souvent des nowdoc ; elles ne sont pas exécutées. Les captures éditoriales existantes restent des images.

## Diagrammes, images et contrôles

Les textes, fonds, traits et flèches Mermaid suivent la palette, y compris les styles générés par la bibliothèque. Les schémas utilisent leur hauteur naturelle et conservent leur ratio ; ne pas ajouter de masque fixe qui rogne du texte.

Ne pas ajouter une bordure à un élément qui n'en possède pas et ne pas modifier les traits internes d'une image éditoriale. Les animations gardent leurs états de visibilité et transformations fonctionnels.

Les captures de contrôle utilisent un viewport 1440 × 900 avec DPR 2, sauvegardé à l'échelle CSS. Le PDF utilise un viewport 1440 × 810 et un canevas final 1920 × 1080 points, à échelle uniforme. Les [tests](testing.md) vérifient styles calculés, contrastes, bordures, débordements, code et navigation.
