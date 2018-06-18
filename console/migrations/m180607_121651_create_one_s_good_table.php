<?php

use yii\db\Migration;

/**
 * Handles the creation of table `one_s_good`.
 */
class m180607_121651_create_one_s_good_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public $tableName = '{{%one_s_good}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'cid' => $this->string(36)->notNull(),
            'parent_id' => $this->string(36)->null(),
            'org_id' => $this->integer()->notNull(),
            'measure' => $this->string(50)->null(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('one_s_goods');
    }
}
