#!/bin/bash

echo "======  START SYNCING 53 SERVER ========="

ssh root@120.27.45.213<<EOF
ls
cd /alidata/www/default/agg_online_v21/
ls

git status
git branch
git checkout online_v2.1
git pull origin online_v2.1

pwd
cd wapdev

gulp

EOF


echo "======  END OF SYNCING 53 SERVER ========="