# Thème Darkwood original — audit, architecture et pilote

## Statut de cette phase

Audit et pilote réalisés le 2026-11-05. Les sources historiques ont été
consultées en lecture seule. Seul le deck `symfony-ai-in-action` a été migré ;
les 27 autres présentations et les workflows Navi restent inchangés. Cette
spécification constitue le point d'arrêt avant une migration globale.

Direction courante : **révision 5 du 2026-11-06**. Hiérarchie sémantique
`p` de contexte facultatif → `h1` de titre → `h2` de sous-titre facultatif,
navigation transparente et contenu centré sous l'en-tête. La palette
`#3579F6` / `#FFFFFF` / `#000000` reste stricte hors des zones de code, où la
coloration Slidewire d'origine est restaurée. Les comptes rendus précédents
documentent les étapes antérieures ; la révision 5 fait autorité.

## Sources historiques inventoriées

### Thème canonique

Répertoire :
`/Users/math/Mathieu/Darkwood/presentations/themes/darkwood`.

- `darkwood.key` : thème Keynote moderne, archive ZIP contenant 27 slides de
  modèle observables, ses styles et ses aperçus.
- `darkwood.pdf` : rendu de référence du thème.
- `fonts/Jost` : huit fontes OTF, graisses 400 à 700, romain et italique.
- `fonts/Verdana` : quatre fontes TTF, romain, gras et italiques.
- `backgrounds` : trois photographies de plage/dunes.
- `logos` : variantes Darkwood et Uniflow, portrait et déclinaisons claires,
  sombres et bleues.
- `icons` : puces circulaires pleines et contour, en SVG et PNG.

### Présentations historiques

Répertoire : `/Users/math/Mathieu/Darkwood/presentations/slides`.

L'inventaire comprend 16 sources Keynote principales et 19 exports PDF de
présentations, de 2006 à 2025. Les exemples les plus utiles pour la fidélité du
système contemporain sont :

- `2021-01-01 uniflow.key` : usage produit/identité avec peu de slides ;
- `2024-08-21 chunk.key` : deck technique long, code, schémas, tableaux,
  citations et sections ;
- `2025-01-30 wave function collapse.key` : contenu technique et visuel récent ;
- `2023-04-25 y-combinator-in-php.key` et les nombreuses versions présentes
  dans `ressources/2021-10-27 ycombinator` : évolution éditoriale d'un sujet ;
- `2021-03-25 railway flow based programming.key` : narration de concepts Flow.

Les anciens paquets Keynote antérieurs ne sont pas tous des archives modernes :
certains sont des répertoires contenant leurs ressources. Les PDF restent la
référence visuelle stable quand le paquet Keynote n'est pas directement lisible.

### Ressources éditoriales

Répertoire : `/Users/math/Mathieu/Darkwood/presentations/ressources`.

Ce corpus contient 9 801 fichiers. Il ne constitue pas un catalogue d'assets à
copier : la majorité est du code source ou de la documentation de recherche.
Les notes observées montrent une méthode éditoriale récurrente :

1. commencer par le « pourquoi » ;
2. rappeler le contexte conceptuel et les travaux antérieurs ;
3. dérouler les concepts du général vers l'implémentation ;
4. illustrer avec du code réel et des références externes ;
5. terminer par une démonstration, une synthèse ou une question.

Références précises :
`ressources/2025-02-30 lambda interpreter/notes.md` et
`ressources/2023-10-29 asynchron recursivity/notes.md`.

## Design system original observé

### Palette

- Bleu de marque : `#3579F6`, confirmé pour cette phase d'ajustement du pilote.
- Fond principal : blanc ou blanc très légèrement cassé.
- Texte principal : noir presque pur.
- Slides de respiration/section : aplat bleu avec texte blanc.
- Code : panneau très sombre, syntaxe colorée, sans transformer toute la slide
  en interface sombre.
- Images : photographie en pleine hauteur, demi-slide ou pleine page ; elles ne
  sont pas systématiquement recouvertes d'un filtre.

Sources : `darkwood.key`, sa série d'aperçus `Data/st-*.jpg`, et
`slides/2024-08-21 chunk.key`.

### Typographie

- Jost est la famille de marque fournie avec le thème. Les titres utilisent une
  graisse moyenne ou normale plutôt qu'un extra-bold massif.
- Verdana est fournie comme famille de lecture ou de compatibilité.
- Les titres sont généralement centrés ou alignés en haut, avec une casse
  naturelle et beaucoup d'espace libre.
- Le corps est compact, noir, sans surabondance de labels en capitales.
- Les slides de section utilisent du blanc sur bleu, avec un titre court.

La police exacte de chaque style Keynote devra être confirmée sur le pilote ;
les seules fontes présentes ne suffisent pas à attribuer chaque rôle avec
certitude.

### Grille, marges et densité

- Format 16:9.
- Marges régulières et généreuses, proches de 4 à 6 % du cadre.
- Une idée principale par slide ; les slides techniques peuvent contenir un
  extrait de code large, mais évitent l'empilement de cartes.
- Mise en page récurrente en une, deux ou trois colonnes.
- Variantes image gauche/droite, image centrée et image pleine page.
- Les tableaux emploient un bandeau d'en-tête bleu, des lignes légères et peu
  d'ornement.
- Les schémas utilisent des formes simples, un contour bleu et beaucoup de
  blanc autour.

### Composants récurrents

- Couverture bleue : titre, sous-titre, date en bas à gauche, auteur et logo en
  bas à droite.
- Couverture noire : alternative observée avec titre blanc.
- Slide de titre simple sur fond blanc.
- Slide de section sur aplat bleu.
- Contenu avec titre et liste à puces noires, sous-puces au cercle vide.
- Deux ou trois colonnes de texte.
- Image et texte en demi-slide.
- Grande image avec titre blanc superposé.
- Citation dans un rectangle bleu, guillemets bleus au-dessus.
- Chiffre clé ou statistiques en grand.
- Processus horizontal, comparaison, tableau, carte, graphiques et icônes.
- Code dans une fenêtre sombre rappelant un éditeur macOS.
- Conclusion « Thanks! » avec portrait, nom, contact et logo.

### Marque, footer et pagination

- Date courte en bas à gauche.
- `@matyo91` et monogramme Darkwood en bas à droite.
- Le footer reste discret et constant sur les slides de contenu.
- Les aperçus historiques ne montrent pas de compteur de page systématique.
- Le monogramme alterne sombre ou clair selon le fond.

### Mouvement

Les sources Keynote contiennent des transitions, mais leur nature exacte n'est
pas extractible de manière fiable depuis les aperçus. Les decks Slidewire ne
doivent donc pas inventer d'animations spectaculaires. La reconstruction
proposée conserve `fade` ou aucune transition, et réserve les fragments à la
progression éditoriale déjà présente.

### Règles observées et hypothèses

Règles fortement observées : fond blanc dominant, bleu de marque, Jost/Verdana,
footer date/auteur/logo, sections bleues, code sombre, images sans effets
gratuitement décoratifs, composition aérée.

Hypothèses à confirmer sur la présentation pilote : tailles typographiques
exactes, interlignage, valeur colorimétrique source non compressée, rayons et
épaisseurs exacts, transitions Keynote et rôle précis de Verdana.

## Audit de Slidewire

### Architecture

- Laravel 13 et PHP 8.3 minimum.
- `wendelladriel/slidewire` 1.4 pour le deck Livewire, les slides, fragments,
  diagrammes et thèmes.
- Tailwind CSS 4 et Vite 8 pour le build.
- GSAP pour certaines animations spécifiques.
- Les routes sont déclarées individuellement dans `routes/web.php`.
- Les decks sont 28 fichiers Blade sous `resources/views/pages/slides`, pour un
  total de 488 slides.
- Les assets partagés sont dans `public/darkwood`; les images propres aux decks
  sont dans `public/images`.
- Le script `bin/render-slides` capture des PNG en 1440 × 900 avec Chrome.

Le paquet Slidewire possède déjà un système de thèmes configurables, une
résolution de fontes, des composants de titre, agenda, colonnes, étapes,
timeline, panneaux, code et diagrammes. Le projet n'a toutefois pas publié de
configuration locale `config/slidewire.php` : les decks utilisent surtout
`theme="black"`, la feuille globale et leurs propres classes.

### État visuel actuel

La feuille `resources/css/app.css` définit actuellement une identité sombre :
fond presque noir, dégradés bleus, grille décorative, transparences, flous,
ombres, cartes et puces arrondies. Cette esthétique n'est pas présente dans le
thème original.

La majorité des decks emploie les classes `dw-*`, mais plusieurs familles ont
leurs propres préfixes (`dw20-*`, `pg-*`, `hc-*`, `sr-*`, `sw-*`) et neuf decks
embarquent un bloc `<style>` local. De très nombreux attributs `style` sont
également présents. Le thème n'est donc pas réellement centralisé aujourd'hui.

### Inventaire des présentations Slidewire

Les 28 decks sont conservés ; aucun n'est à supprimer.

- `symfony-ai-in-action` — 20 slides ; panorama Symfony AI, cartes, flux et
  code ; candidat pilote car il utilise presque uniquement les primitives
  partagées.
- `flow-pipe` — 19 ; discipline des flux/tokens, code, diagrammes et fragments.
- `flow-thispersondoesnotexist` — 14 ; pipeline média/Flow, diagrammes et
  progression fragmentée.
- `flowvox-update-2026-05-17` — 30 ; mise à jour produit, six diagrammes.
- `laravel-pipeline-flow` — 19 ; comparaison de pipelines, code et diagrammes.
- `the-fiber-illusion` — 16 ; concurrence/fibers, code, diagrammes, fragments.
- `profiler-flow-blackfire` — 10 ; performance et profiling, diagrammes.
- `shared-workflows-laptop-bottleneck` — 12 ; architecture distribuée.
- `from-nolife-testing-to-behavioral-testing` — 26 ; tests et comportement.
- `thanks-agent` — 16 ; agents et remerciements, fragments et diagrammes.
- `nolife-agency` — 13 ; agence/organisation, composants Slidewire natifs.
- `nolife-language` — 15 ; langage Nolife, mise en page très personnalisée.
- `nolife-local` — 27 ; runtime local, captures, code et un diagramme.
- `nolife-local-runtime-infrastructure` — 25 ; infrastructure, sept embeds.
- `nolife-tokens` — 12 ; tokenisation, style local limité.
- `llm-game-of-life` — 13 ; LLM et automate, deux embeds.
- `mario-ai-agents` — 16 ; agents de jeu, quatre embeds.
- `sqlite-compiler-php` — 39 ; deck le plus long, code et architecture.
- `php-speed-tooling` — 16 ; outillage et performance PHP.
- `where-native-code-lives` — 14 ; frontières PHP/natif, deux diagrammes.
- `secret-keys-become-application-data` — 15 ; gestion des secrets.
- `symfony-8-1` — 21 ; nouveautés Symfony, diagrammes et code.
- `darkwaar20` — 16 ; jeu, motion spécifique et style `dw20-*`.
- `darkwaar27-pigment` — 15 ; jeu, nombreuses animations et style `pg-*`.
- `darkwaar27-pigment-x` — une slide promotionnelle animée.
- `darkwaar33-seedwake` — 14 ; jeu interactif et style propre.
- `holding-the-core-security-engineering-through-darkwaar40-breach` — 22 ;
  sécurité/jeu et style `hc-*`.
- `strata-relay` — 12 ; jeu interactif et style `sr-*`.

Les decks Darkwaar comportent des interactions applicatives qui doivent garder
leur logique ; seule leur enveloppe éditoriale sera alignée sur Darkwood.

## Matrice de correspondance

### Couverture

- Original : aplat bleu, titre blanc centré, sous-titre, date, auteur et logo.
- Slidewire actuel : fond sombre dégradé, très grand titre gras, kicker et
  diagramme/carte.
- Action : composant `darkwood-title-slide`, variante bleue par défaut et
  variante noire uniquement lorsqu'une référence historique la justifie.

### Section

- Original : aplat bleu, libellé court centré.
- Slidewire actuel : même fond sombre que le contenu, titre très large.
- Action : composant `darkwood-section-slide`, densité minimale.

### Contenu et listes

- Original : fond blanc, titre noir, puces pleines puis creuses, footer stable.
- Slidewire actuel : cartes translucides et labels en capitales.
- Action : layout de contenu blanc ; réserver les cartes aux vrais ensembles
  comparables, utiliser les puces historiques ailleurs.

### Code

- Original : fenêtre sombre isolée sur fond blanc, syntaxe colorée.
- Slidewire actuel : bloc sombre sur slide sombre.
- Action : composant code encadré sur fond blanc, utilisant le highlighter
  Slidewire existant et une palette proche des captures historiques.

### Images et médias

- Original : image pleine page, demi-slide ou image centrée, sans verre ni
  grille décorative.
- Slidewire actuel : embeds encadrés et ombrés.
- Action : composants media plein cadre et split, avec une bordure seulement
  lorsque l'exemple historique en possède une.

### Comparaisons, processus et données

- Original : formes simples, traits bleus fins, tableau à bandeau bleu.
- Slidewire actuel : cartes/pills sombres répétées.
- Action : composants dédiés process, compare et table, sans effet de surface.

### Conclusion

- Original : portrait, « Thanks! », nom en bleu, contact, logo.
- Slidewire actuel : conclusion identique aux slides de contenu.
- Action : composant `darkwood-thanks-slide` réutilisable.

## Architecture proposée

### 1. Thème central

Créer `resources/css/themes/darkwood-original.css`, importé une seule fois par
`app.css`. Il portera des tokens `--darkwood-*` pour la palette, les espacements,
la typographie, le footer, le code et les rapports d'image. Il supprimera les
dégradés, flous et ombres de l'enveloppe Darkwood originale.

Publier une configuration locale `config/slidewire.php` afin de déclarer un
thème `darkwood-original`, plutôt que de modifier le paquet dans `vendor`.

### 2. Composants sémantiques

Créer des composants Blade applicatifs, sous
`resources/views/components/darkwood`, pour : deck, slide, title, section,
content, columns, image-split, quote, code, process, compare, metrics, footer et
thanks. Ils encapsuleront les composants Slidewire existants et resteront
compatibles avec les fragments, diagrammes et animations actuels.

### 3. Couche de compatibilité

Dans un premier temps, remapper les classes `dw-*` les plus répandues vers les
tokens du thème original. Cette couche rendra le pilote rapide et évitera un
big-bang. Les styles inline et mini-thèmes seront ensuite migrés vers les
composants sémantiques deck par deck.

### 4. Assets et licences

- Ne pas copier Verdana dans le dépôt : c'est une police propriétaire ; utiliser
  la fonte système lorsqu'elle est disponible et une fallback métriquement
  proche ailleurs.
- Jost est publiée sous SIL Open Font License 1.1 par son projet officiel. Le
  pilote réutilise uniquement les deux fontes Jost déjà présentes dans
  Slidewire ; aucune copie supplémentaire du corpus historique n'a été ajoutée.
- Les logos Darkwood peuvent être réutilisés après confirmation de propriété.
- Les photographies de plage et le portrait ne doivent pas être copiés tant que
  leurs droits de redistribution ne sont pas établis.
- Conserver un manifeste `resources/darkwood-assets.yml` avec source, auteur,
  licence et usage de chaque asset intégré.

### 5. Validation visuelle reproductible

- Ratio et résolution de référence : 1440 × 900, identiques au script actuel.
- Capturer pour chaque deck : couverture, section, contenu, code, média et fin.
- Produire des planches contact originales et Slidewire à la même taille.
- Ajouter un contrôle des débordements, polices chargées, erreurs console,
  navigation, fragments, impression PDF et mouvement réduit.
- Conserver les captures de validation hors Git, sauf échantillons légers et
  explicitement approuvés.

## Présentation pilote proposée

`symfony-ai-in-action` est le meilleur pilote : 20 slides, couverture, contenu,
grilles, flux, code et conclusion ; peu de styles inline ; aucune interaction
métier complexe. Sa transformation testera le système partagé sans confondre
fidélité graphique et logique de jeu.

Validation minimale avant généralisation :

1. couverture bleue ;
2. section de respiration ;
3. contenu à liste ou colonnes ;
4. slide de code ;
5. slide image/processus ;
6. conclusion Darkwood ;
7. comparaison côte à côte avec `darkwood.key` et
   `slides/2024-08-21 chunk.key` ;
8. build, navigation, PDF et console sans erreur.

## Intégration Navi

Navi ne génère pas le graphisme. Il lit les fichiers Blade Slidewire via
`SLIDEWIRE_SLIDES_DIR`, capture `.slidewire-shell` avec
`assets/tools/capture-slidewire.mjs`, et associe éventuellement des scripts de
narration sous `config/presentation-scripts`.

Le thème doit donc rester entièrement dans Slidewire. Navi n'a besoin d'aucune
duplication graphique : ses captures et vidéos bénéficieront automatiquement
du thème lorsque les decks l'utiliseront. Avant généralisation, le pilote devra
être capturé avec l'outil Navi afin de vérifier la stabilité des sélecteurs, des
dimensions, des fragments et du timing.

## Ordre proposé après validation

1. confirmer les licences et la valeur colorimétrique source ;
2. implémenter le thème central et ses composants ;
3. transformer uniquement `symfony-ai-in-action` ;
4. produire les comparaisons visuelles et corriger le pilote ;
5. valider humainement la fidélité ;
6. migrer les decks techniques utilisant les classes `dw-*` ;
7. migrer les decks avec styles locaux ;
8. traiter séparément les decks interactifs Darkwaar ;
9. exécuter la matrice de tests sur les 28 decks ;
10. vérifier les captures et vidéos Navi.

## Implémentation du pilote

### Thème et tokens

Le thème explicite `darkwood-original` est déclaré dans `config/slidewire.php`
et chargé par `resources/css/themes/darkwood-original.css`. Il ne remplace pas
le thème par défaut des autres decks.

Les tokens centralisés couvrent :

- bleu `#3579F6`, blanc, papier, encre, gris, filets et code ;
- piles Jost/Verdana/monospace ;
- titres, corps, marges, espacements, rayon et footer ;
- fonds blancs et sections bleues ;
- colonnes, processus simples et fenêtre de code sombre.

Les transitions du pilote sont volontairement désactivées : cela correspond à
la sobriété historique et rend les exports déterministes. Les commandes de
navigation et la barre de progression Slidewire restent actives.

### Composants

Les composants anonymes sous `resources/views/components/darkwood` sont :

- `deck`, `slide` et `footer` pour le socle partagé ;
- `cover`, `section`, `content`, `columns`, `code` et `conclusion` pour les
  véritables compositions nécessaires au pilote.

Le composant de code enveloppe le composant natif Slidewire afin de conserver
la coloration syntaxique Phiki. Il ne réimplémente pas le highlighter.

### Typographie et assets

- Jost 400/700 : fontes déjà suivies sous `public/darkwood/fonts/Jost`, sans
  nouvelle copie historique ; projet officiel sous SIL OFL 1.1.
- Verdana : pile système uniquement, jamais redistribuée.
- Logos : copies exactes des trois PNG historiques `dw512x512-uni-blue.png`,
  `dw512x512-uni-dark.png`, `dw512x512-uni-light.png`, sous
  `public/darkwood/logos/`. Voir la sélection automatique ci-dessous.
- Photographies et portraits historiques : non intégrés, car inutiles au
  pilote et non accompagnés ici d'une preuve de redistribution.

### Transformation des 20 slides

Le fichier `resources/views/pages/slides/symfony-ai-in-action.blade.php`
conserve exactement 20 slides et leur contenu éditorial. La distribution est :

- 1 couverture ;
- 3 respirations/sections bleues ;
- 12 slides éditoriales, colonnes ou processus ;
- 3 slides avec code PHP coloré ;
- 1 conclusion.

Les anciennes cartes vitrées, dégradés sombres et effets translucides ont été
remplacés par des compositions blanches, des filets bleus et des formes simples.

## Validation du 2026-11-05

- Build Vite : réussi avec Node 24 fourni par Nix.
- Compilation et cache Blade : réussis avec PHP 8.5 fourni par Nix.
- Rendu : 20/20 captures produites en 1440 × 900.
- Navigation : commandes précédent/suivant et plein écran présentes.
- Polices et logo : chargés depuis les assets locaux.
- Code : trois blocs PHP colorés, sans contenu échappé.
- Débordements : aucun débordement visible sur la planche des 20 slides.
- Non-régression structurelle : aucun des 27 autres fichiers de présentation
  n'a été modifié ; le thème leur reste opt-in.

Les rendus de travail, volontairement hors Git, sont dans
`storage/app/slide-renders/symfony-ai-in-action/` : 20 PNG, une planche contact
et trois comparaisons avant/après représentatives.

### Écarts résiduels

- Le rendu navigateur n'est pas une reproduction binaire de Keynote : les
  métriques de texte et l'anticrénelage diffèrent légèrement.
- Verdana dépend de sa présence sur le système ; Jost prend le relais sinon.
- Les photos historiques ne sont pas utilisées dans ce deck sans contenu photo.
- L'export PDF doit encore être validé humainement si ce canal devient requis ;
  le pilote et les captures navigateur sont fonctionnels.

## Créer ou migrer une présentation

1. Envelopper le deck avec `<x-darkwood.deck>`.
2. Choisir un composant par intention éditoriale, pas par décoration.
3. Garder une idée principale par slide et deux ou trois colonnes au maximum.
4. Utiliser `<x-darkwood.code>` autour de `<x-slidewire::code>` pour le code.
5. Ne pas ajouter de couleurs ou espacements inline : compléter les tokens ou
   un composant partagé si un nouveau besoin historique est démontré.
6. Capturer en 1440 × 900 et vérifier couverture, section, contenu, code et fin.
7. Ne migrer le deck suivant qu'après validation visuelle du précédent.

## Point d'arrêt

Aucune généralisation ne doit commencer avant validation humaine de la planche
du pilote. Les 27 autres présentations restent volontairement inchangées.

## Ajustements graphiques — révision 2 du pilote

### Logos historiques exacts

Le composant unique `<x-darkwood.logo>` sélectionne la variante en fonction de
la surface et hérite du layout via `@aware(['variant' => 'content'])` :

- couverture et section bleues : `dw512x512-uni-blue.png` ;
- contenu et footer sur blanc : `dw512x512-uni-dark.png` ;
- couverture noire : `dw512x512-uni-light.png`.

Une surface locale peut être déclarée avec `background="blue|white|black"`.
La conclusion utilise ainsi le logo bleu dans sa pastille bleue, et le logo
sombre dans son footer blanc. La variante noire est disponible avec
`<x-darkwood.cover background="black" ... />` ; elle est vérifiée dans une
fixture indépendante et n'ajoute pas de slide au pilote.

Les sources viennent de
`/Users/math/Mathieu/Darkwood/presentations/themes/darkwood/logos/`.
Les trois fichiers sont des PNG sRGB de **512 × 512**, copiés sans retouche ;
leurs SHA-256 ont été comparés aux sources et sont identiques. Aucun logo n'a
été généré. Le footer les affiche à 32 × 32 px, sans padding, filtre, recadrage
ou fond CSS ajouté. Les autres usages conservent le ratio carré avec
`object-fit: contain`. Les sources historiques restent en lecture seule.

### Progression et pagination

La barre supérieure garde les 6 px et la position haute du composant Slidewire.
Le remplissage est un aplat `#3579F6`, sans image de fond, dégradé ou opacité ;
la piste restante est blanche et opaque. La surcharge CSS est limitée aux
shells contenant des frames `slidewire-theme-darkwood-original` : le shell
Slidewire lui-même ne porte pas cette classe de thème.

La largeur conserve l'état natif `(index + 1) / count` : 5 % sur la première
slide, 40 % sur la huitième et 100 % sur la vingtième. Le footer lit les mêmes
variables Alpine `index` et `count`, avec deux chiffres et le séparateur `|`.
Il n'existe aucun compteur codé en dur dans les slides. Le compteur est centré
au-dessus des commandes afin de préserver date/libellé, auteur et logo.
Les libellés éditoriaux historiques du pilote (`08 / structure`, etc.) restent
distincts de ce compteur d'avancement.

### Titres et listes

Le composant `content` place désormais son en-tête en haut par défaut. Le titre
est centré, en encre noire, en Jost avec fallback Verdana ; son
échelle est `clamp(2.4rem, 3.4vw, 3.4rem)`. Les marges appartiennent au canvas
Darkwood : le padding supplémentaire des frames Slidewire est neutralisé pour
ce thème. Les couvertures et sections conservent leurs compositions centrées
et leurs titres blancs. La conclusion conserve la composition asymétrique
« Thanks! » observée dans le modèle historique.

Les listes HTML, celles de `.dw-original-list` et les listes produites par
`x-slidewire::markdown` partagent les mêmes règles : marqueur natif `disc`
noir au premier niveau, `circle` noir évidé sur le blanc aux niveaux imbriqués.
Le retrait de 1.35 em, les marqueurs extérieurs et l'interligne de 1.4
conservent l'alignement des lignes repliées. Les niveaux profonds restent
imbriqués, sans pseudo-icône décorative. Les listes ordonnées gardent leur
numérotation native.

La slide 13 « Memory » reprend exactement les trois catégories et leurs textes
dans une liste à deux niveaux ; les 27 autres présentations sont inchangées.

### Validation et rendus à approuver

Rendus hors Git :
`storage/app/slide-renders/symfony-ai-in-action/revision-2/`.

- `slide-01.png` à `slide-20.png` : captures Chrome en 1440 × 900, DPR 1 ;
- `contact-sheet.png` : nouvelle planche contact des 20 slides ;
- `historical-comparison.png` : modèles Keynote à gauche, nouveaux rendus à
  droite (couverture bleue, noire, contenu en colonnes, listes) ;
- `check-blue.png`, `check-black.png`, `check-lists.png` : fixtures des variantes
  et des listes HTML/Markdown à trois niveaux ;
- `validation.json` : mesures et résultats des contrôles navigateur.

Vérifications effectuées :

- build Vite et tests Laravel : 5 tests, 14 assertions réussis ;
- 20 frames conservées, navigation précédent/suivant fonctionnelle ;
- progression exacte sur les 20 positions et séparateur vertical présent ;
- couleurs calculées, épaisseur et absence de dégradé contrôlées ;
- titres noirs centrés en haut sur toutes les slides de contenu ;
- trois variantes de logo réellement chargées, dimensions carrées vérifiées ;
- listes HTML et Markdown : puce pleine puis creuse, y compris au troisième
  niveau, avec contrôle visuel du repli des lignes ;
- sections bleues et trois blocs de code sombres conservés ;
- aucun scroll de frame, débordement horizontal ou chevauchement du footer
  détecté, aucune erreur JavaScript ou réponse HTTP en erreur ;
- revue de la planche des 20 slides et des détails code/listes/logos.

La comparaison utilise les aperçus `Data/st-*.jpg` extraits de `darkwood.key`
dans un répertoire temporaire. Ces aperçus montrent les dispositions natives
des titres, footer, puces et couvertures ; leur résolution et leur format
diffèrent des captures navigateur. Le ratio demandé de 1440 × 900 est conservé
pour les nouveaux rendus. La fidélité visuelle finale reste soumise à validation
humaine ; aucun commit, push ou migration globale n'est autorisé par cette phase.

### Reproduire les contrôles

Depuis Slidewire, entrer dans l'environnement Nix existant :

```bash
nix --extra-experimental-features 'nix-command flakes' develop ../darkwood-com
npm run build
php artisan test
php artisan serve --host=127.0.0.1 --port=8017
```

Dans un second terminal du même environnement, avec Playwright accessible :

```bash
node tests/Browser/darkwood-original.cjs
```

Si Playwright est fourni par le runtime local, indiquer son chemin de module
via `PLAYWRIGHT_MODULE`. `CHROME` permet de choisir l'exécutable Chrome et
`SLIDEWIRE_URL` l'URL du serveur. Le test rend lui-même les fixtures Blade via
PHP, sans créer de route applicative ni modifier le pilote pour les contrôles.
Pour reconstruire la planche avec ImageMagick disponible dans Nix :

```bash
magick montage -label '%t' \
  storage/app/slide-renders/symfony-ai-in-action/revision-2/slide-*.png \
  -font public/darkwood/fonts/Jost/Jost-400-Book.otf -pointsize 17 \
  -thumbnail 360x225 -tile 4x5 -geometry +6+8 -background '#e5e7eb' \
  storage/app/slide-renders/symfony-ai-in-action/revision-2/contact-sheet.png
```

## Révision 3 — palette strictement tricolore et séparateurs simples

### Palette courante

Trois couleurs seulement : bleu `#3579F6`, blanc `#FFFFFF`, noir `#000000`.
Les tokens de gris, papier cassé, bleu secondaire et code anthracite ont été
supprimés de `darkwood-original.css`. La configuration du seul thème
`darkwood-original` utilise désormais `text-black`. Les textes, sous-titres,
listes, bordures, liens et sélections respectent ces trois couleurs.

Les blocs de code ont un fond noir et du texte blanc. Phiki conserve la
syntaxe et les scopes : mots-clés et noms en gras blanc, chaînes et constantes
en bleu, commentaires en italique blanc. Le thème TextMate partagé est
`resources/css/themes/darkwood-original-code.json` ; il fonctionne pour PHP,
JavaScript et les autres grammaires prises en charge par Phiki. Le fallback
sans coloration reste blanc sur noir. La barre de fenêtre utilise bleu,
blanc et un cercle noir bordé de blanc.

`App\Support\DarkwoodCodeHighlighter` est enregistré dans le provider de
l'application et délègue intégralement au highlighter natif pour toute autre
présentation. Il respecte le réglage de désactivation du highlighter. Le
composant de deck `App\View\Components\Darkwood\Deck` établit le contexte
avant le rendu des slots Blade, afin que les composants de code et Markdown
reçoivent effectivement `darkwood-original`, puis nettoie ce contexte.
Le test sur le HTML complet du pilote empêche le retour silencieux de
Catppuccin. Aucun fichier `vendor` n'a été modifié.

### Navigation, progression et états interactifs

Les styles sont limités aux shells contenant des frames Darkwood Original.
L'interface native Slidewire reste fonctionnelle, avec :

- barre de progression bleue opaque de 6 px, piste blanche ;
- panneau de navigation blanc avec bordure noire, sans flou ni ombre ;
- bouton disponible noir, icône blanche ;
- hover bleu, icône blanche ;
- état pressé blanc, icône noire, bordure bleue ;
- focus visible avec contour bleu ;
- bouton désactivé blanc, icône noire et bordure noire discontinue, sans
  diminution d'opacité, avec curseur d'indisponibilité.

Les SVG héritent de `currentColor`. Les liens gardent un soulignement, renforcé
au hover/active, et un contour de focus ; les sélections sont blanches sur bleu.
Aucun dégradé ni transparence colorée n'est utilisé dans ces états.

### Logos : mêmes sources, palette d'affichage normalisée

Les trois PNG historiques restent inchangés et sélectionnés par surface dans
`x-darkwood.logo`. Leur bleu raster d'origine n'est pas exactement `#3579F6`.
Le composant indique donc sa surface via `data-background` et le CSS applique
un filtre SVG partagé : `public/darkwood/filters/tricolor.svg`.

Le logo sur bleu est ramené aux trois couleurs autorisées ; les variantes
sur blanc/noir sont ramenées au noir et blanc. Le seuil alpha du filtre évite
les pixels semi-transparents dans ces logos affichés. Les formes, proportions,
positions et fichiers sources sont conservés. Ce filtre d'affichage remplace
la règle « sans filtre » de la révision 2 ; aucune source historique ni copie
PNG n'a été retouchée et aucun nouveau logo n'a été dessiné.

### Séparateurs sans balise dédiée

Le footer formate seulement les libellés éditoriaux commençant par un numéro :
`12 / memory` devient `12 | memory`. L'expression est ancrée sur le début et
réservée à cette structure, avec un espace de chaque côté du caractère.
Les dates, chemins, URL, expressions PHP et textes tels que
`Flow / Navi / Uniflow` sont conservés.

Le compteur complet est porté par un seul élément sémantique `<output>` :
`08 | 20`, alimenté par l'état natif `index` / `count`. Le séparateur est un
simple caractère du texte, sans span ni composant dédié. La position et la
pile typographique du footer sont conservées. Les 19 attributs `label` du
pilote utilisent automatiquement ce format centralisé ; aucun remplacement
global des `/` n'a été effectué.

### Validation de la révision 3

Rendus : `storage/app/slide-renders/symfony-ai-in-action/revision-3/`.

- `slide-01.png` à `slide-20.png` : captures en 1440 × 900, DPR 1 ;
- `contact-sheet.png` : planche contact actualisée, fond blanc et légendes noires ;
- `check-blue.png`, `check-black.png`, `check-lists.png` : contrôles des logos
  et listes HTML/Markdown ;
- `navigation-hover.png`, `navigation-active.png`, `navigation-disabled.png` :
  états interactifs de navigation ;
- `validation.json` : mesures du rendu et audits de palette pour chaque slide.

Le test navigateur `tests/Browser/darkwood-original.cjs` utilise
`darkwood-palette.cjs` pour examiner les **styles calculés** de tous les éléments
visibles, y compris SVG, `::before`, `::after`, `::marker` et `::selection`.
Il contrôle texte, remplissages, traits, fonds, bordures peintes, contours,
ombres, images de fond, filtres et opacités. Un fond entièrement transparent
est traité comme une absence de peinture ; les couleurs partiellement
transparentes et les opacités intermédiaires sont rejetées. Les styles inline
du highlighter passent par ces mêmes contrôles.

Le test injecte ensuite temporairement un SVG rouge et un pseudo-élément
hors palette, vérifie que l'audit les détecte, puis les retire. Il exerce les
états hover, pressé, focus et désactivé des contrôles, ainsi que ceux d'un lien
et une sélection de texte. Les trois logos affichés sont aussi capturés
séparément et leurs pixels RGBA vérifiés contre la palette exacte.

L'audit CSS concerne les couleurs de peinture, et non les pixels intermédiaires
que le navigateur produit normalement par anticrénelage des textes, traits et
courbes. Cet anticrénelage ne correspond pas à l'ajout d'un token gris ou d'une
couleur d'accent ; les pixels des logos filtrés sont, eux, contrôlés strictement.

Les contrôles de la révision 2 restent actifs : 20 slides, progression réelle,
compteur, libellés, titres centrés en haut, chargement des fontes/logos, navigation
et absence de débordements/chevauchements du footer. Les tests Laravel couvrent
également la palette du highlighter PHP et Markdown/JavaScript, les URL intactes
et l'identité de sortie du highlighter natif sur un thème non concerné.

Résultats du 2026-11-06 : build réussi, **7 tests Laravel / 27 assertions**
réussis, 20 captures et trois fixtures validées, zéro violation de palette
calculée, zéro erreur JavaScript/HTTP et aucun débordement détecté. La couverture
de `flow-pipe`, capturée avant/après en 1440 × 900, conserve exactement le même
HTML de frame et le même PNG ; aucun des 28 fichiers de présentation n'a été
modifié par cette correction centralisée. La planche contact, les trois blocs
PHP, les logos et les captures des états de navigation ont été examinés
visuellement.

Pour reproduire, reprendre les commandes Nix de la section précédente ; le test
navigateur écrit désormais dans `revision-3`. Pour la planche contact, remplacer
également `revision-2` par `revision-3` et utiliser `-background '#FFFFFF'`.
Aucune des 27 autres présentations, aucune source historique, Navi ou VPS-4
n'a été modifié. La validation visuelle humaine reste requise avant commit,
push ou généralisation.

## Révision 4 — métriques historiques, contenu centré et code natif

### Mesures historiques effectives

Les valeurs ci-dessous ont été lues dans une copie temporaire de
`themes/darkwood/darkwood.key`, exportée avec Keynote vers un PPTX temporaire
pour inspecter les styles résolus. Les PDF historiques ont été analysés avec
`pdfplumber` en lecture seule, puis rendus pour les comparaisons visuelles.
La copie, l'export et les mesures intermédiaires restent dans `/tmp` ; aucune
source historique n'a été enregistrée ou modifiée.

La page historique fait **1920 × 1080 points de présentation**. La police des
titres réellement utilisés est **Verdana Regular**, graisse **400** ; le master
générique contient aussi un placeholder 88 pt, mais les titres effectifs ont
un style local à 90 pt. Les valeurs 88 pt du master ne doivent donc pas être
utilisées pour reproduire ces titres.

Mesures des compositions :

- **Contenu** : 90 pt, interligne fixe 90 pt, espacement des lettres 2,99 pt,
  paragraphes avant/après 0 pt, alignement centré. Dans l'export temporaire :
  `ppt/slides/slide16.xml`, forme « Content In Two Columns »,
  `a:lstStyle/a:lvl1pPr/a:defRPr sz="9000" spc="299"`,
  `a:lnSpc/a:spcPts val="9000"` ; le gras hérité vaut `b="0"`.
- **Section / titre simple** : 90 pt, interligne fixe 90 pt, lettres 2,99 pt.
  Export, slide 2 « Presentation title » : `sz="9000"`, `spc="299"`,
  `lnSpc=9000`. Son titre est centré verticalement dans sa composition propre.
- **Couverture Hero** : 180 pt, interligne fixe 180 pt, lettres 5,98 pt.
  Export, slide 1 « Hero » : `sz="18000"`, `spc="598"`, `lnSpc=18000`.
  C'est la variante retenue pour le pilote, également utilisée par les
  couvertures des présentations récentes examinées.
- **Couverture standard alternative** : 90 pt, interligne 90 pt et lettres
  2,99 pt. Elle existe dans le modèle historique ; elle ne remplace pas la
  composition Hero choisie pour ce pilote.

L'unité de `sz`, `spcPts` et `spc` dans cet export est le centième de point.
La police se résout en Verdana dans `ppt/theme/theme1.xml` ; le master fournit
la graisse normale. Les décalages observés entre glyphes dans le PDF confirment
le tracking autour de 3 pt pour un titre 90 pt, avec les ajustements de kerning
et l'arrondi propres au PDF.

Pour un titre de contenu, le haut de la boîte est à **40 pt** ; son inset
supérieur est **121919 EMU / 12700 = 9,5999212598 pt**. Le départ de la ligne
est donc à **49,5999212598 pt**. Le modèle de colonnes possède une boîte de
100 pt de haut ; d'autres boîtes de titre s'adaptent à leur contenu. Les marges
de paragraphe avant/après sont nulles ; les contenus historiques utilisent des
boîtes séparées, fréquemment centrées dans l'espace restant. Le bas de la zone
de contenu n'est pas une marge fixe universelle du titre.

Vérifications croisées dans les exports historiques :

- `themes/darkwood/darkwood.pdf`, pages 1–3 : 180 pt pour Hero, 90 pt pour les
  couvertures standard ; page 16 : titre de colonnes 90 pt ;
- `slides/2024-08-21 chunk.pdf`, page 1 : couverture Verdana 180 pt ; page 2
  « Why? » : titre de section 90 pt ;
- `slides/2025-01-30 wave function collapse.pdf`, page 1 : couverture 180 pt
  sur deux lignes ; pages 3–5 : titres de contenu 90 pt, boîte de glyphes à
  `top=50,5 pt` dans le PDF.

Les références sont consignées dans
`tests/fixtures/darkwood-historical-metrics.json`, utilisées indépendamment
par les assertions navigateur.

### Correspondance CSS et échelle

La conversion est **valeur source × largeur CSS / 1920**. Il ne faut pas
écrire `font-size: 90pt` en CSS : le navigateur appliquerait alors la conversion
physique 1 pt = 4/3 px, qui ne correspond pas à l'échelle de la présentation.

À **1440 × 900** :

- titre de contenu/section : **67,5 px**, interligne **67,5 px** ;
- titre Hero : **135 px**, interligne **135 px** ;
- espacement contenu/section : **2,2425 px** ; Hero : **4,485 px** ;
- haut de la ligne de contenu : **37,1999409449 px** ;
- graisse : **400**, `font-family: Verdana, Jost, …`.

Les tokens CSS utilisent des `calc(100vw * valeur / 1920)`, sans clamp ni
réduction arbitraire des titres longs. L'interligne est fixé à 1 pour ces
styles avec une spécificité suffisante pour dépasser la règle native Slidewire
à 1.2. Chrome a confirmé l'emploi réel de la fonte système Verdana via
`CSS.getPlatformFontsForNode` ; le contrôle ne se limite pas au nom de la pile.

L'échelle reste uniforme. La référence 16:9 produirait une hauteur de 810 px
à 1440 px de large ; le viewport demandé de 900 px apporte 90 px supplémentaires
à la mise en page verticale. Il n'y a ni étirement des lettres ni conversion
par la hauteur. Les titres de contenu restent en haut, noirs et centrés ; les
sections restent blanches sur bleu. Les couvertures conservent leur composition
centrée avec sous-titre et footer. Le footer validé reste ancré au bas du
viewport. Les libellés d'en-tête sont placés dans l'espace supérieur sans
repousser la ligne du titre.

### Zone de contenu réellement centrée

`x-darkwood.content` sépare trois rôles : en-tête, zone `.dw-original-body`
et footer absolu. Le canvas fait 100dvh et comporte deux lignes de grille :
`auto minmax(0, 1fr)`. L'en-tête prend sa hauteur naturelle, y compris les
retours à la ligne à la taille historique. La seconde ligne reçoit exactement
la hauteur restante, au-dessus de la réserve du footer.

Dans cette zone, `.dw-original-body-inner` possède des marges verticales auto.
Les paragraphes, listes imbriquées, images, colonnes, schémas, code et
compositions mixtes utilisent tous le même wrapper. Leur centre est celui de
la zone disponible, et non celui de toute la slide. Le titre et le footer ne
participent jamais à ce centrage. Les espacements internes nécessaires aux
compositions mixtes sont conservés.

Lorsque le contenu est trop haut, les marges auto retombent à zéro et seule
la zone de contenu défile, avec le premier élément accessible dès le haut.
Les tailles de police ne sont pas réduites ; le titre et le footer restent
fixes. Le pilote tient entièrement sans défilement à 1440 × 900. Un test avec
25 paragraphes supplémentaires vérifie le comportement dense et l'accès au bas
du contenu. Les couvertures, sections et composition de conclusion conservent
leurs layouts propres.

### Navigation transparente et suppression du compteur

Le conteneur natif `.slidewire-controls` utilise un vrai
`background: transparent`, sans bordure, ombre ou backdrop-filter. Les boutons
sont également transparents. Leurs contours individuels restent disponibles
pour identifier les commandes, mais aucun panneau ne recouvre la slide.

L'icône et le contour sont noirs sur contenu blanc, blancs sur couvertures ou
sections bleues et sur couverture noire. Une variable de couleur est choisie
par `:has(.slidewire-frame.is-active…)` ; aucun fond n'est simulé. Le hover
renforce le contour, l'état pressé réduit légèrement la commande, le focus a
un contour visible, et le désactivé garde son contour discontinu et son
opacité 1. Les fonctions Alpine natives de navigation ne changent pas.

Le compteur ajouté au-dessus des commandes a été retiré du markup : plus de
`span` ni d'`output` de pagination, et suppression de `.dw-original-pagination`.
Les informations historiques du footer restent présentes, notamment
`12 | memory`, date, auteur et logo. La barre de progression supérieure native
reste active et uniforme.

### Coloration syntaxique originale et exception de palette

Le commit **45713e1**, antérieur à la révision 3, constitue la référence Git.
Il utilisait le moteur natif `CodeHighlighter`, configuré avec
`Theme::CatppuccinMocha`. Les tokens et le fond `#1e1e2e` provenaient de Phiki ;
la fenêtre utilisait `#1d1f21`, sa barre `#292c2f`, et ses trois pastilles
`#ff5f57`, `#febc2e`, `#28c840`.

Ces règles de code ont été restaurées de manière ciblée. Le highlighter
spécifique à la révision 3, son JSON tricolore, sa liaison dans le provider et
le composant de contexte devenu inutile ont été retirés. Le moteur, ses
paramètres, le décodage des blocs Blade et le fallback sont à nouveau ceux de
Slidewire. Aucun reset global ni modification de `vendor` n'a été effectué.

L'audit conserve la palette stricte partout ailleurs. Seules les propriétés
de **couleur** à l'intérieur de `.dw-original-code-window`, `pre.phiki` ou
`pre.slidewire-code` sont exemptées, et leurs couleurs sont consignées à part
sous `codeColors`. Les sélections, dégradés, ombres et opacités restent contrôlés.
Un test injecte la même couleur interdite hors code puis dans un token : elle
est rejetée dans le premier cas et acceptée uniquement dans le second.
Les trois blocs PHP doivent porter `catppuccin-mocha`, avoir le fond natif et
plus de trois couleurs de tokens. Les tests Laravel comparent aussi directement
la sortie au highlighter natif.

### Validation et livrables

Répertoire hors Git :
`storage/app/slide-renders/symfony-ai-in-action/revision-4/`.

- `slide-01.png` à `slide-20.png` : 20 captures en 1440 × 900 ;
- `contact-sheet.png` : planche contact de la révision 4 ;
- `historical-comparison.png` : quatre comparaisons avec les PDF historiques,
  à gauche les références et à droite les rendus du pilote ;
- `navigation-blue.png`, `navigation-black.png`, `navigation-hover.png`,
  `navigation-active.png`, `navigation-disabled.png` : contrôles sur les fonds
  et états demandés ; le fond noir est une fixture temporaire en navigateur,
  sans slide supplémentaire dans le pilote ;
- `dense-content.png` : cas de test du défilement de contenu dense ;
- `check-blue.png`, `check-black.png`, `check-lists.png` : variantes et listes ;
- `validation.json` : mesures de typographie, centrage, code, navigation,
  progression et palette.

Build Vite réussi. Les **7 tests Laravel / 25 assertions** passent. Le test
frontend Playwright vérifie les 20 slides, les tailles issues de la fixture
historique, le centrage à moins de 1 px, la transparence calculée des commandes,
l'absence de markup de pagination, les états interactifs, la navigation clavier,
les trois blocs PHP, les libellés, listes, logos et l'absence de débordement.
Le dépôt ne propose pas d'autre script de test frontend dans `package.json`.

Aucune des 27 autres présentations n'a été modifiée. Le témoin `flow-pipe`
conserve exactement le même HTML de frame et le même PNG qu'avant les
modifications. La planche des 20 slides et les détails code/listes/navigation
ont été examinés visuellement. Les comparaisons historiques gardent la même
échelle horizontale ; leur hauteur peut différer (16:9 source, 1440 × 900 cible).

Pour reproduire, utiliser les commandes Nix et Playwright décrites plus haut ;
le test écrit maintenant dans **revision-4**. La planche contact utilise le
même montage 4 × 5 sur fond blanc. Aucune migration globale, aucun commit ou
push n'a été effectué. Les rendus restent soumis à validation visuelle humaine.

## Révision 5 — rôles éditoriaux et hiérarchie sémantique

### Ce que l'analyse des sources confirme, et ce qu'elle ne confirme pas

L'analyse a été reprise à partir des **formes de texte réellement utilisées**,
pas des classes HTML du pilote ni du placeholder générique de Keynote. Les
sources historiques sont des documents Keynote/PDF : elles ne contiennent
aucune balise `p`, `h1` ou `h2`. La correspondance HTML est donc une décision
sémantique explicite, appuyée sur les rôles Title, Subtitle et Hero observés.

La forme native **Subtitle**, inspectée dans l'export temporaire de
`darkwood.key` sur les slides 1, 2 et 9, donne : `sz=4800`,
`lnSpc/spcPts=4800`, `spc=159`, alignement centré et insets de 127000 EMU
(10 pt). Sa police héritée est Verdana Regular. Les PDF du modèle, ainsi que
le « In PHP » des couvertures de Chunk et Wave Function Collapse, confirment
48 pt. C'est cette règle qui s'applique désormais aux véritables sous-titres,
**pas 90 pt ou 180 pt**.

Les formes Title de contenu/section restent à 90 pt et la forme Hero à 180 pt :
ces correspondances ont été reconfirmées dans les formes nommées du thème et
les exemples récents. Les anciennes longues phrases du pilote ne reçoivent
plus le style Title lorsqu'elles expliquent simplement un sujet nommé.

Aucune forme ou règle historique autonome de « surtitre » n'a été identifiée.
Il serait donc incorrect de présenter sa taille comme une mesure d'un surtitre
historique. Pour les trois véritables indications de contexte du pilote, la
révision 5 **adapte explicitement** le style informatif observé à 34 pt :
Verdana normal, interligne 100 %, tracking nul. Cette taille est attestée pour
les informations de présentation ; son emploi au-dessus d'un titre est une
adaptation éditoriale, pas un emplacement retrouvé dans Keynote.

### Tableau des correspondances historiques / Blade

Toutes les conversions utilisent `valeur source × 1440 / 1920` ; les unités
sont des points du canvas historique, pas des points physiques CSS. Le rôle
est choisi avant la taille.

| Rôle observé dans les sources | Élément sémantique et composant | Taille source → CSS à 1440 px | Interligne source → CSS | Espacement des lettres source → CSS | Graisse, alignement et espacement |
| --- | --- | --- | --- | --- | --- |
| Style informatif 34 pt ; aucun rôle de surtitre nommé retrouvé | `p.dw-overline`, facultatif, dans `x-darkwood.heading` ; adaptation déclarée | 34 pt → 25,5 px | 34 pt → 25,5 px | 0 pt → 0 px | Verdana 400 ; centré ; marge propre 0 ; suit le flux normal, sans position absolue ni capitalisation imposée |
| Title de contenu, « Content In Two Columns » ; titres de contenu de Wave Function Collapse | `h1.dw-original-heading`, via `x-darkwood.content` | 90 pt → 67,5 px | 90 pt → 67,5 px | 2,99 pt → 2,2425 px | Verdana 400 ; noir, centré ; paragraphes avant/après 0 ; boîte source à y=40 pt, inset supérieur 9,5999212598 pt |
| Subtitle natif, présent sur le modèle Hero, la couverture standard et la slide image | `h2.dw-original-subtitle`, facultatif, partagé par tous les layouts | 48 pt → 36 px | 48 pt → 36 px | 1,59 pt → 1,1925 px | Verdana 400 ; centré, couleur héritée du fond ; paragraphes avant/après 0 ; insets natifs de 10 pt |
| Title de section / « Presentation title », exemple « Why? » dans Chunk | `h1.dw-original-section-title`, via `x-darkwood.section` | 90 pt → 67,5 px | 90 pt → 67,5 px | 2,99 pt → 2,2425 px | Verdana 400 ; centré ; blanc sur bleu pour le pilote ; composition centrale de section conservée |
| Hero, confirmé par les couvertures de Chunk et Wave Function Collapse | `h1.dw-original-title`, via `x-darkwood.cover` | 180 pt → 135 px | 180 pt → 135 px | 5,98 pt → 4,485 px | Verdana 400 ; centré ; sous-titre au style Subtitle 48 pt, pas au style Hero |
| Composition de conclusion sur blanc, avec copie à côté du visuel | `h1.dw-original-conclusion-title` puis `h2.dw-original-subtitle` | Titre 90 pt → 67,5 px ; sous-titre 48 pt → 36 px | Respectivement 67,5 px / 36 px | Respectivement 2,2425 px / 1,1925 px | Verdana 400 ; alignement à gauche, exception de composition maintenue |

Les formes historiques sont des boîtes séparées : le modèle place parfois
Subtitle sous le titre de couverture, parfois sous une image. Il n'existe pas
une marge universelle « h1 vers h2 » dans ces sources. Le header réutilisable
adopte donc le **pas mesuré de 10 pt des insets de Subtitle**, soit 7,5 px à
1440 px, entre ses éléments. Ce choix d'adaptation est distingué des tailles
et interlignes effectivement mesurés. Les marges CSS propres des trois éléments
sont nulles ; aucun ancien margin de `p` ne fausse cette grille.

Les textes multilignes conservent leur taille et leurs avances de ligne fixes.
Les mots reviennent naturellement à la ligne dans la largeur disponible ;
aucun clamp de taille ni réduction à la volée n'est appliqué. Le long titre
Hero de Wave Function Collapse confirme ce comportement sur deux lignes.
Les mesures et leur provenance figurent dans
`tests/fixtures/darkwood-historical-metrics.json`.

### Réexamen éditorial des vingt slides

Le détail attendu par slide est consigné dans
`tests/fixtures/darkwood-pilot-headings.json`, utilisé par les tests HTML et
navigateur. La réaffectation est la suivante :

- 1 : « Symfony AI in Action » reste le titre Hero ; l'événement est un contexte
  en `p`, la phrase descriptive devient un vrai `h2`.
- 2 : « Le problème » nomme le sujet en `h1` ; la phrase sur buzzwords/contrôle
  est le sous-titre ; l'explication longue reste dans le contenu.
- 3 : « Vision » est le titre ; la phrase sur la stack est le sous-titre.
- 4 : « Les briques » nomme la section ; « Une stack, pas un helper HTTP. »
  est le sous-titre ; l'inventaire des composants reste du contenu.
- 5 : « Platform » est le titre, sa fonction d'abstraction est le sous-titre.
- 6 : « Le code applicatif reste stable. » reste le véritable titre. « Même
  intention, modèle différent » décrit le contexte de comparaison et reste
  un `p`. Cette slide n'a pas de sous-titre artificiel.
- 7 : « Streaming » est le titre ; la phrase explicative est le sous-titre.
- 8 : « Multimodal » nomme la section ; la phrase sur l'entrée/sortie est son
  sous-titre. La liste des médias et l'explication du pipeline restent du contenu.
- 9 : « Structured Output » est le titre ; le besoin d'objets fiables est le
  sous-titre.
- 10 : « Agent » est le titre ; la définition de sa boucle est le sous-titre.
- 11 : « Tool Calling » est le titre ; la frontière métier est le sous-titre.
- 12 : « Human in the Loop » est le titre ; la décision de l'application est
  le sous-titre.
- 13 : « Memory » est le titre ; sa provenance applicative est le sous-titre.
- 14 : « Store » est le titre ; l'amont du RAG est le sous-titre.
- 15 : « RAG » est le titre ; le contexte vérifiable est le sous-titre.
- 16 : « Multi-agent » est le titre ; l'orchestration lisible est le sous-titre.
- 17 : « MCP » est le titre ; l'interopérabilité est le sous-titre.
- 18 : « Analyse Darkwood » désigne un point de vue et reste un contexte en
  `p`. La phrase sur l'orchestration est le vrai titre ; les questions sont
  son sous-titre.
- 19 : « Stack cible » est le titre ; la liste Symfony AI/MCP/Flow/Navi/Uniflow
  est le sous-titre.
- 20 : la phrase de conclusion reste le titre, son explication devient un `h2` ;
  la composition asymétrique est conservée.

Les **45 textes d'origine** portés par les attributs title/subtitle/overline/lead
ont été vérifiés comme présents après réaffectation, sans réécriture. Les
puces, le PHP, les schémas et les libellés du footer ne changent pas.

### Composants et composition verticale

Le composant commun `x-darkwood.heading` produit un `<header>` avec, dans cet
ordre, un `p` facultatif, un unique `h1`, puis un `h2` facultatif. Le titre doit
être non vide ; les valeurs facultatives nulles, vides ou composées d'espaces
ne produisent aucune balise. Aucun span/div ne simule un titre. Les colonnes
conservent leurs `h3`, subordonnés au titre et au sous-titre de la slide.

Les layouts content, cover, section et conclusion utilisent ce même composant.
Le surtitre fait partie du flux normal en haut de l'en-tête : la position
absolue de la révision 4 a été supprimée. Le titre est suivi de son sous-titre ;
la hauteur complète de cet ensemble est réservée par la première ligne de
la grille. Le contenu reste centré dans la seconde ligne, sous l'en-tête et
au-dessus de la réserve du footer. Les cas denses gardent le comportement de
défilement interne vérifié en révision 4. Couvertures et sections gardent leur
composition centrale, la conclusion son alignement particulier.

### Validation de la révision 5

Rendus hors Git :
`storage/app/slide-renders/symfony-ai-in-action/revision-5/`.

- `slide-01.png` à `slide-20.png` : 20 captures en 1440 × 900, DPR 1 ;
- `contact-sheet.png` : planche contact de la révision 5 ;
- `historical-comparison.png` : quatre rapprochements avec les PDF historiques,
  pour les tailles Title/Subtitle, Hero, les sections et le contenu ;
- `check-blue.png`, `check-black.png`, `check-lists.png` : fixtures de layouts ;
- les captures navigation et dense-content conservent les contrôles des
  propriétés précédemment validées ;
- `validation.json` : structure et ordre des balises, textes des titres,
  métriques de `p`/`h1`/`h2`, centrage, code, palette et navigation.

Les tests vérifient individuellement les vingt titres, leurs trois véritables
contextes et leurs dix-neuf sous-titres, sans balise vide ni `h1` concurrent.
Ils contrôlent les tailles, graisses, interlignes, familles, tracking, couleurs
et ordre visuel, ainsi que la hauteur réelle disponible pour le contenu.
Le cas sans sous-titre est vérifié sur la slide 6 et sur les fixtures.

Les autres contrôles restent actifs : palette tricolore hors du code,
Catppuccin Mocha sur les trois blocs PHP, navigation transparente sans compteur,
barre bleue, logos adaptés au fond, listes pleines/creuses, séparateurs `|`,
footer, absence de débordements et comportement dense.

Résultats du 2026-11-06 : build Vite réussi, **9 tests Laravel / 150 assertions**
réussis et les 20 captures validées par le test navigateur. Aucun débordement,
erreur JavaScript/HTTP ou violation de palette hors code n'a été détecté.
La planche contact et les détails des slides 3, 6, 8, 18 et 20 ont été revus.
Le témoin non migré `flow-pipe` conserve le même HTML et le même PNG avant/après.

Reproduction : mêmes commandes Nix, build Vite, tests Laravel et Playwright
que précédemment. Le test navigateur écrit maintenant dans **revision-5**.
Aucune source historique, aucun des 27 autres decks, Navi ou VPS-4 n'a été
modifié. La généralisation reste conditionnée à la validation visuelle humaine ;
aucun commit ni push n'a été effectué.
