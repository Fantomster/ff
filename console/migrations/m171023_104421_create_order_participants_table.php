<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_participants`.
 */
class m171023_104421_create_order_participants_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%order_participants}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'profile_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('{{%order_participants_order}}', '{{%order_participants}}', 'order_id', '{{%order}}', 'id');
        $this->addForeignKey('{{%order_participants_profile}}', '{{%order_participants}}', 'profile_id', '{{%profile}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%order_participants_order}}', '{{%order_participants}}');
        $this->dropForeignKey('{{%order_participants_profile}}', '{{%order_participants}}');
        $this->dropTable('{{%order_participants}}');
    }
}
