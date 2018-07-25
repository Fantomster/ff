#!/bin/bash
echo "clear old dump.."
[ -e /var/www/html/qas.mixcart.ru/deploy/db_dump/qas-dbs.sql ] && rm /var/www/html/qas.mixcart.ru/deploy/db_dump/qas-dbs.sql
echo "export..."
mysqldump -h qas-db.cj1dgfxs1e0h.eu-west-1.rds.amazonaws.com -u fkeep -phateyouf4simba -B qas-main qas-int --add-drop-database > /var/www/html/qas.mixcart.ru/deploy/db_dump/qas-dbs.sql
