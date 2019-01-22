#!/bin/bash

COUNT_ABADDON=$(ps -ef | grep -v grep | grep AbaddonDaemonController | wc -l)
COUNT_CONSUMER=$(ps -ef | grep -v grep | grep ConsumerDaemonController | wc -l)
COUNT_WATCHER=$(ps -ef | grep -v grep | grep WatcherDaemonController | wc -l)

echo "Abaddon:" $COUNT_ABADDON
echo "Watcher:" $COUNT_WATCHER
echo "Consumer:" $COUNT_CONSUMER