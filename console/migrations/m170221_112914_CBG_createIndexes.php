<?php

use yii\db\Migration;

class m170221_112914_CBG_createIndexes extends Migration
{
    public function safeUp()
    {
        $this->createIndex('cat_id', '{{%catalog_base_goods}}', 'cat_id');
        $this->createIndex('article', '{{%catalog_base_goods}}', 'article');
    }

    public function safeDown()
    {
        $this->dropIndex('cat_id', '{{%catalog_base_goods}}', 'cat_id');
        $this->dropIndex('article', '{{%catalog_base_goods}}', 'article');
    }
}
