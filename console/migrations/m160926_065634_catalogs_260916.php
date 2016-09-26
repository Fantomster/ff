<?php

use yii\db\Migration;

class m160926_065634_catalogs_260916 extends Migration
{
    public function safeUp()
    {
     $this->dropColumn('{{%catalog_base_goods}}', 'units');   
     $this->dropColumn('{{%catalog_base_goods}}', 'category_id'); 
     $this->addColumn('{{%catalog_base_goods}}', 'units', $this->integer()->defaultValue(null)); 
     $this->addColumn('{{%catalog_base_goods}}', 'category_id', $this->integer()->defaultValue(null));
    }
    public function safeDown()
    {
     //$this->addColumn('{{%catalog_base_goods}}', 'units', $this->integer()->defaultValue(0));  
    }
}
