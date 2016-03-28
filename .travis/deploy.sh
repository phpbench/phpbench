#!/bin/bash
# Unpack secrets; -C ensures they unpack *in* the .travis directory
tar xvf .travis/secrets.tar -C .travis

# Setup SSH agent:
eval "$(ssh-agent -s)" #start the ssh agent
chmod 600 .travis/build-key.pem
ssh-add .travis/build-key.pem

# Setup git defaults:
git config --global user.email "Daniel Leech"
git config --global user.name "dainel@dantleech.com"

# Add SSH-based remote to GitHub repo:
git remote add deploy git@github.com:phpbench/phpbench.git
git fetch deploy

# Get box and build PHAR
curl -LSs https://box-project.github.io/box2/installer.php | php
./box.phar build -vv

# small smoke test
php phpbench.phar --version

# Without the following step, we cannot checkout the gh-pages branch due to
# file conflicts:
mv phpbench.phar phpbench.phar.tmp

# discard modifications (i.e. to composer.json)
git reset --hard origin/master

# Checkout gh-pages and add PHAR file and version:
git checkout -b gh-pages deploy/gh-pages

# remove previous phars from the history
git filter-branch --force --index-filter 'git rm --cached --ignore-unmatch phpbench.phar' -- gh-pages

mv phpbench.phar.tmp phpbench.phar
sha1sum phpbench.phar > phpbench.phar.version
git add phpbench.phar phpbench.phar.version

# Commit and push:
git commit -m 'Rebuilt phar'
git push deploy gh-pages:gh-pages -f
