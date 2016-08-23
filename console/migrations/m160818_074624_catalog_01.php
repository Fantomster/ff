<?php
use yii\db\Schema;
use yii\db\Migration;

class m160818_074624_catalog_01 extends Migration
{    
    public function safeUp()
    {
		$tableOptions = null;
	    if ($this->db->driverName === 'mysql') {
	        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
	    }	    
	    $this->createTable('{{%catalog}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' not null',
            'org_supp_id' => Schema::TYPE_INTEGER . ' not null',
            'type' => Schema::TYPE_INTEGER . ' not null',
            'create_datetime' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);
        
        $this->createTable('{{%catalog_base_goods}}', [
            'id' => Schema::TYPE_PK,
            'cat_id' => Schema::TYPE_INTEGER . ' not null',
            'category_id' => Schema::TYPE_INTEGER . ' not null',
            'article' => Schema::TYPE_STRING . ' null',
            'product' => Schema::TYPE_STRING . ' null',
            'units' => Schema::TYPE_STRING . ' null',
            'price' => Schema::TYPE_STRING . ' null',
        ], $tableOptions);
        
        $this->createTable('{{%catalog_goods}}', [
            'id' => Schema::TYPE_PK,
            'cat_id' => Schema::TYPE_INTEGER . ' not null',
            'cat_base_goods_id' => Schema::TYPE_INTEGER . ' not null',
            'article' => Schema::TYPE_STRING . ' null',
            'product' => Schema::TYPE_STRING . ' null',
            'units' => Schema::TYPE_STRING . ' null',
            'price' => Schema::TYPE_STRING . ' null',
            'note' => Schema::TYPE_STRING . ' null',
        ], $tableOptions);	
                 
        $this->createTable('{{%category}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' not null',
        ], $tableOptions);   
        
        $this->createTable('{{%relation_category}}', [
            'id' => Schema::TYPE_PK,
            'relation_supp_rest_id' => Schema::TYPE_INTEGER . ' not null',
            'category' => Schema::TYPE_INTEGER . ' not null',
        ], $tableOptions); 
        
        $this->createTable('{{%relation_supp_rest}}', [
            'id' => Schema::TYPE_PK,
            'rest_org_id' => Schema::TYPE_INTEGER . ' not null',
            'sup_org_id' => Schema::TYPE_INTEGER . ' not null',
            'cat_id' => Schema::TYPE_INTEGER . ' not null',
        ], $tableOptions);
    }
    public function safeDown()
    {
	    $this->dropTable('{{%catalog}}');
        $this->dropTable('{{%catalog_base_goods}}');        
        $this->dropTable('{{%catalog_goods}}');        
        $this->dropTable('{{%category}}');        
        $this->dropTable('{{%relation_category}}');        
        $this->dropTable('{{%relation_supp_rest}}');
    }

}
