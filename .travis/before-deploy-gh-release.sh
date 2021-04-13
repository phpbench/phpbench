#!/bin/bash
# set -x

export PATH=.:$PATH

# Travis won't deploy ignored files
sed -ie '/^\/phpbench.phar*/d'  .gitignore

# Install PHAR dependencies and remove strip other dev deps.
rm -Rf ./vendor
rm -f composer.lock
composer install --no-dev -o

wget https://github.com/box-project/box/releases/download/3.8.5/box.phar
php box.phar compile -c box.json.gh-release

echo "Done pre-deploy build"
