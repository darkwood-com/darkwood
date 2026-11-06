# Validation du design system Slidewire

État local contrôlé le 6 novembre 2026. **28 présentations, 487 slides** ; nombres, ordre, textes, codes, liens, UUID et événements conservés. Toutes les sources utilisent les composants Darkwood natifs.

## Résultats

- Build Vite réussi depuis les sources, sans lecture des vues compilées en cache. CSS généré : environ 77 Ko.
- Laravel : **34 tests, 2 780 assertions**. JavaScript : **30 tests** de règles et de structure fonctionnelle.
- Pint : les fichiers PHP concernés contrôlés ; `git diff --check` sans erreur.
- Navigateur : **487 captures 1440 × 900**, contrôles de palette calculée, corps et listes à 36 px, bordures à 2,5 px, centrage, assets et absence de débordement horizontal ou du cadre.
- **155 blocs de code** : Dark Plus, fond uniforme et police monospace locale. Contrôles supplémentaires des titres, puces imbriquées, trois fonds de navigation, états hover/active/disabled, code, terminal et traits SVG.
- Catalogue natif : les 28 entrées et routes proviennent du même registre. Homepage desktop/mobile, flèches et deux portraits de conclusion contrôlés ; voir `docs/catalogue-validation.md`.
- Tableau de bord local : palette, focus, accents et contrôles désactivés testés, sans soumission de formulaire.
- Interactions : boutons, réinitialisations, fragments Darkwaar20 et animation automatique complète de Pigment X testés. Les fonctions de calcul des jeux conservent leurs empreintes de référence.
- **28 planches contact** et **135 vues de lecture** pour les compositions denses. [Galerie locale](../storage/app/design-system/index.html), [vue générale](../storage/app/design-system/overview.png), [mesures](../storage/app/design-system/validation.json).

## Limites à valider

**67 slides nécessitent encore du défilement à 1440 × 900.** Leur contenu reste intégralement accessible. Les vues de lecture montrent toute leur hauteur. Elles ne sont pas déclarées prêtes pour un PDF : une composition différente ou une décision éditoriale est nécessaire, sans réduction locale de police ni changement automatique du nombre de slides. [Liste détaillée](../storage/app/design-system/dense-compositions.json).

Les libellés et glyphes des diagrammes/plateaux conservent leur échelle graphique intrinsèque. Les paragraphes internes de Mermaid ne reçoivent pas le corps éditorial : cela fausserait les boîtes calculées par le moteur. Les libellés sont vérifiés dans leurs boîtes de nœuds et les traits restent à 2,5 px sans mise à l’échelle. Les images et captures éditoriales existantes gardent leurs couleurs et leur contenu ; elles ne sont pas retranscrites ou recolorisées.

La démonstration iframe `http://127.0.0.1:8000/runs` de `llm-game-of-life` répond **404**. Le lien est conservé ; son application doit être démarrée séparément pour vérifier cette intégration. Aucun asset Slidewire manquant ni erreur JavaScript n’a été détecté.

Les contrôles à 1440 × 810 distinguent le format PDF 16:9 des captures 1440 × 900. **4 présentations passent intégralement ce précontrôle**. Les 24 autres comportent du contenu défilant et/ou des médias interactifs que le pipeline refuse de couper ou de convertir silencieusement. [Précontrôle de toutes les slides](../storage/app/design-system/pdf-readiness.json).

## PDF canoniques contrôlés

Les 87 pages ci-dessous ont été exportées depuis Slidewire : **1920 × 1080 points**, une page par frame, polices incorporées, footer Verdana 34 pt et aucun caractère hors page. Chacun des quatre PDF a été exporté deux fois depuis la même empreinte de sources, avec des octets et SHA-256 identiques. [Contrôle de reproductibilité](../storage/app/design-system/pdf/reproducibility.json).

- **symfony-8-1** — 21 pages : [PDF](../storage/app/publications/00031a3b-f803-4061-a07a-08a4634844b1/95d76987-64e9-433b-b878-4684f1455c8a/presentation.pdf), SHA-256 `d359d3771f5e1abb6181f6c69daae1ebd89aeabb81fc8b58a335d50b9c7e556f`.
- **flowvox-update-2026-05-17** — 30 pages : [PDF](../storage/app/publications/10845f40-41dd-49e6-96b4-5ef1d0b083dd/9d3dca3b-fd42-42d7-a8ba-0cc25a793cf1/presentation.pdf), SHA-256 `dbf58f9b2a930b94dd88c81bdfab2e60e4890c06c8a4a28d9f67d99279264568`.
- **symfony-ai-in-action** — 20 pages : [PDF](../storage/app/publications/ced2e361-eb17-4e83-a695-3213d156a876/18eb5451-3beb-481e-98eb-efdad2f4ba44/presentation.pdf), SHA-256 `dd6a9647d596e36f161a65cc940eaa0267501fcca975c969db11b0bda56bf56b`.
- **thanks-agent** — 16 pages : [PDF](../storage/app/publications/e29b95a9-6373-4031-b0c4-791deabc1a81/4f712168-3582-469d-8e20-6820b15663e2/presentation.pdf), SHA-256 `fa75604053c9d4bc91234809f3ce7026203d509f20f2477425aca6f8dc685ecb`.

Les aperçus PDF sont dans `storage/app/design-system/pdf/`. Les artefacts courants sont répertoriés dans [exports.json](../storage/app/design-system/pdf/exports.json). La validation de révision empêche de préparer un ancien PDF avec de nouvelles sources.

## Présentations

Chaque entrée dispose de contrôles Blade, contenu, navigation et styles calculés conformes. Les numéros signalent les compositions à reprendre ; ils ne constituent pas une suppression de contenu.

- **darkwaar20** — 16 slides ; [planche contact](../storage/app/design-system/renders/darkwaar20/contact-sheet.png). Composition dense à 900 px : 14. PDF à examiner : slides 14.
- **darkwaar27-pigment-x** — 1 slides ; [planche contact](../storage/app/design-system/renders/darkwaar27-pigment-x/contact-sheet.png). Composition dense à 900 px : aucune. PDF à examiner : slides 1.
- **darkwaar27-pigment** — 15 slides ; [planche contact](../storage/app/design-system/renders/darkwaar27-pigment/contact-sheet.png). Composition dense à 900 px : 3, 14. PDF à examiner : slides 3, 14.
- **darkwaar33-seedwake** — 14 slides ; [planche contact](../storage/app/design-system/renders/darkwaar33-seedwake/contact-sheet.png). Composition dense à 900 px : 7, 9, 11, 14. PDF à examiner : slides 3, 4, 6, 7, 9, 11, 12, 13, 14.
- **flow-pipe** — 19 slides ; [planche contact](../storage/app/design-system/renders/flow-pipe/contact-sheet.png). Composition dense à 900 px : 10, 13. PDF à examiner : slides 9, 10, 13, 19.
- **flow-thispersondoesnotexist** — 14 slides ; [planche contact](../storage/app/design-system/renders/flow-thispersondoesnotexist/contact-sheet.png). Composition dense à 900 px : 4, 7, 9, 10, 11. PDF à examiner : slides 3, 4, 7, 9, 10, 11, 12, 14.
- **flowvox-update-2026-05-17** — 30 slides ; [planche contact](../storage/app/design-system/renders/flowvox-update-2026-05-17/contact-sheet.png). Composition dense à 900 px : aucune. PDF complet contrôlé.
- **from-nolife-testing-to-behavioral-testing** — 26 slides ; [planche contact](../storage/app/design-system/renders/from-nolife-testing-to-behavioral-testing/contact-sheet.png). Composition dense à 900 px : aucune. PDF à examiner : slides 4, 16, 21, 26.
- **holding-the-core-security-engineering-through-darkwaar40-breach** — 22 slides ; [planche contact](../storage/app/design-system/renders/holding-the-core-security-engineering-through-darkwaar40-breach/contact-sheet.png). Composition dense à 900 px : 1, 2, 4, 8, 11, 12, 14, 15, 16, 17, 18, 19. PDF à examiner : slides 1, 2, 4, 6, 7, 8, 9, 11, 12, 13, 14, 15, 16, 17, 18, 19.
- **laravel-pipeline-flow** — 19 slides ; [planche contact](../storage/app/design-system/renders/laravel-pipeline-flow/contact-sheet.png). Composition dense à 900 px : 15, 16. PDF à examiner : slides 12, 15, 16.
- **llm-game-of-life** — 13 slides ; [planche contact](../storage/app/design-system/renders/llm-game-of-life/contact-sheet.png). Composition dense à 900 px : 1. PDF à examiner : slides 1, 12.
- **mario-ai-agents** — 15 slides ; [planche contact](../storage/app/design-system/renders/mario-ai-agents/contact-sheet.png). Composition dense à 900 px : 5, 6, 10, 11, 12. PDF à examiner : slides 5, 6, 10, 11, 12, 13, 14.
- **nolife-agency** — 13 slides ; [planche contact](../storage/app/design-system/renders/nolife-agency/contact-sheet.png). Composition dense à 900 px : 5, 8, 9, 11. PDF à examiner : slides 2, 5, 6, 8, 9, 11, 12.
- **nolife-language** — 15 slides ; [planche contact](../storage/app/design-system/renders/nolife-language/contact-sheet.png). Composition dense à 900 px : aucune. PDF à examiner : slides 8, 9, 14.
- **nolife-local-runtime-infrastructure** — 25 slides ; [planche contact](../storage/app/design-system/renders/nolife-local-runtime-infrastructure/contact-sheet.png). Composition dense à 900 px : aucune. PDF à examiner : slides 5, 7, 8, 10, 11, 15, 17, 19.
- **nolife-local** — 27 slides ; [planche contact](../storage/app/design-system/renders/nolife-local/contact-sheet.png). Composition dense à 900 px : 2, 6, 12, 20, 23, 26. PDF à examiner : slides 2, 3, 5, 6, 8, 11, 12, 20, 23, 26.
- **nolife-tokens** — 12 slides ; [planche contact](../storage/app/design-system/renders/nolife-tokens/contact-sheet.png). Composition dense à 900 px : 2, 5, 6, 9, 12. PDF à examiner : slides 2, 3, 5, 6, 7, 8, 9, 10, 12.
- **php-speed-tooling** — 16 slides ; [planche contact](../storage/app/design-system/renders/php-speed-tooling/contact-sheet.png). Composition dense à 900 px : aucune. PDF à examiner : slides 2, 6.
- **profiler-flow-blackfire** — 10 slides ; [planche contact](../storage/app/design-system/renders/profiler-flow-blackfire/contact-sheet.png). Composition dense à 900 px : 3, 10. PDF à examiner : slides 3, 6, 8, 10.
- **secret-keys-become-application-data** — 15 slides ; [planche contact](../storage/app/design-system/renders/secret-keys-become-application-data/contact-sheet.png). Composition dense à 900 px : 4, 6, 7, 9, 10, 15. PDF à examiner : slides 3, 4, 5, 6, 7, 9, 10, 13, 14, 15.
- **shared-workflows-laptop-bottleneck** — 12 slides ; [planche contact](../storage/app/design-system/renders/shared-workflows-laptop-bottleneck/contact-sheet.png). Composition dense à 900 px : 8. PDF à examiner : slides 1, 8.
- **sqlite-compiler-php** — 39 slides ; [planche contact](../storage/app/design-system/renders/sqlite-compiler-php/contact-sheet.png). Composition dense à 900 px : 9, 28, 29, 32. PDF à examiner : slides 9, 15, 21, 28, 29, 32.
- **strata-relay** — 12 slides ; [planche contact](../storage/app/design-system/renders/strata-relay/contact-sheet.png). Composition dense à 900 px : 1, 2, 9, 12. PDF à examiner : slides 1, 2, 5, 6, 7, 9, 12.
- **symfony-8-1** — 21 slides ; [planche contact](../storage/app/design-system/renders/symfony-8-1/contact-sheet.png). Composition dense à 900 px : aucune. PDF complet contrôlé.
- **symfony-ai-in-action** — 20 slides ; [planche contact](../storage/app/design-system/renders/symfony-ai-in-action/contact-sheet.png). Composition dense à 900 px : aucune. PDF complet contrôlé.
- **thanks-agent** — 16 slides ; [planche contact](../storage/app/design-system/renders/thanks-agent/contact-sheet.png). Composition dense à 900 px : aucune. PDF complet contrôlé.
- **the-fiber-illusion** — 16 slides ; [planche contact](../storage/app/design-system/renders/the-fiber-illusion/contact-sheet.png). Composition dense à 900 px : 7. PDF à examiner : slides 7, 10.
- **where-native-code-lives** — 14 slides ; [planche contact](../storage/app/design-system/renders/where-native-code-lives/contact-sheet.png). Composition dense à 900 px : aucune. PDF à examiner : slides 1, 11.

## Fichiers et composants

- Style natif : `resources/css/themes/darkwood.css`, `components.css`, `resources/css/app.css` ; configuration dans `config/slidewire.php`. Bleu unique, corps 36 px et traits 2,5 px centralisés.
- Sources : les 28 fichiers de `resources/views/pages/slides/`, composants de `resources/views/components/darkwood/` et présentation compilée directement par Slidewire.
- Composants employés : `author-portrait`, `body`, `code`, `columns`, `conclusion`, `content`, `cover`, `deck`, `footer`, `heading`, `section`, `slide`. Les composants `code-capture`, `terminal` et `terminal-capture` partagent la même surface.
- Générateur natif : `app/Console/Commands/MakeSlideCommand.php`, enregistrement dans `AppServiceProvider`, modèles `stubs/slidewire/` et tests `PresentationScaffoldTest`. Les nouveaux decks utilisent directement les mêmes composants, y compris avec Markdown ou plusieurs fichiers.
- Habillage des jeux : `resources/js/darkwaar20/board.js`, `motions.js`, `resources/js/darkwaar27/rules.js`, `widgets.js` ; règles métier conservées.
- Rendu et audits partagés : `bin/browser/`, `bin/render-presentations.cjs`, `contact-sheets.cjs`, `render-slides`, `design-tokens.cjs`, export PDF et génération du filtre SVG au build. JetBrains Mono et sa licence sont fournis localement.
- Tests : `DesignSystemTest`, `PresentationContentTest`, audits navigateur de palette/design/SVG, précontrôle PDF et conservation des règles/interactions.
- Documentation : `README.md`, `docs/design-system.md`, documentation des publications et ce bilan. [Manifest des fichiers concernés](../storage/app/design-system/changes.json).

## Publication et périmètre

Les associations et identifiants distants existants restent conservés. Les PDF courants attendent une validation visuelle ; aucun compte Speaker Deck ou SlideShare n’a été utilisé pour cette intervention. Aucun push, déploiement ni commit n’a été effectué. Les modifications préexistantes sont préservées, avec un instantané local de contrôle. Les sources historiques, Navi et le VPS-4 restent inchangés.
