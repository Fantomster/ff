<?php

use yii\db\Migration;

class m180831_074900_add_comments_table_catalog_base_goods extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `catalog_base_goods` comment "Таблица сведений о товарах базовых каталогов";');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'cat_id','Идентификатор каталога товаров');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'article','Артикул товара');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'product','Наименование товара');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'status','Показатель состояния активности товара (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'market_place','Показатель задействования товара в Маркет Плейсе (0 - не задействован, 1 - задействован)');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'deleted','Показатель состояния удаления товара (0 - удалён, 1 - не удалён)');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'supp_org_id','Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'price','Цена товара');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'units','Количество единиц товара в товарной упаковке');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'category_id','Идентификатор катгории товаров из Market Place');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'note','Примечание');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'ed','Единица измерения товара');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'image','Название файла-изображения товара');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'brand','Название производителя товара (бренд)');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'region','Страна или регион производителя');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'weight','Вес товарной упаковки товара');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'es_status','Показатель состояния индесации товара в поискомо движке Elastic Search (0 - не участвует в поиске, 1  - участвует в поиске)');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'mp_show_price','Показатель состояния необходимости показа цены на торва в Market Place');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'rating','Рейтинг товара на Market Place');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'barcode','Штрих-код товара на Market Place');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'edi_supplier_article','Артикул товара для EDI');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'ssid','Идентификатор SSID (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `catalog_base_goods` comment "";');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'id');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'cat_id');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'article');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'product');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'status');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'market_place');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'deleted');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'created_at');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'updated_at');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'supp_org_id');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'price');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'units');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'category_id');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'note');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'ed');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'image');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'brand');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'region');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'weight');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'es_status');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'mp_show_price');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'rating');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'barcode');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'edi_supplier_article');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'ssid');
    }
}
