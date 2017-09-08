<?php

use yii\db\Migration;

/**
 * Handles the creation of table `relation_manager_leader`.
 */
class m170904_072224_create_relation_manager_leader_table extends Migration
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

        $this->createTable('{{%relation_manager_leader}}', [
            'id' => $this->primaryKey(),
            'manager_id' => $this->integer()->notNull(),
            'leader_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('{{%relation_manager_id}}', '{{%relation_manager_leader}}', 'manager_id', '{{%user}}', 'id');
        $this->addForeignKey('{{%relation_leader_id}}', '{{%relation_manager_leader}}', 'leader_id', '{{%user}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%relation_manager_id}}', '{{%relation_manager_leader}}');
        $this->dropForeignKey('{{%relation_leader_id}}', '{{%relation_manager_leader}}');
        $this->dropTable('{{%relation_manager_leader}}');
    }
}
