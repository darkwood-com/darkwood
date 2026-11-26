# Guide d'intégration

1. Inventorier les variables et composants existants avant toute modification.
2. Mapper les rôles locaux vers `--dw-text`, `--dw-background`, `--dw-surface`, `--dw-input`, `--dw-border`, `--dw-primary` et les états.
3. Conserver les noms publics du produit si les renommer casserait une API CSS ou un thème existant.
4. Ajouter Jost localement avec `font-display: swap` ; ne pas charger une police distante sans nécessité.
5. Persister le thème dans le navigateur seulement si l'application le faisait déjà.
6. Vérifier clair, sombre et sépia, puis clavier, contraste, réduction des mouvements, desktop et mobile.
7. Isoler le commit et le déploiement par produit.

## Package partagé

DarkwoodBundle distribue les composants Twig et les actifs communs aux produits Symfony. Les produits qui utilisent une autre pile peuvent réutiliser les ressources statiques depuis le répertoire `public/` du bundle.

## Compatibilité

Les produits peuvent exposer leurs anciens noms de variables comme alias vers les rôles `--dw-*`. Cette stratégie permet une migration progressive sans réécrire les composants métier.

## YouTube video embeds

`youtube.js` is the small shared browser helper for `[data-youtube-id]` blocks.
A `[data-youtube-load]` button explicitly requests the third-party player; no
YouTube network request occurs before that action. Keep a direct HTTPS watch
link next to the player as a no-JavaScript/error fallback.

The helper validates the video ID, uses `youtube-nocookie.com`, lazy loading,
an accessible title, 16:9 sizing and `strict-origin-when-cross-origin`. It never
enables audible autoplay. Include the same source in static exports/Twig pages
instead of duplicating iframe configuration in each card.

## Adoption sans dépendance runtime

Les feuilles locales peuvent reprendre les déclarations de `tokens.css` et exposer les anciennes variables comme alias. Adapter uniquement les sélecteurs de thème au mécanisme déjà utilisé (`data-theme` ou `body.theme-*`). Conserver les composants et leurs exceptions fonctionnelles. Les ateliers et Pages suivent cette méthode ; leur source CSS reste celle qui est assemblée par leur build existant.

Le bleu d’action ne garantit pas à lui seul le contraste d’un petit texte. Utiliser le rôle `--dw-primary-hover` pour les libellés bleus sur fond clair. Pour un bouton à petit texte blanc, assombrir localement le bleu d’action si nécessaire (`color-mix(in srgb, var(--dw-primary) 85%, #000)` dans les ateliers), puis vérifier le contraste dans chaque thème. Le bleu de survol du thème sombre est clair : il ne convient pas comme fond sous un petit texte blanc. Les polices locales conservent leur licence OFL.

## Bootstrap 5.3 et navigation

Sur une interface Bootstrap 5.3, le sélecteur applique une seule valeur parmi
`light`, `dark` et `sepia`. `data-theme` et `data-bs-theme` reflètent ce même
choix. Une ancienne classe `theme-*` peut rester un alias pour les composants
existants. Black correspond à `dark`. Conserver la clé de préférence déjà
utilisée par le produit et valider la valeur relue. Reprendre les icônes SVG d’Uniflow pour les trois thèmes, avec le
[soleil pour Light](../public/icons/light.svg), la [lune pour Dark](../public/icons/dark.svg)
et la [tasse pour Sepia](../public/icons/sepia.svg). Insérer le SVG dans le document
pour qu’il hérite de la couleur du bouton et garder le nom accessible sur le
bouton. La [palette pour Original](../public/icons/original.svg) représente
l’habillage personnalisé du site.

La palette sépia demande des déclarations CSS locales. Mapper `--bs-body-color`
et `--bs-emphasis-color` vers `--dw-text`, `--bs-body-bg` vers
`--dw-background`, `--bs-secondary-color` vers `--dw-muted`,
`--bs-secondary-bg` vers `--dw-subtle` et `--bs-tertiary-bg` vers
`--dw-surface`. Relier les bordures, les liens et le texte des états aux rôles
Darkwood correspondants. Les utilitaires Bootstrap qui consomment des triplets
RGB et les variables propres aux composants demandent une vérification
distincte. Le champ de saisie utilise `--dw-input`, même lorsque la surface
environnante utilise un autre token.

Pages donne la référence pour la navigation. Le header regroupe l’identité du
produit et les actions, dont le thème. Une barre mobile affiche les destinations
principales avec icône décorative, libellé et `aria-current`. Réserver sa hauteur
et la zone de sécurité du bas dans le contenu. Vérifier les champs avec le
clavier ouvert, le focus, le zoom et les menus débordants. Une interface de jeu,
de montage ou de lecture peut garder sa propre navigation.
