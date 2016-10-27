<?php

use yii\db\Migration;

class m161027_131608_alter_table_units extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%catalog_base_goods}}', 'units', $this->float()->null());
    }
    public function safeDown()
    {
        $this->alterColumn('{{%catalog_base_goods}}', 'units', $this->integer()->null());
    }
}
