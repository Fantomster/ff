<?php

use yii\db\Migration;

class m181224_105054_integration_setting_change extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->createTable('{{%integration_setting_change}}', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'integration_setting_id' => $this->integer()->notNull(),
            'old_value' => $this->string(),
            'new_value' => $this->string()->notNull(),
            'changed_user_id' => $this->integer()->notNull(),
            'confirmed_user_id' => $this->integer()->notNull(),
            'is_active' => $this->tinyInteger()->defaultValue(1),
            'created_at' => $this->timestamp()->null(),
            'updated_at' => $this->timestamp()->null(),
            'confirmed_at' => $this->timestamp()->null()
        ]);

        $this->addForeignKey(
            '{{%integration_setting_change_integration_setting_id}}',
            '{{%integration_setting_change}}',
            'integration_setting_id',
            '{{%integration_setting}}',
            'id'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%integration_setting_change_integration_setting_id}}', '{{%integration_setting_change}}');
        $this->dropTable('{{%integration_setting_change}}');
    }
}
