<?php

use yii\db\Schema;
use yii\db\Migration;

class m160818_074624_catalog_01 extends Migration {

    public function safeUp() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%catalog}}', [
            'id' => Schema::TYPE_PK,
            'type' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'supp_org_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'status' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'created_at' => Schema::TYPE_TIMESTAMP . ' NULL',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createTable('{{%catalog_base_goods}}', [
            'id' => Schema::TYPE_PK,
            'cat_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'category_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'article' => Schema::TYPE_STRING . ' NULL',
            'product' => Schema::TYPE_STRING . ' NULL',
            'units' => Schema::TYPE_STRING . ' NULL',
            'price' => Schema::TYPE_STRING . ' NULL',
            'status' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'market_place' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'deleted' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'created_at' => Schema::TYPE_TIMESTAMP . ' NULL',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createTable('{{%catalog_goods}}', [
            'id' => Schema::TYPE_PK,
            'cat_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'base_goods_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'price' => Schema::TYPE_STRING . ' NULL',
            'note' => Schema::TYPE_STRING . ' NULL',
            'discount' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'created_at' => Schema::TYPE_TIMESTAMP . ' NULL',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createTable('{{%category}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
        ], $tableOptions);

        $this->createTable('{{%relation_category}}', [
            'id' => Schema::TYPE_PK,
            'category_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'rest_org_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'supp_org_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'created_at' => Schema::TYPE_TIMESTAMP . ' NULL',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->createTable('{{%relation_supp_rest}}', [
            'id' => Schema::TYPE_PK,
            'rest_org_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'supp_org_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'cat_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'invite' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'created_at' => Schema::TYPE_TIMESTAMP . ' NULL',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);
    }

    public function safeDown() {
        $this->dropTable('{{%catalog}}');
        $this->dropTable('{{%catalog_base_goods}}');
        $this->dropTable('{{%catalog_goods}}');
        $this->dropTable('{{%category}}');
        $this->dropTable('{{%relation_category}}');
        $this->dropTable('{{%relation_supp_rest}}');
    }

}
