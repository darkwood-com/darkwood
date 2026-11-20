{ }:
let
  navi = ../darkwood-php/src/Darkwood/Component/Navi/tools/env;
in import (navi + "/shell.nix") {
  lockFile = navi + "/flake.lock";
  extraExtensions = { all, ... }: [ all.ffi ];
}
