#!/bin/bash

echo "======  START SYNCING 53 SERVER ========="

ssh root@120.27.45.213<<EOF
ls
cd /alidata/www/default/agg/
ls

git status
git branch
git checkout dev
git pull origin dev

pwd
cd wapdev

gulp

EOF


echo "======  END OF SYNCING 53 SERVER ========="