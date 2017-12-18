<?php

use yii\db\Migration;

/**
 * Class m171213_064928_billing_payment
 */
class m171213_064928_billing_payment extends Migration
{
    public $tableName = 'billing_payment';
    public $index_array = ['currency_id', 'organization_id', 'payment_type_id', 'user_id'];

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'billing_payment_id' => $this->primaryKey(),
            'amount' => $this->float()->notNull()->comment('Сумма оплаты'),
            'currency_id' => $this->integer()->notNull()->defaultValue('1')->comment('Валюта'),
            'user_id' => $this->integer()->comment('Пользователь'),
            'organization_id' => $this->integer()->comment('Организация'),
            'status' => $this->integer(1)->notNull()->defaultValue('0')->comment('Статус платежа'),
            'payment_type_id' => $this->integer(2)->notNull()->comment('Тип платежа'),
            'idempotency_key' => $this->string(36)->null()->comment('Ключ идемпотенции'),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()'))->comment('Дата создания платежа'),
            'capture_at' => $this->timestamp()->null()->comment('Дата подверждения'),
            'payment_at' => $this->timestamp()->null()->comment('Дата оплаты'),
            'refund_at' => $this->timestamp()->null()->comment('Дата отмены'),
            'external_payment_id' => $this->string(50)->null()->comment('Ключ платежа в платежной системе'),
            'external_created_at' => $this->timestamp()->null()->comment('Дата создания платежа у провайдера'),
            'external_expires_at' => $this->timestamp()->null()->comment('Срок для подтверждения платежа'),
        ]);

        // creates $index_array
        foreach ($this->index_array as $index) {
            $this->createIndex(
                'idx-' . $this->tableName . '-' . $index,
                $this->tableName,
                $index
            );
        }
        // add foreign key
        $this->addForeignKey('fk-' . $this->tableName . '-currency_id', $this->tableName, 'currency_id', 'currency', 'id', 'RESTRICT');
        $this->addForeignKey('fk-' . $this->tableName . '-organization_id', $this->tableName, 'organization_id', 'organization', 'id', 'RESTRICT');
        $this->addForeignKey('fk-' . $this->tableName . '-payment_type_id', $this->tableName, 'payment_type_id', 'payment_type', 'type_id', 'RESTRICT');
        $this->addForeignKey('fk-' . $this->tableName . '-user_id', $this->tableName, 'user_id', 'user', 'id', 'RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-' . $this->tableName . '-currency_id', $this->tableName);
        $this->dropForeignKey('fk-' . $this->tableName . '-organization_id', $this->tableName);
        $this->dropForeignKey('fk-' . $this->tableName . '-payment_type_id', $this->tableName);
        $this->dropForeignKey('fk-' . $this->tableName . '-user_id', $this->tableName);

        foreach ($this->index_array as $index) {
            $this->dropIndex('idx-' . $this->tableName . '-' . $index, $this->tableName);
        }

        $this->dropTable($this->tableName);
    }

}
