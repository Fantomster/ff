<?php

use yii\db\Migration;

class m170107_231700_es_status extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%catalog_base_goods}}', 'es_status', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%catalog_base_goods}}', 'es_status');
    }
}
