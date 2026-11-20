# Navi — environnement PHP commun

Ce répertoire est le socle de développement PHP Darkwood. Il ne lance aucun service et ne lit ni ne copie les sources applicatives dans une dérivation.

## Utilisation

```sh
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix --arg lockFile ./flake.lock
# Outils qualité optionnels
nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix --arg lockFile ./flake.lock --arg withQualityTools true
```

`default` et le profil qualité utilisent PHP 8.6.0. La version vient du verrou Nixpkgs, indépendamment de celle du client et du daemon Nix.

Le socle minimal apporte PHP, ses extensions natives par défaut et Composer. Le profil `quality` ajoute Symfony CLI, Castor, PHPStan, Psalm, PHPUnit et PHP-CS-Fixer. Psalm utilise le PHAR officiel vérifié par empreinte. Rector reste fourni par le verrou Composer de `tools/rector`. Les outils isolés sous `tools/` et leurs lockfiles restent prioritaires pour les contrôles propres au projet ; les versions globales ne remplacent pas les contraintes Composer de ces outils. Satis et les exemples préexistants restent des sorties optionnelles : ils ne sont plus requis pour ouvrir le shell minimal.

## Composition dans une card

Dans le même monorepo, importer `shell.nix` depuis le flake de la card et fournir son `pkgs` verrouillé sur la même révision que Navi :

```nix
let mkPhpShell = import ../darkwood-php/src/Darkwood/Component/Navi/tools/env/shell.nix;
in mkPhpShell {
  inherit pkgs;
  extraExtensions = { all, ... }: [ all.mbstring ];
  extraPackages = [ pkgs.nodejs_22 ];
}
```

L'API est aussi exposée par `lib.mkPhpShell` du flake Navi. Un dépôt extérieur doit épingler une révision publiée du composant public Navi existant, avec `?dir=tools/env`, et faire suivre son entrée `nixpkgs` à sa propre révision pour conserver la même révision canonique de runtime. Ne pas enregistrer un chemin absolu `/Users/...` dans un lockfile portable.

Une card peut aussi fournir un `shell.nix` fin appelant ce même constructeur avec `lockFile = ./flake.lock`. `nix --extra-experimental-features 'nix-command flakes' develop --file ./shell.nix` évite de capturer le monorepo applicatif comme source de flake. Le constructeur lit uniquement le lock et récupère sa révision Nixpkgs avec vérification du `narHash` ; il ne transmet aucun répertoire applicatif à Nix. `extraPackages` accepte une liste ou une fonction de `pkgs`.

Paramètres : `pkgs`, `lockFile`, `system`, `extraExtensions`, `extraConfig`, `extraPackages`, `withQualityTools`, `shellHook` et `name`. Pas de source applicative, de secrets ou de liste générique de services. FFmpeg, PostgreSQL, Godot, SDK mobiles et outils ML restent des ajouts explicites de la card concernée.

Les lanceurs Castor utilisent l’entrée native `--file shell.nix --arg lockFile ...`, qui ne capture pas le répertoire comme source de flake. `extraConfig` est réservé aux réglages PHP non sensibles : ne jamais y injecter de credential, car la configuration générée appartient au store lisible.

Dans le shell, Symfony CLI utilise le binaire PHP de Navi via `SYMFONY_CLI_PHP_BINARY_PATH`, y compris avec une autre version installée sur la machine. Le shell neutralise un `PHP_INI_SCAN_DIR` hérité d'un autre PHP. Une extension opérateur peut être ajoutée explicitement via `DARKWOOD_PHP_INI_DIR`. Aucun fichier de configuration système n'est réécrit.

## Profil hors ligne

Pour conserver le shell qualité hors ligne, ajouter
`--profile ~/.local/state/nix/profiles/darkwood-navi` à la commande native
du shell qualité. Cette racine couvre les exécutables du shell, Bash interactif
et la source Nixpkgs du verrou. Le profil référence cette source existante
sans la recopier. Le constructeur ne référence aucune source applicative.

## Sources et sécurité du store

Ne pas utiliser `path:.` pour capturer une application. Ne pas transmettre `self`, `vendor`, `node_modules`, `var`, builds ou données privées à un constructeur de sources. `shell.nix` n'accepte aucune source applicative et ne construit que le runtime.

Le fetcher Git d'un flake copie néanmoins les fichiers suivis nécessaires à sa source ; un secret suivi n'est donc pas protégé par Git. Auditer les fichiers suivis avant évaluation. Un input local de type `path:` n'offre pas le filtrage Git : préférer le composant public épinglé pour les consommateurs externes. Ne jamais placer de secrets dans ce répertoire d'environnement.

Les fichiers Compose existants décrivent un PostgreSQL local optionnel historique. Ils sont conservés pour compatibilité, ne sont jamais démarrés par les shells et ne constituent pas une dépendance des autres cards.

## Politique de versions

PHP 8.6.0 est l’unique runtime de développement du socle. Les consommateurs
alignent leur révision Nixpkgs sur celle de Navi. Les projets incompatibles
conservent temporairement leur environnement propre, documenté dans le bilan
S47. Le retour au verrou et au shell précédents reste possible par Git.
