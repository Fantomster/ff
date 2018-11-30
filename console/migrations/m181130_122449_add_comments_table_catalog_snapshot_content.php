<?php

use yii\db\Migration;

class m181130_122449_add_comments_table_catalog_snapshot_content extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `catalog_snapshot_content` comment "Таблица сведений о товарных позиций резервных копий каталогов товаров поставщиков";');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'snapshot_id','Идентификатор резервной копии каталога товаров поставщиков');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'article','Артикул товара');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'product','Наименование товара');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'status','Показатель состояния активности товара (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'market_place','Показатель задействования товара в Маркет Плейсе (0 - не задействован, 1 - задействован)');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'deleted','Показатель состояния удаления товара (0 - не удалён, 1 - удалён)');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'price','Цена товара');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'units','Количество единиц товара в товарной упаковке');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'category_id','Идентификатор категории товаров из Market Place');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'note','Примечание');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'ed','Единица измерения товара');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'image','Название файла-изображения товара');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'brand','Название производителя товара (бренд)');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'region','Страна или регион производителя');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'weight','Вес товарной упаковки товара');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'mp_show_price','Показатель состояния необходимости показа цены на товар в Market Place');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'barcode','Штрих-код товара на Market Place');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'edi_supplier_article','Артикул товара для EDI');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'ssid','Идентификатор SSID (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `catalog_snapshot_content` comment "";');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'id');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'snapshot_id');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'article');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'product');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'status');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'market_place');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'deleted');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'price');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'units');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'category_id');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'note');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'ed');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'image');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'brand');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'region');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'weight');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'mp_show_price');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'barcode');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'edi_supplier_article');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'ssid');
    }
}
