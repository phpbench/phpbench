#!/bin/bash
# set -x

export PATH=.:$PATH

# Travis won't deploy ignored files
sed -ie '/^\/phpbench.phar*/d'  .gitignore

# Extract the private key (needed to sign the PHAR)
openssl aes-256-cbc -K $encrypted_d58d55177063_key -iv $encrypted_d58d55177063_iv -in .travis/secrets.tar.enc -out .travis/secrets.tar -d
tar xvf .travis/secrets.tar -C .travis

# Install PHAR dependencies and remove strip other dev deps.
rm -Rf ./vendor
rm -f composer.lock
composer require "doctrine/dbal" "~2.5"  --no-update
composer require "padraic/phar-updater" "^1.0" --no-update
composer install --no-dev -o

# Build the PHAR
curl -LSs https://box-project.github.io/box2/installer.php | php
box.phar build
ls

# Dump the version file (used to see if PHPBench needs an update)
sha1sum phpbench.phar > phpbench.phar.version
echo "Done pre-deploy build"
