#!/bin/bash

cd /var/www/html/dev.mixcart.ru/
export COMPOSER_HOME="$HOME/.composer";
composer update
php yii migrate --interactive=0
if [ $? -gt 0 ]; then
    exit 1
fi

cd /var/www/html/dev.mixcart.ru/nightwatch
chmod 777 nightwatch
npm install
#nightwatch

sudo rm -rf /var/www/html/dev.mixcart.ru/frontend/web/assets/*
sudo rm -rf /var/www/html/dev.mixcart.ru/backend/web/assets/*
sudo rm -rf /var/www/html/dev.mixcart.ru/market/web/assets/*
sudo rm -rf /var/www/html/dev.mixcart.ru/franchise/web/assets/*
sudo rm -rf /var/www/html/dev.mixcart.ru/api_web/web/assets/*

chmod 777 /var/www/html/dev.mixcart.ru/frontend/web/upload/temp

echo "assets erased"