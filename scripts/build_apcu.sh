#!/bin/bash
cd /home/protected
git clone https://github.com/php/php-src.git
git clone https://github.com/krakjoe/apcu.git php-src/ext/apcu
cd php-src
git checkout PHP-8.4
./buildconf --force
./configure --disable-all --enable-apcu=shared
make -j2
cp modules/apcu.so ../
cd ..
rm -rf php-src
