#!/bin/bash bash
pid=$(pidof AbaddonDaemonController)
if [[ "$pid" -gt 0 ]]
then
    echo "Running" >> 1.log
else
    echo "Stopped" >> 1.log
    cd /app
    php yii abaddon-daemon
fi