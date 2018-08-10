<?php

use yii\db\Migration;

/**
 * Handles the creation of table `blocked_cbg`.
 */
class m180808_115327_create_blocked_cbg_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%catalog_goods_blocked}}', [
            'id' => $this->primaryKey(),
            'cbg_id' => $this->integer()->notNull(),
            'owner_organization_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->null(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%catalog_goods_blocked}}');
    }
}
