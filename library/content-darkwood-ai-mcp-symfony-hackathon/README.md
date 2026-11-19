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

L'unique sortie publiable est `dist/`. Elle contient une page, douze ressources, une page 404,
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
La génération vidéo et la soumission finale ne sont pas déclenchées par le build statique.
Ne pas ajouter de secrets, données privées ou logs fournisseurs aux ressources.
Les polices Jost conservent leur attribution et licence dans `public/fonts/OFL.txt`.

Retour arrière : rétablir une version antérieure de cette card par un commit normal,
puis relancer le même workflow. Le domaine et le dépôt de publication sont conservés.

## Current verified iteration

The main path is designer agent → official SDK client → existing SDK server → generation agent → PHP geometry validation → approved local artifacts. The current proof is public/hackathon/design-proof.json; mcp-proof.json remains available as a dated historical trace. docs/hackathon-review.md distinguishes observations from judgments and compares public entries. Video generation does not establish publication or submission.

## Vidéos et narration continue — itération 4

- [Darkwood MCP Experience](https://youtu.be/IbljHH4wj0s), anglais, 86,21 s,
  non répertoriée ; source et résultat réellement enregistrés, attente condensée.
- [Présentation française](https://youtu.be/Ytts4XFgTxI), narration continue,
  232,88 s, non répertoriée.

Les identifiants, la chaîne et les anciens chemins sont conservés dans
`public/hackathon/experience-edit.json`. Les huit slides restent dans la card
Slides ; les narrations appartiennent à Navi. Le mode continu produit une seule
prise vocale, mesure cette prise, aligne ses repères et monte les visuels sans
couper ni fondre la voix. Le portrait MATYO91 est statique et laisse l’exécution
dégagée. Aucun appel Replicate ni nouveau serveur.

Les vidéos générées sont des artefacts locaux, ignorés par Git. Le build public
ne dépend d’aucun MP4. Les lecteurs chargent YouTube nocookie après un clic,
sans autoplay sonore, et proposent toujours un lien direct de secours. Leur
petit mécanisme commun provient de `library/darkwood-design/youtube.js`.

GitHub Pages ne fournit pas de redirections HTTP personnalisées par chemin.
Les références connues des anciennes vidéos ont été corrigées ; `404.html`
fournit les nouvelles adresses si un ancien lien direct est utilisé. Les anciens
films sont conservés localement dans le pilotage S47 et dans l’historique Git.
Aucune réécriture d’historique : le déversionnement ne diminue pas l’historique
existant. Les anciennes itérations sont décrites dans `docs/hackathon-review.md`.

Le jury peut toujours exécuter Mario indépendamment, avec sa propre clé. La
candidature est préparée et **non soumise**. Aucun post social n’est déclenché.
