#!/usr/bin/env bash

set -e

mkdir -p build
cd build

wget -O box.phar https://github.com/box-project/box/releases/download/3.11.1/box.phar
php box.phar compile -c ../box.json.gh-release

if [[ "$GPG_SIGNING" != '' ]] ; then
    if [[ "$GPG_SECRET_KEY" != '' ]] ; then
        echo "Load secret key into gpg"
        echo "$GPG_SECRET_KEY" | gpg --import --no-tty --batch --yes
    fi

    echo "Sign Phar"

    echo "$GPG_PASSPHRASE" | gpg --command-fd 0 --passphrase-fd 0 --pinentry-mode loopback -u 676674024C0D866B --batch --detach-sign --armor --output phpbench.phar.asc phpbench.phar
fi

cd -
