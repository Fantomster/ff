<?php

use yii\db\Migration;

/**
 * Class m180515_133321_change_mobile_phone_user
 */
class m180515_133321_change_mobile_phone_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sms_code_change_mobile}}',[
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'phone' => $this->string()->notNull(),
            'code' => $this->integer(4)->notNull(),
            'attempt' => $this->integer(2)->notNull()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->createIndex('{{%sms_code_change_mobile_index_user_id}}', '{{%sms_code_change_mobile}}', 'user_id', true);
        $this->addForeignKey('{{%sms_code_change_mobile_relation_user_id}}', '{{%sms_code_change_mobile}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%sms_code_change_mobile_relation_user_id}}', '{{%sms_code_change_mobile}}');
        $this->dropIndex('{{%sms_code_change_mobile_index_user_id}}', '{{%sms_code_change_mobile}}');
        $this->dropTable('{{%sms_code_change_mobile}}');
    }
}
