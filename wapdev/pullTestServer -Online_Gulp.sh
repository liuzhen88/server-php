#!/bin/bash

echo "======  START SYNCING 53 SERVER ========="

ssh root@120.27.45.213<<EOF
ls
cd /alidata/www/default/agg_online/
ls

git status
git branch
git checkout online
git pull origin online

pwd
cd wapdev

gulp

EOF


echo "======  END OF SYNCING 53 SERVER ========="