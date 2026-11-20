{
  description = "Darkwood Memory — local Symfony demonstrations";
  inputs.nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
  outputs = { nixpkgs, ... }:
    let
      systems = [ "aarch64-darwin" "aarch64-linux" "x86_64-darwin" "x86_64-linux" ];
      each = nixpkgs.lib.genAttrs systems;
    in {
      devShells = each (system:
        let pkgs = import nixpkgs { inherit system; }; in {
          default = import ./shell.nix { inherit pkgs; };
        });
    };
}
