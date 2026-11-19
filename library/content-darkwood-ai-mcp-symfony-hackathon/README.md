# Darkwood Symfony AI MCP Hackathon

Atelier public : <https://ai-mcp-symfony-hackathon-content.darkwood.com/>.
Cette card possède la page, les ressources publiques et son `dist/`, indépendamment
 de Darkwood Intent. Aucun backend ni appel de modèle n'est exécuté par cette page.

## Sources et génération

- `src/index.html` : source éditoriale statique de l'atelier, reprise du rendu Twig
  précédemment publié par Intent. Modifier cette source pour actualiser l'atelier.
- `public/` : thème Darkwood, scripts, logo, polices Jost et licence OFL,
  trace publique de l'essai Mario du 17 novembre 2026.
- `CNAME` : domaine de publication, inchangé.
- `build.mjs` : assemblage Node 22 natif, sans installation npm ni Composer.
- `validate.mjs` : contrôle des liens, ancres, ressources, métadonnées et liste
  stricte des seuls fichiers publics autorisés.

Depuis cette card, dans son environnement Nix épinglé :

```sh
nix --extra-experimental-features 'nix-command flakes' develop -c node build.mjs
nix --extra-experimental-features 'nix-command flakes' develop -c node validate.mjs
```

L'unique sortie publiable est `dist/`. Elle contient une page, quatorze ressources,
`CNAME` et `.nojekyll`. Les sources et ressources ont été transférées depuis
l'atelier existant ; aucun framework ou système de build supplémentaire n'est
nécessaire. Les copies locales du thème permettent une génération autonome.

## Publication

Le manifeste racine `.github/distribution-cards.json` et le workflow existant
`.github/workflows/publish-github-pages.yml` publient exclusivement ce `dist/`
sur `gh-pages` du dépôt d'artefacts **existant**
`darkwood-com/content-ai-mcp-symfony-hackathon`. Son nom ne change pas.
La GitHub App de distribution conserve ses droits limités à ce dépôt.

Le portail `pages.darkwood.com` est construit séparément par `library/pages` et
publié par GitHub Actions Pages dans le dépôt racine. Ne pas mélanger les artefacts.

## Preuves et limites

La trace `public/hackathon/mcp-proof.json` conserve les réponses de l'essai réel
Mario : SDK MCP officiel, rejet d'une entrée vide et génération IA réelle.
Elle constitue une preuve historique, pas un nouvel appel pendant le build.
Le texte distingue six cards qualifiées et leurs limites. La candidature technique
reste Mario Orchestration, avec son installation et sa démonstration propres.
La vidéo et la soumission finale ne sont pas réalisées par ce refactoring.
Ne pas ajouter de secrets, données privées ou logs fournisseurs aux ressources.
Les polices Jost conservent leur attribution et licence dans `public/fonts/OFL.txt`.

Retour arrière : rétablir une version antérieure de cette card par un commit normal,
puis relancer le même workflow. Le domaine et le dépôt de publication sont conservés.

## Current verified iteration

The main path is designer agent → official SDK client → existing SDK server → generation agent → PHP geometry validation → approved local artifacts. The current proof is public/hackathon/design-proof.json; mcp-proof.json remains available as a dated historical trace. docs/hackathon-review.md distinguishes observations from judgments and compares public entries. Video generation does not establish publication or submission.

## Recorded media

`public/hackathon/actual-demo.mp4` is Darkwood MCP Experience, **80.8 seconds**, with English Navi narration, the workshop tour and the real execution at source commit `e55d2593165b82cbb495d3c14341d610d85a63be`. Idle waiting is explicitly condensed; no response is fabricated. `workshop-fr.mp4` is the complementary **203.2-second** French presentation. Both decks have eight slides. `experience-poster.jpg` comes from the actual film. These static assets do not execute a model. The contest entry has not been submitted.

## Historical iteration 2: dated evidence and avatar

The six card summaries link their public articles and separate current execution,
historical context and simulated behavior. `darkwood-game-proof.json` records a
real Symfony AI observer calling the existing Portal server against isolated local
synthetic data, all four tool checks, approval and quota refusals. Production
checks are discovery and refusal without a key only. Mario remains independent.

The 19 November presentation treatment reuses Navi's existing MATYO91 static
portrait. The short film retains the entire actual 18 November execution, with
the portrait only on intro/outro. This is not a new generation or talking head.
Navi's existing command uses `--avatar=assets/images/avatar.jpg` and
`--avatar-bottom-margin=124`. The French workshop has eleven slides; the English
short version four. The previous media remain recoverable from commit
`2d729174cf76a1baef2ab55ee9527c43a9613446` through a normal revert/republication.

## Darkwood MCP Experience — iteration 3

Eight-step guided story: welcome, workshop discovery, request, available capability,
recorded execution, independent DarkwoodGame observation, inspected result, public
invitation. Both existing SlideWire decks now have eight slides. Their canonical
slugs and the workshop domain are retained; Navi owns the English/French scripts.

The English candidate film uses the existing Navi command and an FFmpeg editorial
assembly of its segments, the actual workshop browser tour and the dated real
Mario capture. Idle waiting is condensed and disclosed in narration and on screen;
the request and successful response are retained. `experience-edit.json` records
the cut list and provenance. No automatic hand-off between cards is implemented
or implied. MCP Apps remains simulated; other historical integrations are context.

MATYO91 remains a static portrait at a consistent size and position. It accompanies
the journey and transitions but is absent from technical execution frames. No
lip-sync, Replicate generation or new renderer is used. Original iteration-2
media remain in S47 and commit `58e9558a60f71cb93a57002953c3179c8ba04684`.
The entry remains prepared, not submitted.
