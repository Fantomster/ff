<?php

use yii\db\Migration;
//m170221_112914_CBG_createIndexes
class m170130_123343_CBG_add_es_status extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'es_status', $this->integer()->null());
    }
    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'es_status');
    }
}
