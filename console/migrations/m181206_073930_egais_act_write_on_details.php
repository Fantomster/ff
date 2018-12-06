<?php

use yii\db\Migration;

/**
 * Class m181206_073930_egais_act_write_on_details
 */
class m181206_073930_egais_act_write_on_details extends Migration
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
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%egais_act_write_on_details}}', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'act_write_on_id' => $this->integer()->notNull(),
            'act_reg_id' => $this->string(250)->notNull(),
            'number' => $this->integer()->null(),
            'identity' => $this->integer()->null(),
            'in_form_f1_reg_id' => $this->string(250)->notNull(),
            'f2_reg_id' => $this->string(250)->notNull(),
            'status' => $this->string(250)->null(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
        ], $tableOptions);

        $this->addForeignKey(
            '{{%egais_act_write_on_details_act_write_on_id}}',
            '{{%egais_act_write_on_details}}',
            'act_write_on_id',
            '{{%egais_act_write_on}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%egais_act_write_on_details_act_write_on_id}}', '{{%egais_act_write_on_details}}');
        $this->dropTable('{{%egais_act_write_on_details}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181206_073930_egais_act_write_on_details cannot be reverted.\n";

        return false;
    }
    */
}
