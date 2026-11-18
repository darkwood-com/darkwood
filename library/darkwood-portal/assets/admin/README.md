# Admin assets

Les bibliothèques de l’admin sont installées par le workspace npm défini dans
`package.json`, puis copiées par la configuration Encore existante dans
`public/build/admin/vendor`. Ces fichiers générés et `node_modules` sont ignorés
par Git. Le lock npm racine est la référence de reconstruction.

Depuis la racine de Darkwood Portal, dans son environnement Nix :

```sh
npm ci --ignore-scripts
npm run build
npm run test:admin-assets
```

Le déploiement doit inclure `public/build`, comme pour le site public. Les URLs
de l’admin utilisent désormais `/build/admin/vendor/...`. Aucune ressource n’est
chargée depuis un CDN. Bootstrap 3.4.1 est résolu depuis ce workspace ; le site
public conserve son Bootstrap 5. Les versions de compatibilité sont épinglées.

Le test navigateur sert une page locale synthétique avec les scripts et styles
extraits du template réel. Il bloque les requêtes externes et vérifie les widgets,
les ressources ainsi que la soumission HTML. `ADMIN_BROWSER_PATH` permet de
désigner un Chromium/Chrome existant ; sur macOS, Google Chrome est détecté.
Les profils des navigateurs de l’utilisateur ne sont pas utilisés. Ce contrôle ne
remplace pas une recette authentifiée de l’admin avec ses données métier.

Sources : [npm workspaces](https://docs.npmjs.com/cli/using-npm/workspaces/),
[Trumbowyg](https://alex-d.github.io/Trumbowyg/documentation/core/).

Le thème Color Admin, ses styles, les images et les scripts applicatifs restent
dans `public/admin`. Le JavaScript applicatif est adapté à jQuery 3.7.1. Les
plugins sans utilisation trouvée dans les templates/scripts ne sont plus chargés.

Trumbowyg remplace CKEditor 4 pour les champs `.ckeditor` existants : édition
visuelle, source HTML, formatage, liens, listes, alignements et compteur de texte.
La textarea et son nom Symfony sont préservés, y compris lors d’une soumission
depuis le mode source. Il n’y a pas de migration des contenus déjà enregistrés.
Les anciennes configurations spécifiques sont conservées dans
`legacy-ckeditor-config/` pour référence ; elles ne sont plus exécutées.

Le chargement des anciens plugins graphiques ou formulaires inutilisés n’est pas
conservé. Pour ajouter un widget, déclarer son package et son utilisation plutôt
que réintroduire une bibliothèque dans `public/admin`.

Cette extraction des bibliothèques n’est pas une refonte du thème Bootstrap 3 :
plusieurs plugins restent anciens et des avis npm modérés demeurent. En particulier,
Multiselect 0.9.15 installe un jQuery 2 transitif qui n’est ni copié dans le build
ni chargé par l’admin ; le seul jQuery servi est 3.7.1. Examiner `npm audit` avant
une modernisation fonctionnelle ultérieure, sans mise à niveau forcée des pairs.
