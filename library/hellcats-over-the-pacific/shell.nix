# Python for tools/: Pillow for resedit.py's PNG pixel comparisons (without it `verify`
# reports metadata drift), Capstone for the optional m68k checks of tools/qa/test_regression.py.
{ pkgs ? import <nixpkgs> { } }:
pkgs.mkShell {
  packages = [ (pkgs.python3.withPackages (p: [ p.pillow p.capstone ])) ];
}
