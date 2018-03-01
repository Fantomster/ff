<?php

use yii\db\Migration;

/**
 * Class m180222_062436_integration_invoice_content
 */
class m180222_062436_integration_invoice_content extends Migration
{
    public $tableName = 'integration_invoice_content';
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer()->notNull(),
            'row_number' => $this->integer(),
            'article' => $this->string(),
            'title' => $this->string(),
            'percent_nds' => $this->integer(),
            'price_nds' => $this->float(),
            'price_without_nds' => $this->float(),
            'quantity' => $this->integer(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->createIndex('idx-integration_invoice_content-invoice_id', $this->tableName, 'invoice_id');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('idx-integration_invoice_content-invoice_id', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
