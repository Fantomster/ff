<?php

use yii\db\Migration;

class m160925_165841_catalogs_250916 extends Migration
{
     public function safeUp()
    {
        $this->dropColumn('{{%catalog_goods}}', 'price');
	$this->addColumn('{{%catalog_goods}}', 'price', $this->decimal(10,2)->defaultValue(0));              
    }
    public function safeDown()
    {
        $this->dropColumn('{{%catalog_goods}}', 'price');
        $this->addColumn('{{%catalog_goods}}', 'price', $this->string(255));
    }
}
