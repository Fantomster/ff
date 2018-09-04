#!/bin/bash
cd /var/www/html/dev.mixcart.ru/
composer update
php yii migrate --interactive=0

sudo rm -rf /var/www/html/dev.mixcart.ru/frontend/web/assets/*
sudo rm -rf /var/www/html/dev.mixcart.ru/backend/web/assets/*
sudo rm -rf /var/www/html/dev.mixcart.ru/market/web/assets/*
sudo rm -rf /var/www/html/dev.mixcart.ru/franchise/web/assets/*

echo "assets erased"