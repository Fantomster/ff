<?php

use yii\db\Migration;

/**
 * Handles the creation for table `delivery`.
 */
class m161003_074652_create_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%delivery}}', [
            'id' => $this->primaryKey(),
            'vendor_id' => $this->integer()->notNull(),
            'delivery_charge' => $this->decimal(10,2)->defaultValue(0),
            'min_free_delivery_charge' => $this->decimal(10,2)->defaultValue(0),
            'delivery_mon' => $this->boolean()->null()->defaultValue(false),
            'delivery_tue' => $this->boolean()->null()->defaultValue(false),
            'delivery_wed' => $this->boolean()->null()->defaultValue(false),
            'delivery_thu' => $this->boolean()->null()->defaultValue(false),
            'delivery_fri' => $this->boolean()->null()->defaultValue(false),
            'delivery_sat' => $this->boolean()->null()->defaultValue(false),
            'delivery_sun' => $this->boolean()->null()->defaultValue(false),
            'min_order_price' => $this->decimal(10,2)->defaultValue(0),
            'created_at' => $this->timestamp()->null(),
            'updated_at' => $this->timestamp()->null(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%delivery}}');
    }
}
