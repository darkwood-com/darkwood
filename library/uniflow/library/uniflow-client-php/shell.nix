{ pkgs ? null }:
import ../../../darkwood-php/src/Darkwood/Component/Navi/tools/env/shell.nix {
  inherit pkgs;
  lockFile = ./flake.lock;
  extraExtensions = { all, ... }: [
    all.filter all.openssl all.curl all.simplexml all.tokenizer all.dom
    all.mbstring all.xmlwriter all.zip all.session
  ];
  extraPackages = pkgs: [ pkgs.pkg-config ];
}
