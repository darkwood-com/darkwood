# Thème Darkwood original — audit, architecture et pilote

## Statut de cette phase

Audit et pilote réalisés le 2026-11-05. Les sources historiques ont été
consultées en lecture seule. Seul le deck `symfony-ai-in-action` a été migré ;
les 27 autres présentations et les workflows Navi restent inchangés. Cette
spécification constitue le point d'arrêt avant une migration globale.

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
est centré, en encre noire (`#111111`), en Jost avec fallback Verdana ; son
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
