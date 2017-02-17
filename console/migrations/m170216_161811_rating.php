<?php

use yii\db\Migration;

class m170216_161811_rating extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'rating', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%catalog_base_goods}}', 'rating', $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'rating');
        $this->dropColumn('{{%catalog_base_goods}}', 'rating');
    }
}
