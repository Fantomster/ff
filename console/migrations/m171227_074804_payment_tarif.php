<?php

use yii\db\Migration;

/**
 * Class m171227_074804_payment_tarif
 */
class m171227_074804_payment_tarif extends Migration
{
    public $tableName = '{{%payment_tarif}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'tarif_id' => $this->primaryKey(),
            'payment_type_id' => $this->integer()->notNull(),
            'organization_type_id' => $this->integer()->notNull(),
            'price' => $this->float()->notNull(),
            'status' => $this->integer()->defaultValue(1),
            'organization_id' => $this->integer()->null()->comment('Индивидуальная цена для организации'),
            'individual' => $this->integer()->defaultValue(0)->comment('Индивидуальный прайс'),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->createIndex('idx-tarif-payment_type_id', $this->tableName, 'payment_type_id');
        $this->createIndex('idx-tarif-organization_type', $this->tableName, 'organization_type_id');
        $this->createIndex('idx-tarif-organization_id', $this->tableName, 'organization_id');

        $this->addForeignKey(
            'fk-tarif-payment_type_id',
            $this->tableName,
            'payment_type_id',
            'payment_type',
            'type_id'
        );

        $this->addForeignKey(
            'fk-tarif-organization_type_id',
            $this->tableName,
            'organization_type_id',
            'organization_type',
            'id'
        );

        $this->addForeignKey(
            'fk-tarif-organization_id',
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
        $this->dropForeignKey('fk-tarif-payment_type_id', $this->tableName);
        $this->dropForeignKey('fk-tarif-organization_type_id', $this->tableName);
        $this->dropForeignKey('fk-tarif-organization_id', $this->tableName);

        $this->dropIndex('idx-tarif-payment_type_id', $this->tableName);
        $this->dropIndex('idx-tarif-organization_type', $this->tableName);
        $this->dropIndex('idx-tarif-organization_id', $this->tableName);

        $this->dropTable($this->tableName);
    }
}
