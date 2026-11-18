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

L'unique sortie publiable est `dist/`. Elle contient une page, sept ressources,
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
