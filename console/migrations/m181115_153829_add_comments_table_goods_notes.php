<?php

use yii\db\Migration;

class m181115_153829_add_comments_table_goods_notes extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `goods_notes` comment "Таблица пометок ресторанов о товарах";');
        $this->addCommentOnColumn('{{%goods_notes}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%goods_notes}}', 'rest_org_id','Идентификатор организации-ресторана');
        $this->addCommentOnColumn('{{%goods_notes}}', 'note','Пометка ресторана о товаре');
        $this->addCommentOnColumn('{{%goods_notes}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%goods_notes}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%goods_notes}}', 'catalog_base_goods_id','Идентификатор товара в главном каталоге (catalog_base_goods)');
    }

    public function safeDown()
    {
        $this->execute('alter table `goods_notes` comment "";');
        $this->dropCommentFromColumn('{{%goods_notes}}', 'id');
        $this->dropCommentFromColumn('{{%goods_notes}}', 'rest_org_id');
        $this->dropCommentFromColumn('{{%goods_notes}}', 'note');
        $this->dropCommentFromColumn('{{%goods_notes}}', 'created_at');
        $this->dropCommentFromColumn('{{%goods_notes}}', 'updated_at');
        $this->dropCommentFromColumn('{{%goods_notes}}', 'catalog_base_goods_id');
    }
}