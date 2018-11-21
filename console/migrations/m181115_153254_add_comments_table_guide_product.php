<?php

use yii\db\Migration;

class m181115_153254_add_comments_table_guide_product extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `guide_product` comment "Таблица сведений о товарах шаблонов заказов";');
        $this->addCommentOnColumn('{{%guide_product}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%guide_product}}', 'guide_id','Идентификатор шаблона заказа');
        $this->addCommentOnColumn('{{%guide_product}}', 'cbg_id','Идентификатор товара (catalog_base_goods)');
        $this->addCommentOnColumn('{{%guide_product}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%guide_product}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%guide_product}}', 'currency_id','Идентификатор валюты');
    }

    public function safeDown()
    {
        $this->execute('alter table `guide_product` comment "";');
        $this->dropCommentFromColumn('{{%guide_product}}', 'id');
        $this->dropCommentFromColumn('{{%guide_product}}', 'guide_id');
        $this->dropCommentFromColumn('{{%guide_product}}', 'cbg_id');
        $this->dropCommentFromColumn('{{%guide_product}}', 'created_at');
        $this->dropCommentFromColumn('{{%guide_product}}', 'updated_at');
        $this->dropCommentFromColumn('{{%guide_product}}', 'currency_id');
    }
}
