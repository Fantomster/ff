<?php

use yii\db\Migration;

class m190215_105217_add_comments_table_preorder_content extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `preorder_content` comment "Таблица сведений о товарных позициях предзаказов";');
        $this->addCommentOnColumn('{{%preorder_content}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%preorder_content}}', 'preorder_id','Идентификатор предзаказа, к которому относится товарная позиция');
        $this->addCommentOnColumn('{{%preorder_content}}', 'product_id','Идентификатор товара в таблице catalog_base_goods');
        $this->addCommentOnColumn('{{%preorder_content}}', 'plan_quantity','Планируемое количество товара');
        $this->addCommentOnColumn('{{%preorder_content}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%preorder_content}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `preorder_content` comment "";');
        $this->dropCommentFromColumn('{{%preorder_content}}', 'id');
        $this->dropCommentFromColumn('{{%preorder_content}}', 'preorder_id');
        $this->dropCommentFromColumn('{{%preorder_content}}', 'product_id');
        $this->dropCommentFromColumn('{{%preorder_content}}', 'plan_quantity');
        $this->dropCommentFromColumn('{{%preorder_content}}', 'created_at');
        $this->dropCommentFromColumn('{{%preorder_content}}', 'updated_at');
    }
}
