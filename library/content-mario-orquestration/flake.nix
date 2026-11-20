{
  description = "Darkwood original level designer — Symfony AI and official MCP SDK";
  inputs.nixpkgs.url = "github:NixOS/nixpkgs/4ae25df2c68d889251c436d487553bc8f4e12030";
  outputs = { nixpkgs, ... }:
    let systems = [ "aarch64-darwin" "aarch64-linux" "x86_64-darwin" "x86_64-linux" ];
    in {
      devShells = nixpkgs.lib.genAttrs systems (system:
        let
          pkgs = import nixpkgs { inherit system; };
        in { default = import ./shell.nix { inherit pkgs; }; });
    };
}
