#!/bin/bash
cd /var/www/html/ftr.mixcart.ru/
composer update
php yii migrate --interactive=0

sudo rm -rf /var/www/html/ftr.mixcart.ru/frontend/web/assets/*
sudo rm -rf /var/www/html/ftr.mixcart.ru/backend/web/assets/*
sudo rm -rf /var/www/html/ftr.mixcart.ru/market/web/assets/*
sudo rm -rf /var/www/html/ftr.mixcart.ru/franchise/web/assets/*
sudo rm -rf /var/www/html/ftr.mixcart.ru/api_web/web/assets/*

echo "assets erased"