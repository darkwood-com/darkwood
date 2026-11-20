# Navi — environnement PHP commun

Ce répertoire est le socle de développement PHP Darkwood. Il ne lance aucun service et ne lit ni ne copie les sources applicatives dans une dérivation.

## Utilisation

```sh
nix --extra-experimental-features 'nix-command flakes' develop path:.
nix --extra-experimental-features 'nix-command flakes' develop .#php86
nix --extra-experimental-features 'nix-command flakes' develop .#quality
```

`default` et `php85` utilisent PHP 8.5.13. `php86` est une variante expérimentale PHP 8.6.0. Les versions sont celles de la révision Nixpkgs du `flake.lock`, distinctes de la version du client ou du daemon Nix. Aucun runtime FrankenPHP n'est modifié par ce shell.

Le socle minimal apporte PHP, ses extensions natives par défaut et Composer. Le profil `quality` ajoute Symfony CLI, PHPStan, Psalm et PHPUnit de Nixpkgs. Les outils isolés sous `tools/` et leurs lockfiles restent prioritaires pour les contrôles propres au projet ; les versions globales ne remplacent pas les contraintes Composer de ces outils. Satis et les exemples préexistants restent des sorties optionnelles : ils ne sont plus requis pour ouvrir le shell minimal.

## Composition dans une card

Dans le même monorepo, importer `shell.nix` depuis le flake de la card et fournir son `pkgs` verrouillé :

```nix
let mkPhpShell = import ../darkwood-php/src/Darkwood/Component/Navi/tools/env/shell.nix;
in mkPhpShell {
  inherit pkgs;
  php = pkgs.php85;
  extraExtensions = { all, ... }: [ all.mbstring ];
  extraPackages = [ pkgs.nodejs_22 ];
}
```

L'API est aussi exposée par `lib.mkPhpShell` du flake Navi. Un dépôt extérieur doit épingler une révision publiée du composant public Navi existant, avec `?dir=tools/env`, et faire suivre son entrée `nixpkgs` à sa propre révision lorsque la conservation de son runtime est nécessaire. Ne pas enregistrer un chemin absolu `/Users/...` dans un lockfile portable.

Une card peut aussi fournir un `shell.nix` fin appelant ce même constructeur avec `lockFile = ./flake.lock`. `nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix` évite de capturer le monorepo applicatif comme source de flake. Le constructeur lit uniquement le lock et récupère sa révision Nixpkgs avec vérification du `narHash` ; il ne transmet aucun répertoire applicatif à Nix. `extraPackages` accepte une liste ou une fonction de `pkgs`.

Paramètres : `pkgs`, `lockFile`, `system`, `php`, `extraExtensions`, `extraConfig`, `extraPackages`, `shellHook` et `name`. Pas de source applicative, de secrets ou de liste générique de services. FFmpeg, PostgreSQL, Godot, SDK mobiles et outils ML restent des ajouts explicites de la card concernée.

Les lanceurs Castor utilisent l’entrée native `--file shell.nix --arg lockFile ...`, qui ne capture pas le répertoire comme source de flake. `extraConfig` est réservé aux réglages PHP non sensibles : ne jamais y injecter de credential, car la configuration générée appartient au store lisible.

Le shell neutralise un `PHP_INI_SCAN_DIR` hérité d'un autre PHP. Une extension opérateur peut être ajoutée explicitement via `DARKWOOD_PHP_INI_DIR`. Aucun fichier de configuration système n'est réécrit.

## Sources et sécurité du store

Ne pas utiliser `path:.` pour capturer une application. Ne pas transmettre `self`, `vendor`, `node_modules`, `var`, builds ou données privées à un constructeur de sources. `shell.nix` n'accepte aucune source applicative et ne construit que le runtime.

Le fetcher Git d'un flake copie néanmoins les fichiers suivis nécessaires à sa source ; un secret suivi n'est donc pas protégé par Git. Auditer les fichiers suivis avant évaluation. Un input local de type `path:` n'offre pas le filtrage Git : préférer le composant public épinglé pour les consommateurs externes. Ne jamais placer de secrets dans ce répertoire d'environnement.

Les fichiers Compose existants décrivent un PostgreSQL local optionnel historique. Ils sont conservés pour compatibilité, ne sont jamais démarrés par les shells et ne constituent pas une dépendance des autres cards.

## Repli

Les consommateurs conservent leur révision Nixpkgs et leurs manifests Composer. Revenir au commit précédent de leur flake suffit pour annuler l'adoption. Pour quitter PHP 8.6 expérimental, sortir du shell puis rouvrir `default`/`php85`. Aucun changement de daemon, profil système ou runtime de production n'est nécessaire.
