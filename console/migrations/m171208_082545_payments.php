<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m171208_082545_payments
 */
class m171208_082545_payments extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('payment',[
            'payment_id' => $this->primaryKey(),
            'total' => $this->float()->notNull(),
            'receipt_number' => $this->string(50)->null(),
            'organization_id' => $this->integer()->notNull(),
            'type_payment' => $this->integer()->notNull(),
            'email' => $this->text()->null(),
            'phone' => $this->text()->null(),
            'date' => $this->timestamp()->null(),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->createTable('payment_type',[
            'type_id' => $this->primaryKey(),
            'title' => $this->text()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->insert('payment_type', ['title' => 'Абонентская плата']);
        $this->insert('payment_type', ['title' => 'Подключение']);

        $this->createIndex('Unique-index', 'payment', ['total', 'receipt_number', 'organization_id'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('Unique-index', 'payment');
        $this->dropTable('payment');
        $this->dropTable('payment_type');
    }
}
