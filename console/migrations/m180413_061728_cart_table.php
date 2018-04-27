<?php

use yii\db\Migration;

/**
 * Class m180413_061728_cart_table
 */
class m180413_061728_cart_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%cart}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->createIndex('{{%cart_index_user_id}}', '{{%cart}}', 'user_id', true);
        $this->createIndex('{{%cart_index_organization_id}}', '{{%cart}}', 'organization_id');
        $this->createIndex('{{%cart_index_org_and_user_id}}', '{{%cart}}', ['organization_id', 'user_id'], true);

        $this->addForeignKey('{{%cart_relation_organization_id}}', '{{%cart}}', 'organization_id', '{{%organization}}', 'id');
        $this->addForeignKey('{{%cart_relation_user_id}}', '{{%cart}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%cart_relation_organization_id}}', '{{%cart}}');
        $this->dropForeignKey('{{%cart_relation_user_id}}', '{{%cart}}');
        $this->dropIndex('{{%cart_index_user_id}}', '{{%cart}}');
        $this->dropIndex('{{%cart_index_organization_id}}', '{{%cart}}');
        $this->dropIndex('{{%cart_index_org_and_user_id}}', '{{%cart}}');
        $this->dropTable('{{%cart}}');
    }
}
