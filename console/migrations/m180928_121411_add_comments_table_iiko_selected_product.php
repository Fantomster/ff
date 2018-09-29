<?php

use yii\db\Migration;

class m180928_121411_add_comments_table_iiko_selected_product extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_selected_product` comment "Таблица сведений о доступных товарах в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_selected_product}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_selected_product}}', 'product_id', 'Идентификатор товара-продукта');
        $this->addCommentOnColumn('{{%iiko_selected_product}}', 'organization_id', 'Идентификатор организации, для которой доступен товар');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_selected_product` comment "";');
        $this->dropCommentFromColumn('{{%iiko_selected_product}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_selected_product}}', 'product_id');
        $this->dropCommentFromColumn('{{%iiko_selected_product}}', 'organization_id');
    }
}
