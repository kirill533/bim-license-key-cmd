#!/usr/bin/env bash
set -x

wget https://github.com/clue/phar-composer/releases/download/v1.0.0/phar-composer.phar
chmod +x phar-composer.phar
cd ../
cp -rL license-key license-key-tmp
./license-key/phar-composer.phar build license-key-tmp
mv bim-lib-tests.phar license-key/license-key.phar
cd license-key
rm -Rf ../license-key-tmp
rm phar-composer.phar