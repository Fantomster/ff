<?php

use yii\db\Migration;

/**
 * Class m181211_123010_egais_egais_write_off_history
 */
class m181211_123010_egais_egais_write_off_history extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%egais_write_off_history}}', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'act_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'type_write_off_id' => $this->integer()->notNull(),
            'status' => $this->integer()->null(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->addForeignKey(
            '{{%egais_write_off_history_act_id}}',
            '{{%egais_write_off_history}}',
            'act_id',
            '{{%egais_write_off}}',
            'id'
        );

        $this->addForeignKey(
            '{{%egais_write_off_history_product_id}}',
            '{{%egais_write_off_history}}',
            'product_id',
            '{{%egais_product_on_balance}}',
            'id'
        );

        $this->addForeignKey(
            '{{%egais_write_off_history_type_write_off_id}}',
            '{{%egais_write_off_history}}',
            'type_write_off_id',
            '{{%egais_type_write_off}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%egais_write_off_history_type_write_off_id}}', '{{%egais_write_off_history}}');
        $this->dropForeignKey('{{%egais_write_off_history_product_id}}', '{{%egais_write_off_history}}');
        $this->dropForeignKey('{{%egais_write_off_history_act_id}}', '{{%egais_write_off_history}}');
        $this->dropTable('{{%egais_write_off_history}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181211_123010_egais_egais_write_off_history cannot be reverted.\n";

        return false;
    }
    */
}
