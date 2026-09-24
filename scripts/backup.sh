#!/bin/bash
set -e
ssh dumb "
set -e
cd /home/private
rm -rf backup
rm -f backup.tar.gz
mkdir -p backup/avatars
mariadb-dump \
	--single-transaction \
	--databases deltaruneboards \
	> backup/dump.sql
cp /home/public/custom_avatar/* backup/avatars/
tar -czf backup.tar.gz -C backup .
rm -rf backup
"
scp dumb:/home/private/backup.tar.gz .
ssh dumb "rm /home/private/backup.tar.gz"
