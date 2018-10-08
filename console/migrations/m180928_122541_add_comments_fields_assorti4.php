<?php

use yii\db\Migration;

class m180928_122541_add_comments_fields_assorti4 extends Migration
{
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'es_status', 'Показатель состояния индесации товара в поисковом движке Elastic Search (0 - не участвует в поиске, 1  - участвует в поиске)');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'mp_show_price', 'Показатель состояния необходимости показа цены на товар в Market Place');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'es_status');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'mp_show_price');
    }
}
