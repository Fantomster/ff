<?php

use yii\db\Migration;

class m181012_081424_add_comments_fields_assorti5 extends Migration
{
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'category_id', 'Идентификатор категории товаров из Market Place');
        $this->addCommentOnColumn('{{%order_content}}', 'merc_uuid', 'Уникальный идентификатор товара в системе Ветис');
    }

    public function safeDown()
    {
        echo "m181012_081424_add_comments_fields_assorti5 cannot be reverted.\n";

        return false;
    }
}
