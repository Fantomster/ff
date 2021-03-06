#!/bin/bash
cd /var/www/html/ftr.mixcart.ru/
export COMPOSER_HOME="$HOME/.composer";
composer update
php yii migrate --interactive=0
if [ $? -gt 0 ]; then
    exit 1
fi

sudo rm -rf /var/www/html/ftr.mixcart.ru/frontend/web/assets/*
sudo rm -rf /var/www/html/ftr.mixcart.ru/backend/web/assets/*
sudo rm -rf /var/www/html/ftr.mixcart.ru/market/web/assets/*
sudo rm -rf /var/www/html/ftr.mixcart.ru/franchise/web/assets/*
sudo rm -rf /var/www/html/ftr.mixcart.ru/api_web/web/assets/*

chmod 777 /var/www/html/ftr.mixcart.ru/frontend/web/upload/temp

echo "assets erased"