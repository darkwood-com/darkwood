# Catalogue, flèches et portraits — validation locale

Contrôle du 6 novembre 2026 : **28 présentations, 487 slides**, mêmes identifiants, chemins, ordre, textes, codes et liens. [Galerie des preuves](../storage/app/catalogue/index.html).

## Résultats

- `/` répond 200 et affiche les 28 entrées du registre `resources/presentations.json`, sans doublon. Les 28 routes `/slides/SLUG` et leurs noms existants sont conservés et testés, avec paramètres et fragments. Le catalogue ne synchronise pas la base.
- Homepage contrôlée à 1440 × 900 et 390 × 844 : palette, liens, flèches SVG, bordures 2,5 px, focus, absence de débordement horizontal et d’erreur JavaScript.
- Flèches de navigation bleues sur blanc et blanches sur bleu ; fond réellement transparent, hover/focus/disabled et navigation par bouton/clavier testés. Les composants directionnels, SVG et pseudo-éléments concernés sont contrôlés dans les styles calculés des 487 slides.
- Portrait historique copié une seule fois dans `public/images/authors/matyo91.jpg` ; SHA-256 identique à l’original : `ae35b6d03fe4f1258690a2de9e73d014ac42126154d7bf10333933485967b2e2`. Les fichiers historiques restent inchangés.
- Deux fins contenaient un logo hors footer : **Symfony AI in Action** et **Holding the Core**. Elles utilisent maintenant `darkwood.author-portrait`, auteur `matyo91`, image carrée, cadrage sans filtre ni déformation. Les 26 autres fins gardent leur composition ; les 487 logos de footer et les autres logos restent présents.
- La fin Holding the Core dispose maintenant son illustration et sa signature en colonnes : elles tiennent ensemble à 1440 × 900 et au format PDF, sans modifier les textes ni l’URL.
- Un auteur sans portrait déclaré conserve un logo adapté au fond, identifié par `data-author-fallback="logo"`. Le contact d’un autre auteur ne reçoit pas la photo de matyo91.
- Build Vite réussi ; **34 tests Laravel, 2 780 assertions**, **30 tests JavaScript**, contrôles navigateur et interactions Darkwaar/Pigment X réussis. Aucun changement des scripts des jeux.
- Les deux épreuves de conclusion font chacune une page, en 1920 × 1080 points : portrait incorporé en 640 × 640, polices incorporées, footer Verdana 34 pt, aucun texte hors page. Le lien de jeu Holding the Core reste cliquable. Le traitement des clés d’annotation PDF a été corrigé et vérifié avec ce lien réel.
- **487 captures et 28 planches contact générales actualisées**, plus une planche des 28 dernières slides. Les limites de densité préexistantes restent signalées : 67 slides défilantes ; la fin Holding the Core n’en fait plus partie. Les 24 présentations comportant du contenu défilant/interactif ne sont pas déclarées exportables intégralement. La démonstration externe `/runs` reste une dépendance locale en 404, sans modification de son application.

## Preuves

- [Homepage desktop](../storage/app/catalogue/homepage-desktop.png) · [homepage mobile](../storage/app/catalogue/homepage-mobile.png) · [page complète](../storage/app/catalogue/homepage-full.png).
- [Flèches sur blanc](../storage/app/catalogue/arrows-white.png) · [sur bleu](../storage/app/catalogue/arrows-blue.png) · [sur noir](../storage/app/catalogue/arrows-black.png).
- [Les 28 fins](../storage/app/catalogue/endings-contact-sheet.png) · [les deux portraits](../storage/app/catalogue/portraits.png).
- [Épreuves PDF des deux fins](../storage/app/catalogue/ending-proofs.pdf) — extraits de contrôle, jamais destinés à être publiés comme présentations complètes.
- [Mesures navigateur](../storage/app/catalogue/validation.json) · [contrôle PDF](../storage/app/catalogue/pdf-validation.json) · [galerie générale](../storage/app/design-system/index.html).

## Présentations contrôlées

- **darkwaar20** — 16 slides ; `05d57064-fcd6-478e-a8ae-6767615f8f5d` ; route `/slides/darkwaar20` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/darkwaar20.png).
- **darkwaar27-pigment-x** — 1 slides ; `b0d8f82e-522f-48cf-8082-6bfd282ca93e` ; route `/slides/darkwaar27-pigment-x` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/darkwaar27-pigment-x.png).
- **darkwaar27-pigment** — 15 slides ; `b2a9a7bc-5a65-40d2-9c69-29859cd0f801` ; route `/slides/darkwaar27-pigment` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/darkwaar27-pigment.png).
- **darkwaar33-seedwake** — 14 slides ; `92e37e56-a7c5-4a82-aeb9-6b1eaba329ac` ; route `/slides/darkwaar33-seedwake` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/darkwaar33-seedwake.png).
- **flow-pipe** — 19 slides ; `d35f1e4c-1059-40ad-ac96-5180440f8fe1` ; route `/slides/flow-pipe` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/flow-pipe.png).
- **flow-thispersondoesnotexist** — 14 slides ; `154b792d-19ac-4a34-ab56-7dbef3a26aec` ; route `/slides/flow-thispersondoesnotexist` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/flow-thispersondoesnotexist.png).
- **flowvox-update-2026-05-17** — 30 slides ; `10845f40-41dd-49e6-96b4-5ef1d0b083dd` ; route `/slides/flowvox-update-2026-05-17` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/flowvox-update-2026-05-17.png).
- **from-nolife-testing-to-behavioral-testing** — 26 slides ; `176c71ea-d93a-4e20-8667-386705940709` ; route `/slides/from-nolife-testing-to-behavioral-testing` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/from-nolife-testing-to-behavioral-testing.png).
- **holding-the-core-security-engineering-through-darkwaar40-breach** — 22 slides ; `4245e12c-43d5-4204-b1fb-a8ff64247470` ; route `/slides/holding-the-core-security-engineering-through-darkwaar40-breach` ; portrait matyo91 remplaçant le logo de conclusion. [Capture](../storage/app/catalogue/endings/holding-the-core-security-engineering-through-darkwaar40-breach.png).
- **laravel-pipeline-flow** — 19 slides ; `144410f9-653a-4f06-acc7-032f7c16d6de` ; route `/slides/laravel-pipeline-flow` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/laravel-pipeline-flow.png).
- **llm-game-of-life** — 13 slides ; `2883a530-78d3-4067-a2dd-e4e2af6e2089` ; route `/slides/llm-game-of-life` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/llm-game-of-life.png).
- **mario-ai-agents** — 15 slides ; `32e0ac94-aca0-4c2d-a9c4-520149df813e` ; route `/slides/mario-ai-agents` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/mario-ai-agents.png).
- **nolife-agency** — 13 slides ; `2ae239c4-47cc-44a9-ae68-39933ab8c722` ; route `/slides/nolife-agency` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/nolife-agency.png).
- **nolife-language** — 15 slides ; `bc2771ad-58ad-47e0-bd2f-d6f68b70d28d` ; route `/slides/nolife-language` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/nolife-language.png).
- **nolife-local-runtime-infrastructure** — 25 slides ; `4aeaa678-2fe7-4e76-86eb-344f29e17934` ; route `/slides/nolife-local-runtime-infrastructure` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/nolife-local-runtime-infrastructure.png).
- **nolife-local** — 27 slides ; `89d1ecf3-d4cf-416a-86e8-012b405dc67f` ; route `/slides/nolife-local` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/nolife-local.png).
- **nolife-tokens** — 12 slides ; `a2bb89c4-9da0-4667-87d7-b6db71fc8483` ; route `/slides/nolife-tokens` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/nolife-tokens.png).
- **php-speed-tooling** — 16 slides ; `4b7ab508-68f5-4ed1-be57-88d1d9267066` ; route `/slides/php-speed-tooling` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/php-speed-tooling.png).
- **profiler-flow-blackfire** — 10 slides ; `b9d98b10-453f-4fbc-9ec8-823dbad25bb7` ; route `/slides/profiler-flow-blackfire` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/profiler-flow-blackfire.png).
- **secret-keys-become-application-data** — 15 slides ; `77738729-6ab9-4710-ae0a-02ab90011ad4` ; route `/slides/secret-keys-become-application-data` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/secret-keys-become-application-data.png).
- **shared-workflows-laptop-bottleneck** — 12 slides ; `21f1717c-f698-47ab-915a-d063c68bb140` ; route `/slides/shared-workflows-laptop-bottleneck` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/shared-workflows-laptop-bottleneck.png).
- **sqlite-compiler-php** — 39 slides ; `22ef3877-ced6-4d72-b136-26d889d36d88` ; route `/slides/sqlite-compiler-php` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/sqlite-compiler-php.png).
- **strata-relay** — 12 slides ; `f1e202fc-b950-414e-94c3-711c53001d09` ; route `/slides/strata-relay` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/strata-relay.png).
- **symfony-8-1** — 21 slides ; `00031a3b-f803-4061-a07a-08a4634844b1` ; route `/slides/symfony-8-1` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/symfony-8-1.png).
- **symfony-ai-in-action** — 20 slides ; `ced2e361-eb17-4e83-a695-3213d156a876` ; route `/slides/symfony-ai-in-action` ; portrait matyo91 remplaçant le logo de conclusion. [Capture](../storage/app/catalogue/endings/symfony-ai-in-action.png).
- **thanks-agent** — 16 slides ; `e29b95a9-6373-4031-b0c4-791deabc1a81` ; route `/slides/thanks-agent` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/thanks-agent.png).
- **the-fiber-illusion** — 16 slides ; `988d14e3-2cc4-43a7-9475-2090c3215111` ; route `/slides/the-fiber-illusion` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/the-fiber-illusion.png).
- **where-native-code-lives** — 14 slides ; `a710ce05-b3f8-488a-aa25-3d23836b52e3` ; route `/slides/where-native-code-lives` ; fin conservée, aucun logo de conclusion à remplacer. [Capture](../storage/app/catalogue/endings/where-native-code-lives.png).

## Fichiers de cette intervention

[Manifest comparé à l’instantané de départ](../storage/app/catalogue/changes.json). Les modifications préexistantes sont conservées.

- `README.md` (modified).
- `app/Console/Commands/MakeSlideCommand.php` (modified).
- `app/Support/PresentationCatalog.php` (modified).
- `bin/browser/design.cjs` (modified).
- `bin/publication-export.cjs` (modified).
- `bin/publication-merge.py` (modified).
- `bin/render-presentations.cjs` (modified).
- `config/authors.php` (added).
- `docs/catalogue-validation.md` (added).
- `docs/design-system.md` (modified).
- `docs/design-validation.md` (modified).
- `package.json` (modified).
- `resources/css/themes/components.css` (modified).
- `resources/css/themes/darkwood.css` (modified).
- `resources/views/catalogue.blade.php` (added).
- `resources/views/components/darkwood/arrow.blade.php` (added).
- `resources/views/components/darkwood/author-portrait.blade.php` (added).
- `resources/views/components/darkwood/body.blade.php` (modified).
- `resources/views/components/darkwood/conclusion.blade.php` (modified).
- `resources/views/pages/slides/holding-the-core-security-engineering-through-darkwaar40-breach.blade.php` (modified).
- `routes/web.php` (modified).
- `tests/Browser/catalogue-pdf.py` (added).
- `tests/Browser/catalogue.cjs` (added).
- `tests/Browser/design-results.cjs` (modified).
- `tests/Browser/design-system.cjs` (modified).
- `tests/Feature/CatalogueTest.php` (added).
- `tests/Feature/DesignSystemTest.php` (modified).
- `tests/Feature/PresentationContentTest.php` (modified).
- `public/images/authors/matyo91.jpg` (added).

## Commandes de contrôle

À exécuter dans l’environnement Nix du projet, avec le serveur local sur le port 8017 :

```sh
php artisan test
npm test
npm run build
npm run test:catalogue
npm run test:design
npm run test:interactions
npm run render
npm run test:render-results
npm run render:reading
npm run contact-sheets
python tests/Browser/catalogue-pdf.py
```

Le contrôle PDF nécessite le runtime Python existant contenant pypdf, pdfplumber et pypdfium2. Les quatre présentations intégralement exportables ont également été régénérées et contrôlées avec leur empreinte de sources courante ; voir le [bilan global](design-validation.md).

Aucun compte externe, publication, UUID ou association distante modifié. Aucun commit, push, déploiement, changement de Navi, du VPS-4 ou des sources historiques.
