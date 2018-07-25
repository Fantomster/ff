#!/bin/bash
set -e
cd /var/www/html/qas.mixcart.ru/
composer update
if [ -e "/var/www/html/qas.mixcart.ru/deploy/db_dump/qas-dbs.sql" ] 
then
    echo "init..."
    mysql -h qas-db.cj1dgfxs1e0h.eu-west-1.rds.amazonaws.com -u fkeep -phateyouf4simba < /var/www/html/qas.mixcart.ru/deploy/db_dump/init-import.sql
    echo "import..."
    mysql -h qas-db.cj1dgfxs1e0h.eu-west-1.rds.amazonaws.com -u fkeep -phateyouf4simba < /var/www/html/qas.mixcart.ru/deploy/db_dump/qas-dbs.sql
    echo "end..."
    mysql -h qas-db.cj1dgfxs1e0h.eu-west-1.rds.amazonaws.com -u fkeep -phateyouf4simba < /var/www/html/qas.mixcart.ru/deploy/db_dump/end-import.sql
    echo "done!"
else
    echo "skipping import..."
fi

php yii migrate --interactive=0

#echo "clear old dump.."
#[ -e /var/www/html/qas.mixcart.ru/deploy/db_dump/qas-dbs.sql ] && rm /var/www/html/qas.mixcart.ru/deploy/db_dump/qas-dbs.sql
#echo "export..."
#mysqldump -h qas-db.cj1dgfxs1e0h.eu-west-1.rds.amazonaws.com -u fkeep -phateyouf4simba -B qas-main qas-int --add-drop-database > /var/www/html/qas.mixcart.ru/deploy/db_dump/qas-dbs.sql
