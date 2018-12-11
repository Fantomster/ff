<?php

use yii\db\Migration;

/**
 * Class m181210_084855_egais_query_rests
 */
class m181210_084855_egais_query_rests extends Migration
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
        $this->createTable('{{%egais_query_rests}}', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'reply_id' => $this->string()->notNull(),
            'status' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%egais_query_rests}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181210_084855_egais_query_rests cannot be reverted.\n";

        return false;
    }
    */
}
