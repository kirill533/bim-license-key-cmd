#!/usr/bin/env bash

set -e -x

if [ ! -f ./phar-composer-1.4.0.phar ]; then
  wget https://github.com/clue/phar-composer/releases/download/v1.4.0/phar-composer-1.4.0.phar
  chmod +x ./phar-composer-1.4.0.phar
fi;

rm -rf tmp-build
mkdir -p tmp-build/license-key
cp -rL vendor tmp-build/license-key/vendor
cp -rL src tmp-build/license-key/src
cp -rL bin tmp-build/license-key/bin
cp composer.json tmp-build/license-key/composer.json
cp composer.lock tmp-build/license-key/composer.lock
cd tmp-build
php -d phar.readonly=off ..//phar-composer-1.4.0.phar build license-key
mv license-key-cmd.phar ../license-key.phar
cd ..
rm -rf tmp-build
