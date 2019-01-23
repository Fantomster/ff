#!/bin/bash
set -e
cd /var/www/html/qas.mixcart.ru/
export COMPOSER_HOME="$HOME/.composer";
composer update
#if [ -e "/var/www/html/qas.mixcart.ru/deploy/db_dump/qas-dbs.sql" ] 
#then
#    echo "init..."
#    mysql -h qas-db.cj1dgfxs1e0h.eu-west-1.rds.amazonaws.com -u fkeep -phateyouf4simba < /var/www/html/qas.mixcart.ru/deploy/db_dump/init-import.sql
#    echo "import..."
#    mysql -h qas-db.cj1dgfxs1e0h.eu-west-1.rds.amazonaws.com -u fkeep -phateyouf4simba < /var/www/html/qas.mixcart.ru/deploy/db_dump/qas-dbs.sql
#    echo "end..."
#    mysql -h qas-db.cj1dgfxs1e0h.eu-west-1.rds.amazonaws.com -u fkeep -phateyouf4simba < /var/www/html/qas.mixcart.ru/deploy/db_dump/end-import.sql
#    echo "done!"
#else
#    echo "skipping import..."
#fi

php yii migrate --interactive=0
if [ $? -gt 0 ]; then
    exit 1
fi

sudo rm -rf /var/www/html/qas.mixcart.ru/frontend/web/assets/*
sudo rm -rf /var/www/html/qas.mixcart.ru/backend/web/assets/*
sudo rm -rf /var/www/html/qas.mixcart.ru/market/web/assets/*
sudo rm -rf /var/www/html/qas.mixcart.ru/franchise/web/assets/*
sudo rm -rf /var/www/html/qas.mixcart.ru/api_web/web/assets/*

chmod 777 /var/www/html/qas.mixcart.ru/frontend/web/upload/temp

echo "assets erased"