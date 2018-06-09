<?php

use yii\db\Migration;

/**
 * Handles the creation of table `one_s_store`.
 */
class m180607_122623_create_one_s_store_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public $tableName = '{{%one_s_store}}';


    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }


    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'cid' => $this->string(36)->notNull(),
            'name' => $this->string(255)->notNull(),
            'address' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('one_s_store');
    }
}
