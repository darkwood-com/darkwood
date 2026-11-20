# Shared PHP shell only: no application source, services or tool downloads.
{ pkgs ? null
, lockFile ? null
, system ? builtins.currentSystem
, php ? null
, extraExtensions ? (_: [])
, extraConfig ? ""
, extraPackages ? []
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
  basePhp = if php == null then packageSet.php85 else php;
  runtime = basePhp.buildEnv {
    extensions = available: packageSet.lib.unique (available.enabled ++ extraExtensions available);
    inherit extraConfig;
  };
  additions = if builtins.isFunction extraPackages then extraPackages packageSet else extraPackages;
in packageSet.mkShellNoCC {
  inherit name;
  packages = [ runtime runtime.packages.composer ] ++ additions;
  COMPOSER_MEMORY_LIMIT = "-1";
  shellHook = ''
    # Do not inherit a scan directory from a different PHP installation.
    export PHP_INI_SCAN_DIR="${runtime}/lib''${DARKWOOD_PHP_INI_DIR:+:$DARKWOOD_PHP_INI_DIR}"
    ${shellHook}
  '';
}
