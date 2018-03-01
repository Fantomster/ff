<?php

use yii\db\Migration;

/**
 * Class m180222_062425_integration_invoice_head
 */
class m180222_062425_integration_invoice_head extends Migration
{
    public $tableName = 'integration_invoice';
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'integration_setting_from_email_id' => $this->integer()->notNull(),
            'number' => $this->string(),
            'date' => $this->timestamp(),
            'email_id' => $this->string(),
            'file_mime_type' => $this->string(),
            'file_content' => $this->text(),
            'file_hash_summ' => $this->string(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->createIndex('idx-integration_invoice_head-setting_from_email_id', $this->tableName, 'integration_setting_from_email_id');
        $this->createIndex('idx-integration_invoice_head-organization_id', $this->tableName, 'organization_id');

        $this->addForeignKey(
            'fk-integration_invoice_head-setting_from_email_id',
            $this->tableName,
            'integration_setting_from_email_id',
            'integration_setting_from_email',
            'id'
        );

        $this->addForeignKey(
            'fk-integration_invoice_head-organization_id',
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
        $this->dropForeignKey('fk-integration_invoice_head-setting_from_email_id', $this->tableName);
        $this->dropForeignKey('fk-integration_invoice_head-organization_id', $this->tableName);
        $this->dropIndex('idx-integration_invoice_head-setting_from_email_id', $this->tableName);
        $this->dropIndex('idx-integration_invoice_head-organization_id', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
