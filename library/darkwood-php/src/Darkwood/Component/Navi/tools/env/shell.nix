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
  packageSet = if pkgs != null then pkgs else
    assert lockFile != null && builtins.baseNameOf lockFile == "flake.lock";
    let
      lock = builtins.fromJSON (builtins.readFile lockFile);
      input = lock.nodes.${lock.nodes.${lock.root}.inputs.nixpkgs}.locked;
      source = builtins.fetchTree { inherit (input) type owner repo rev narHash; };
    in import source.outPath { inherit system; config = {}; overlays = []; };
  basePhp = packageSet.php86;
  runtime = assert basePhp.version == "8.6.0"; basePhp.buildEnv {
    extensions = available: packageSet.lib.unique (available.enabled ++ extraExtensions available);
    inherit extraConfig;
  };
  qualityTools = with packageSet; [
    symfony-cli
    runtime.packages.castor
    (phpstan.override { php = runtime; })
    runtime.packages.psalm
    (phpunit.override { php = runtime; })
  ];
  additions = if builtins.isFunction extraPackages then extraPackages packageSet else extraPackages;
in packageSet.mkShellNoCC {
  inherit name;
  packages = [ runtime runtime.packages.composer ]
    ++ packageSet.lib.optionals withQualityTools qualityTools
    ++ additions;
  COMPOSER_MEMORY_LIMIT = "-1";
  shellHook = ''
    # Do not inherit a scan directory from a different PHP installation.
    export PHP_INI_SCAN_DIR="${runtime}/lib''${DARKWOOD_PHP_INI_DIR:+:$DARKWOOD_PHP_INI_DIR}"
    ${shellHook}
  '';
}
