<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m180215_104736_create_relation_supp_rest_potential
 */
class m180215_104736_create_relation_supp_rest_potential extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%relation_supp_rest_potential}}', [
            'id' => Schema::TYPE_PK,
            'rest_org_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'supp_org_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'cat_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'invite' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'created_at' => Schema::TYPE_TIMESTAMP . ' NULL',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);

        $this->addColumn('{{%relation_supp_rest_potential}}', 'uploaded_catalog', $this->string()->null());
        $this->addColumn('{{%relation_supp_rest_potential}}', 'uploaded_processed', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%relation_supp_rest_potential}}', 'status', $this->integer()->defaultValue(0));
        $this->addColumn('{{%relation_supp_rest_potential}}', 'is_from_market', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%relation_supp_rest_potential}}', 'deleted', $this->boolean()->notNull()->defaultValue(false));




    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_settings}}');
    }
}
