{
  description = "Darkwood Intention — local deterministic demonstrations";
  inputs.nixpkgs.url = "github:NixOS/nixpkgs/4ae25df2c68d889251c436d487553bc8f4e12030";
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
