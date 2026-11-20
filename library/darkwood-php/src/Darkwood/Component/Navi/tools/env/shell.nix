# Shared PHP shell only: no application source, services or tool downloads.
{ pkgs ? null
, lockFile ? null
, system ? builtins.currentSystem
, extraExtensions ? (_: [])
, extraConfig ? ""
, extraPackages ? []
, withQualityTools ? false
, shellHook ? ""
, name ? "darkwood-php"
}:
let
  # The native --file entry point reads only the lock, never the application tree.
  lockedSource =
    assert lockFile != null && builtins.baseNameOf lockFile == "flake.lock";
    let
      lock = builtins.fromJSON (builtins.readFile lockFile);
      input = lock.nodes.${lock.nodes.${lock.root}.inputs.nixpkgs}.locked;
    in builtins.fetchTree { inherit (input) type owner repo rev narHash; };
  packageSet = if pkgs != null then pkgs else
    import lockedSource.outPath { inherit system; config = {}; overlays = []; };
  basePhp = packageSet.php86;
  runtime = assert basePhp.version == "8.6.0"; basePhp.buildEnv {
    extensions = available: packageSet.lib.unique (available.enabled ++ extraExtensions available);
    inherit extraConfig;
  };
  psalm = packageSet.fetchurl {
    url = "https://github.com/vimeo/psalm/releases/download/7.12.0/psalm.phar";
    sha256 = "6d632443537a38d7089983d16b14d7e798434a578e501dd8342f35656136d17b";
  };
  qualityTools = with packageSet; [
    symfony-cli
    runtime.packages.castor
    (phpstan.override { php = runtime; withTurbo = false; })
    (writeShellScriptBin "psalm" ''
      exec ${runtime}/bin/php ${psalm} "$@"
    '')
    runtime.packages.php-cs-fixer
    (phpunit.override { php = runtime; })
  ];
  additions = if builtins.isFunction extraPackages then extraPackages packageSet else extraPackages;
in packageSet.mkShellNoCC {
  inherit name;
  packages = [ runtime runtime.packages.composer packageSet.bashInteractive packageSet.bashInteractive.man ]
    ++ packageSet.lib.optionals withQualityTools qualityTools
    ++ additions;
  COMPOSER_MEMORY_LIMIT = "-1";
  # Keep the pinned package definitions reachable from an offline shell profile.
  DARKWOOD_NIXPKGS_SOURCE = if pkgs == null then lockedSource.outPath else null;
  shellHook = ''
    # Do not inherit a scan directory from a different PHP installation.
    export PHP_INI_SCAN_DIR="${runtime}/lib''${DARKWOOD_PHP_INI_DIR:+:$DARKWOOD_PHP_INI_DIR}"
    export SYMFONY_CLI_PHP_BINARY_PATH="${runtime}/bin/php"
    ${shellHook}
  '';
}
