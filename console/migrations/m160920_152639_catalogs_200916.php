<?php

use yii\db\Migration;
use yii\db\Schema;

class m160920_152639_catalogs_200916 extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->dropColumn('{{%catalog_base_goods}}', 'price');
        $this->dropColumn('{{%catalog_goods}}', 'note');
	$this->addColumn('{{%catalog_base_goods}}', 'price', $this->decimal(10,2)->defaultValue(0));
        $this->addColumn('{{%catalog_base_goods}}', 'image', $this->string(255));
        
        $this->dropColumn('{{%catalog_goods}}', 'discount');
        $this->dropColumn('{{%catalog_goods}}', 'discount_fixed');
        $this->addColumn('{{%catalog_goods}}', 'discount', $this->decimal(10,2)->defaultValue(0));
        $this->addColumn('{{%catalog_goods}}', 'discount_fixed', $this->decimal(10,2)->defaultValue(0));
        
        $this->createTable('{{%goods_notes}}', [
            'id' => Schema::TYPE_PK,
            'rest_org_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'catalog_goods_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'note' => Schema::TYPE_STRING . ' NULL',
            'created_at' => Schema::TYPE_TIMESTAMP . ' NULL',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);       
    }
    public function safeDown()
    {
        $this->addColumn('{{%catalog_goods}}', 'note', $this->string(255));
        $this->addColumn('{{%catalog_base_goods}}', 'price', $this->integer()->defaultValue(0));
        $this->dropColumn('{{%catalog_base_goods}}', 'image');
        
        
        $this->dropColumn('{{%catalog_goods}}', 'discount');
        $this->dropColumn('{{%catalog_goods}}', 'discount_fixed');
        
        $this->addColumn('{{%catalog_goods}}', 'discount', $this->integer()->defaultValue(0));
        $this->addColumn('{{%catalog_goods}}', 'discount_fixed', $this->integer()->defaultValue(0));
        
        $this->dropTable('{{%goods_notes}}');

    }
}
