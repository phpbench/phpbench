# this is a WIP flake for development and experimentation only
{
  description = "php-tui/php-tui";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
  };

  outputs = inputs @ {
    self,
    flake-parts,
    ...
  }:
    flake-parts.lib.mkFlake {inherit inputs;} {
      # This flake is for Linux (x86) and Apple (darwin) systems
      # If you need more systems, inspect `nixpkgs.lib.systems.flakeExposed` and
      # add them to this list.
      #
      # $ nix repl "<nixpkgs>"
      # nix-repl> lib.systems.flakeExposed
      systems = ["x86_64-linux" "aarch64-linux"];

      perSystem = {
        pkgs,
        system,
        ...
      }: let 
        phpForPhpTui = (pkgs.php81.buildEnv {
          extensions = ({ enabled, all }: enabled ++ (with all; [
            imagick
          ]));
          extraConfig = ''
          '';
        });
      in {
        # Run `nix fmt` to reformat the nix files
        formatter = pkgs.alejandra;

        # Run `nix develop` to enter the development shell
        devShells.default = pkgs.mkShellNoCC {
          name = "php-devshell";

          buildInputs = [
            pkgs.bash
            pkgs.vhs
            pkgs.hugo
            phpForPhpTui
            pkgs.php81.packages.composer
          ];
          shellHook = ''
          if [ ! -d ".venv" ]; then
            python3 -m venv .venv;
          fi
          source .venv/bin/activate;'';
        };
      };
    };
}
