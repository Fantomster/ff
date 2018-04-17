<?php

use yii\db\Migration;

/**
 * Class m180417_074232_cart_table_delete_uniqe_user_id
 */
class m180417_074232_cart_table_delete_uniqe_user_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('{{%cart_relation_user_id}}', '{{%cart}}');
        $this->dropIndex('{{%cart_index_user_id}}', '{{%cart}}');
        $this->createIndex('{{%cart_index_user_id}}', '{{%cart}}', 'user_id');
        $this->addForeignKey('{{%cart_relation_user_id}}', '{{%cart}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%cart_relation_user_id}}', '{{%cart}}');
        $this->dropIndex('{{%cart_index_user_id}}', '{{%cart}}');
        $this->createIndex('{{%cart_index_user_id}}', '{{%cart}}', 'user_id', true);
        $this->addForeignKey('{{%cart_relation_user_id}}', '{{%cart}}', 'user_id', '{{%user}}', 'id');
    }
}
