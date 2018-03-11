#!/bin/bash
set -x

export PATH=.:$PATH

# Travis won't deploy ignored files
sed -ie '/^phpbench.phar*/d'  .gitignore

openssl aes-256-cbc -K $encrypted_d58d55177063_key -iv $encrypted_d58d55177063_iv -in .travis/secrets.tar.enc -out .travis/secrets.tar -d

tar xvf .travis/secrets.tar -C .travis

composer require "doctrine/dbal" "~2.5"  --no-update
composer require "padraic/phar-updater" "^1.0" --no-update

curl -LSs https://box-project.github.io/box2/installer.php | php

rm -Rf ./vendor
composer install --no-dev -o
box.phar build
sha1sum phpbench.phar > phpbench.phar.version
