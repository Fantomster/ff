<?php

use yii\db\Migration;

class m181012_074600_add_comments_table_catalog_goods extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `catalog_goods` comment "Таблица сведений о товарах индивидуальных каталогов, назначенных поставщиками ресторанам";');
        $this->addCommentOnColumn('{{%catalog_goods}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%catalog_goods}}', 'cat_id','Идентификатор каталога');
        $this->addCommentOnColumn('{{%catalog_goods}}', 'base_goods_id','Идентификатор товара в основном каталоге поставщика');
        $this->addCommentOnColumn('{{%catalog_goods}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%catalog_goods}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%catalog_goods}}', 'discount_percent','Скидка на товар в процентах');
        $this->addCommentOnColumn('{{%catalog_goods}}', 'discount','Скидка на товар в денежных единицах (рублях)');
        $this->addCommentOnColumn('{{%catalog_goods}}', 'discount_fixed','Фиксированная цена на товар');
        $this->addCommentOnColumn('{{%catalog_goods}}', 'price','Цена на товар');
    }

    public function safeDown()
    {
        $this->execute('alter table `catalog_goods` comment "";');
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'id');
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'cat_id');
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'base_goods_id');
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'created_at');
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'updated_at');
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'discount_percent');
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'discount');
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'discount_fixed');
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'price');
    }
}
