{
  description = "Uniflow reproducible build environment";

  inputs.nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
  inputs.naviPhpNixpkgs.url = "github:NixOS/nixpkgs/4ae25df2c68d889251c436d487553bc8f4e12030";

  outputs = { nixpkgs, naviPhpNixpkgs, ... }:
    let
      systems = [ "x86_64-linux" "aarch64-linux" "x86_64-darwin" "aarch64-darwin" ];
      forAllSystems = nixpkgs.lib.genAttrs systems;
    in {
      devShells = forAllSystems (system:
        let
          pkgs = import nixpkgs { inherit system; };
          php = (import naviPhpNixpkgs { inherit system; }).php86;
        in {
          default = pkgs.mkShellNoCC {
            packages = [
              php
              php.packages.composer
              pkgs.nodejs_22
            ];
          };
        });

      formatter = forAllSystems (system:
        (import nixpkgs { inherit system; }).nixfmt-rfc-style);
    };
}
