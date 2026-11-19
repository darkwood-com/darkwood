{
  description = "Darkwood original level designer — Symfony AI and official MCP SDK";
  inputs.nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
  outputs = { nixpkgs, ... }:
    let systems = [ "aarch64-darwin" "aarch64-linux" "x86_64-darwin" "x86_64-linux" ];
    in {
      devShells = nixpkgs.lib.genAttrs systems (system:
        let
          pkgs = import nixpkgs { inherit system; };
          php = pkgs.php85.buildEnv { extensions = { enabled, all }: pkgs.lib.unique (enabled ++ [ all.mbstring ]); };
        in { default = pkgs.mkShellNoCC { packages = [ php php.packages.composer ]; }; });
    };
}
