<?php

use yii\db\Migration;

/**
 * Class m180221_065403_integration_setting_from_email
 */
class m180221_065403_integration_setting_from_email extends Migration
{
    public $tableName = 'integration_setting_from_email';
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'server_type' => $this->string()->notNull(),
            'server_host' => $this->string()->notNull(),
            'server_port' => $this->integer()->notNull(),
            'server_ssl' => $this->integer()->notNull()->defaultValue(1),
            'user' => $this->string()->notNull(),
            'password' => $this->string()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->createIndex('idx-integration_setting_from_email-organization_id', $this->tableName, 'organization_id');

        $this->addForeignKey(
            'fk-integration_setting_from_email-organization_id',
            $this->tableName,
            'organization_id',
            'organization',
            'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-integration_setting_from_email-organization_id', $this->tableName);
        $this->dropIndex('idx-integration_setting_from_email-organization_id', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
