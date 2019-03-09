#!/bin/bash
# set -x

export PATH=.:$PATH

# Travis won't deploy ignored files
sed -ie '/^\/phpbench.phar*/d'  .gitignore

# Install PHAR dependencies and remove strip other dev deps.
rm -Rf ./vendor
rm -f composer.lock
composer require "doctrine/dbal" "~2.5"  --no-update
composer install --no-dev -o

# Build the PHAR
wget https://github.com/humbug/box/releases/download/3.5.1/box.phar
php box.phar compile -c box.json.gh-release

echo "Done pre-deploy build"
