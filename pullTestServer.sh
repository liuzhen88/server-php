#!/bin/bash
## HerbertDai 2015-10

thisCmd=`basename "$0"`
WEBPATH='/alidata/www/default/'
SPLIT_LINE="========================================================="

if [ $# -eq 0 ]; then
    echo $SPLIT_LINE
    echo "usage: $thisCmd [ dev | online | dev_test ] "
    echo ""
    echo "    Update dev: $thisCmd dev "
    echo "    Update online: $thisCmd online "
    echo "    Update dev_test: $thisCmd dev_test "
    echo $SPLIT_LINE
    exit 0
fi

echo $SPLIT_LINE
echo "  ==  Try to sync code on $@ branch ...  =="
echo $SPLIT_LINE

if [[ $@ == 'dev' ]]; then
    target_path=$WEBPATH'agg/'
elif [[ $@ == 'online' ]]; then
    target_path=$WEBPATH'agg_online/'
elif [[ $@ == 'dev_test' ]]; then
    target_path=$WEBPATH'agg_test/'
fi

ssh root@120.27.45.213<<EOF

cd $target_path
pwd
git checkout $@
git status
git stash
git pull --rebase

EOF

echo $SPLIT_LINE
echo "  ==  End of syncing code on $@ branch ...  =="
echo $SPLIT_LINE
