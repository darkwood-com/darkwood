# Migration générale Darkwood Original

La révision 6 de `symfony-ai-in-action` a été validée par le propriétaire avant
cette mission. Elle reste la référence ; son fichier Blade et les captures
approuvées ne sont pas réécrits. Les changements concernent exclusivement
Slidewire. Aucun push, déploiement, changement Navi/VPS-4 ou écriture dans les
sources historiques n'est effectué.

## Référence et inventaire

État initial propre : `11953f30b3d635b921283800ad2a2ce9bfe0519a`.
**28 présentations, 487 slides réellement compilées**. L'écart avec les 488
balises initialement recensées correspond à une slide Mario commentée en Blade,
qui n'est pas une slide active. Aucun nombre de slides n'est changé.

Références conservées :

- `tests/fixtures/darkwood-migration-inventory.json` : fichiers, empreintes,
  familles, composants, styles, assets, code, diagrammes, fragments, animations,
  hooks et scripts avant migration ;
- `tests/fixtures/darkwood-migration-baseline.json` : contenu compilé de chaque
  slide, identifiants, fragments, textes, codes, liens et hooks fonctionnels ;
- `tests/fixtures/darkwood-functional-css.json` : déclarations CSS fonctionnelles
  et leurs sélecteurs/at-rules avant nettoyage ;
- `storage/app/darkwood-migration/baseline/` : copies intégrales des 28 sources,
  487 HTML compilés, inventaire navigateur et captures initiales (hors Git).

`bin/darkwood-inventory.php` refuse d'écraser une référence existante.
Le test de conservation utilise les fixtures versionnées et ne dépend donc pas
de la présence du répertoire de captures sur un autre poste.

## Composition commune

Les 27 racines utilisent maintenant `x-darkwood.deck`. Un adaptateur de
compilation, `DarkwoodPresentationCompiler` / `DarkwoodSlideLayout`, compose
leur contenu existant avec les composants partagés `darkwood.heading` et
`darkwood.footer`. Il conserve les identifiants, fragments, attributs Alpine,
hooks JavaScript, code, médias, liens et ordre des slides. Le pilote est exclu
de cet adaptateur et continue d'utiliser directement les composants R6.

Le premier véritable titre h1/h2 devient le h1 de slide. Un contexte réellement
présent reste un p ; un texte introductif associé au titre peut devenir h2.
Les titres internes de contenu restent subordonnés (h3). Les slides sans titre,
notamment les médias, ne reçoivent pas de titre vide ni de rangée d'en-tête vide.

La peau commune `resources/css/themes/darkwood-migration.css` n'agit que sur
`.dw-migrated`. Elle réutilise les tokens R6, notamment les titres Verdana
67,5 px, les Hero 135 px, les sous-titres 36 px, le contexte 25,5 px à
1440 px de large, ainsi que les espacements 20/28 px. Les variantes de
couverture utilisent les espacements historiques non arrondis de R6.
Les noms courts utilisent Hero ; les titres techniques longs utilisent la
composition de couverture standard à 90 points source, sans réduction de
police pour les faire tenir. Les présentations de démonstration conservent une
zone de contenu adaptée à leurs plateaux et commandes.

Le corps est centré dans l'espace restant sous le titre et au-dessus du footer.
Lorsqu'il est plus haut que cet espace, il défile intérieurement : ni le titre,
ni le footer, ni la navigation ne défilent. Ce choix préserve les tailles et les
textes des slides denses ; les slides concernées sont listées dans les mesures.

La configuration ne propose plus les identités default/black/white/aurora/
sunset/neon/solarized. Le thème par défaut est `darkwood-original`. Les scaffolds natifs et les nouvelles
présentations Markdown sont également ramenés à cette identité par le compilateur.
**375 déclarations de peinture concurrentes ont été retirées** avec un parseur
CSS ; un contrôle d'égalité prouve la conservation des autres déclarations,
sélecteurs, transformations, dimensions, opacités et keyframes. Les styles de
structure nécessaires aux démonstrations restent dans leurs sources, au lieu
d'une suppression aveugle de leurs blocs CSS.

## Palette, code et interactions

Les couleurs calculées des éléments, SVG, styles inline, pseudo-éléments,
bordures, sélections et commandes sont contrôlées dans le navigateur. Le code
Phiki/Catppuccin Mocha est une exception explicitement délimitée par les
composants de code ; les images éditoriales conservent leurs pixels intrinsèques.
Les gradients, ombres, flous, fonds de verre et couleurs secondaires sont
neutralisés. Les commandes de navigation restent réellement transparentes,
sans compteur ; leurs états hover/active/disabled restent tricolores.

Les trois logos historiques et leur sélection selon le fond sont réutilisés.
Le glyphe emoji du titre « This Person Does Not Exist » conserve exactement son
Unicode et passe par le filtre monochrome existant. Le composant d’en-tête
centralise ce traitement, conserve l’échappement HTML et l’audit signale les
glyphes de police couleur non quantifiés hors code.
Les anciens footers passent par le footer commun, avec le séparateur `|` pour
les libellés numérotés. Les URL et les exemples de code ne sont pas remplacés.

Les moteurs Darkwaar sont conservés. Trois ajouts JavaScript purement visuels
exposent l'état déjà calculé dans des attributs data : lettres des pigments et
niveaux des quadrants. Des lettres, contours et motifs remplacent les anciennes
couleurs de jeu sans modifier les règles, seeds, événements ou trajectoires.
Un test vérifie les empreintes de tous les scripts d'origine après exclusion
exacte de ces trois ajouts et du remplacement du séparateur du compteur Pigment. Les contrôles Pigment (tip, die, green, undo), leurs
resets, le compteur `0 | 5`, les fragments Darkwaar20 et l'autoplay Pigment-X jusqu’au signal de fin et à l’affichage de son appel à action sont testés réellement.

## Exceptions et limites à valider

- Les opacités de mise en scène **fonctionnelles** des animations Darkwaar et
  fragments sont conservées, mesurées séparément sous `functionalOpacity`.
  Leurs couleurs de peinture restent tricolores. Une opacité partielle produit
  néanmoins des pixels intermédiaires : cette exception d'animation est soumise
  à la validation visuelle, plutôt que de supprimer les étapes démonstratives.
- Les pages incorporées par iframe sont des démonstrations externes, pas des
  composants CSS du thème. Leurs URL et fonctionnalités sont conservées ; leur
  palette interne, souvent sur une autre origine, ne peut pas être garantie par
  le thème. Cette exception technique doit être validée. Les photos, captures
  de jeux et autres illustrations éditoriales gardent leurs couleurs naturelles.
- Le viewer LLM `http://127.0.0.1:8000/runs` renvoie une 404 dans l'environnement
  local. Les démonstrations Mario dépendent des ports 8060–8063. YouTube, X et
  les sites incorporés dépendent de services et politiques d'intégration externes.
  La migration conserve ces intégrations ; elle ne valide pas leurs applications
  métiers ni ne démarre d'autres projets pour les remplacer.
- Dans `nolife-agency`, les composants natifs titre/agenda situés hors des
  articles sont déjà ignorés par le compilateur Slidewire initial. Ils restent
  conservés dans la source ; les 13 slides actives restent 13. Leur éventuelle
  activation exige une décision séparée sur le nombre de slides.
- Les slides denses à défilement interne restent à examiner pour un éventuel
  découpage éditorial futur. Aucun découpage ni changement de texte n'est imposé.

## Reproduction locale

Exécuter les commandes dans l'environnement Nix du projet voisin :

```sh
nix --extra-experimental-features 'nix-command flakes' develop ../darkwood-com
npm run build
php artisan test
node --test tests/Browser/darkwood-functional-css.cjs tests/Browser/darkwood-rules.mjs
PLAYWRIGHT_MODULE=/chemin/vers/playwright node tests/Browser/darkwood-migration.cjs
PLAYWRIGHT_MODULE=/chemin/vers/playwright node tests/Browser/darkwood-verification.cjs
PLAYWRIGHT_MODULE=/chemin/vers/playwright node tests/Browser/darkwood-interactions.cjs
OUTPUT_DIR=storage/app/darkwood-migration/pilot-verification PLAYWRIGHT_MODULE=/chemin/vers/playwright node tests/Browser/darkwood-original.cjs
PLAYWRIGHT_MODULE=/chemin/vers/playwright node tests/Browser/darkwood-dense.cjs
node bin/darkwood-contact-sheets.cjs
node bin/darkwood-evidence.cjs
node bin/darkwood-migration-report.cjs
```

Le serveur doit répondre sur `http://127.0.0.1:8017` (ou `SLIDEWIRE_URL`).
`CHROME` et `MAGICK` permettent de choisir les exécutables locaux.
`DECKS=slug1,slug2` permet une reprise ciblée des captures ;
`SLIDE_NUMBERS=1,2` permet une reprise de slides, en conservant les autres mesures.
Les captures révèlent les fragments via les liens natifs `/f/index` et attendent leur état visible complet avant prise de vue.

## Rendus et résultats

Galerie locale : `storage/app/darkwood-migration/index.html`.

`overview.png`, `before-after.png` et `reference-comparisons.png` présentent
les couvertures, les anciens rendus et les rapprochements avec les sources
historiques. `autoplay-complete.png` montre la séquence Pigment-X terminée.
Chaque répertoire `renders/<slug>/` contient les captures `slide-NN.png` à
1440 × 900 et `contact-sheet.png`. Les mesures sont dans `validation.json`,
les contrôles typographiques/navigation dans `component-verification.json`,
et les scénarios interactifs dans `interactions.json`.

Le pilote repasse ses contrôles R6 dans un répertoire distinct. Les captures
approuvées sont conservées. La comparaison PNG n'est pas identique octet pour
octet : de faibles différences de rasterisation concernent notamment les
icônes et certains tokens/contours de code. Les mesures de composition et les
contrôles typographiques, de palette et de comportement restent la référence
vérifiable ; `pilot-comparison.json` conserve les différences constatées.

<!-- migration-results -->

**Bilan automatisé : 28 présentations, 487 slides, 12 tests Laravel / 1 629 assertions, 30 tests Node réussis.** Build Vite et contrôles navigateur réussis. 155 blocs de code visibles dans les captures, aucune violation de couleur calculée hors exceptions de code, aucun débordement horizontal ou du cadre, aucune image manquante détectée. Les corps qui tiennent dans leur zone sont centrés à moins de 1 px. 21 slides ont un défilement interne ; 20 vues complémentaires permettent leur lecture. 98 mesures d'opacité fonctionnelle sont consignées séparément, sous réserve de validation visuelle de cette exception.

| Présentation | Slides | Famille(s) | Statut | Tests | Planche contact |
|---|---:|---|---|---|---|
| darkwaar20 | 16 | interactif / code | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/darkwaar20/contact-sheet.png) |
| darkwaar27-pigment-x | 1 | interactif | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/darkwaar27-pigment-x/contact-sheet.png) |
| darkwaar27-pigment | 15 | interactif / code | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/darkwaar27-pigment/contact-sheet.png) |
| darkwaar33-seedwake | 14 | interactif / code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/darkwaar33-seedwake/contact-sheet.png) |
| flow-pipe | 19 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/flow-pipe/contact-sheet.png) |
| flow-thispersondoesnotexist | 14 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/flow-thispersondoesnotexist/contact-sheet.png) |
| flowvox-update-2026-05-17 | 30 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/flowvox-update-2026-05-17/contact-sheet.png) |
| from-nolife-testing-to-behavioral-testing | 26 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/from-nolife-testing-to-behavioral-testing/contact-sheet.png) |
| holding-the-core-security-engineering-through-darkwaar40-breach | 22 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/holding-the-core-security-engineering-through-darkwaar40-breach/contact-sheet.png) |
| laravel-pipeline-flow | 19 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/laravel-pipeline-flow/contact-sheet.png) |
| llm-game-of-life | 13 | médias / intégrations externes | Migrée | OK local · iframe² | [Planche](../storage/app/darkwood-migration/renders/llm-game-of-life/contact-sheet.png) |
| mario-ai-agents | 15 | code / médias / intégrations externes | Migrée | OK local · iframe² | [Planche](../storage/app/darkwood-migration/renders/mario-ai-agents/contact-sheet.png) |
| nolife-agency | 13 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/nolife-agency/contact-sheet.png) |
| nolife-language | 15 | code | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/nolife-language/contact-sheet.png) |
| nolife-local-runtime-infrastructure | 25 | code / médias / intégrations externes | Migrée | OK local · iframe² | [Planche](../storage/app/darkwood-migration/renders/nolife-local-runtime-infrastructure/contact-sheet.png) |
| nolife-local | 27 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/nolife-local/contact-sheet.png) |
| nolife-tokens | 12 | code | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/nolife-tokens/contact-sheet.png) |
| php-speed-tooling | 16 | code | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/php-speed-tooling/contact-sheet.png) |
| profiler-flow-blackfire | 10 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/profiler-flow-blackfire/contact-sheet.png) |
| secret-keys-become-application-data | 15 | code | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/secret-keys-become-application-data/contact-sheet.png) |
| shared-workflows-laptop-bottleneck | 12 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/shared-workflows-laptop-bottleneck/contact-sheet.png) |
| sqlite-compiler-php | 39 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/sqlite-compiler-php/contact-sheet.png) |
| strata-relay | 12 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/strata-relay/contact-sheet.png) |
| symfony-8-1 | 21 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/symfony-8-1/contact-sheet.png) |
| symfony-ai-in-action | 20 | code | Référence R6 conservée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/symfony-ai-in-action/contact-sheet.png) |
| thanks-agent | 16 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/thanks-agent/contact-sheet.png) |
| the-fiber-illusion | 16 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/the-fiber-illusion/contact-sheet.png) |
| where-native-code-lives | 14 | code / médias | Migrée | OK¹ | [Planche](../storage/app/darkwood-migration/renders/where-native-code-lives/contact-sheet.png) |

¹ Conservation du contenu et des hooks, compilation Blade, hiérarchie, tailles/espacements, navigation, palette calculée, assets et absence de débordement. Les scénarios interactifs supplémentaires concernent Darkwaar. Le fonctionnement métier et la palette interne des iframes externes restent hors garantie locale.

² Les intégrations externes sont conservées ; leur fonctionnement et leur palette interne ne sont pas validés localement.

### Slides à défilement interne

- `darkwaar20` : 12.
- `darkwaar33-seedwake` : 11, 14.
- `flow-thispersondoesnotexist` : 7.
- `holding-the-core-security-engineering-through-darkwaar40-breach` : 2.
- `laravel-pipeline-flow` : 6.
- `llm-game-of-life` : 1.
- `mario-ai-agents` : 5, 10, 11, 12, 14.
- `nolife-agency` : 8.
- `nolife-local` : 6, 12.
- `nolife-tokens` : 12.
- `secret-keys-become-application-data` : 9.
- `sqlite-compiler-php` : 9.
- `strata-relay` : 9.
- `the-fiber-illusion` : 6, 13.

Les vues `slide-NN-detail-*.png` complètent la capture principale sans ajouter de slides à la présentation.
