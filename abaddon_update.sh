#!/bin/bash

#param $1 = current app folder i.e. f-keeper-b

COUNT=$(ps -ef | grep -v grep | grep AbaddonDaemonController | wc -l)
if [ $COUNT -lt 1 ]; then
    cd /$1
    php yii abaddon-daemon
fi
if [ $COUNT -gt 1 ]; then
    kill -9 $(ps aux | grep -e AbaddonDaemonController | awk '{ print $2 }')
    cd /$1
    php yii abaddon-daemon
fi
