<?php

use yii\db\Migration;

/**
 * Class m181204_112753_egais_request_response_table
 */
class m181204_112753_egais_request_response_table extends Migration
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

        $this->createTable('{{%egais_request_response}}', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'act_id' => $this->integer()->notNull(),
            'doc_id' => $this->integer()->notNull(),
            'operation_name' => $this->string(250)->null(),
            'result' => $this->string(250)->null(),
            'conclusion' => $this->string(250)->null(),
            'date' => $this->string()->null(),
            'comment' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
        ], $tableOptions);

        $this->addForeignKey(
            '{{%egais_request_response_act_id}}',
            '{{%egais_request_response}}',
            'act_id',
            '{{%egais_act_write_on}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%egais_request_response_act_id}}', '{{%egais_request_response}}');
        $this->dropTable('{{%egais_request_response}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181204_112753_egais_request_response_table cannot be reverted.\n";

        return false;
    }
    */
}
