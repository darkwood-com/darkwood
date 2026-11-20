{ pkgs ? null }:
import ../darkwood-php/src/Darkwood/Component/Navi/tools/env/shell.nix {
  inherit pkgs;
  lockFile = ./flake.lock;
  extraPackages = pkgs: [ pkgs.nodejs ];
}
