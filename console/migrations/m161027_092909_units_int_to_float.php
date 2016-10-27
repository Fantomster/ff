<?php

use yii\db\Migration;

class m161027_092909_units_int_to_float extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%catalog_base_goods}}', 'units', $this->float()->defaultValue('1'));
    }
    public function safeDown()
    {
        $this->alterColumn('{{%catalog_base_goods}}', 'units', $this->integer()->defaultValue('1'));
    }
}
