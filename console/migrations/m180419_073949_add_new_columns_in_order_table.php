<?php

use yii\db\Migration;

/**
 * Class m180419_073949_add_new_columns_in_order_table
 */
class m180419_073949_add_new_columns_in_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->dropColumn(\common\models\Order::tableName(), 'order_code');
        $this->addColumn(\common\models\OrderContent::tableName(), 'plan_price', $this->decimal(20, 2)->defaultValue(0.00));
        $this->addColumn(\common\models\OrderContent::tableName(), 'plan_quantity', $this->decimal(20, 3)->defaultValue(0.000));
        $this->addColumn(\common\models\OrderContent::tableName(), 'updated_at', $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')));
        $this->addColumn(\common\models\OrderContent::tableName(), 'updated_user_id', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->addColumn(\common\models\Order::tableName(), 'order_code', $this->integer()->defaultValue(0));
        $this->dropColumn(\common\models\OrderContent::tableName(), 'updated_at');
        $this->dropColumn(\common\models\OrderContent::tableName(), 'updated_user_id');
        $this->dropColumn(\common\models\OrderContent::tableName(), 'plan_price');
        $this->dropColumn(\common\models\OrderContent::tableName(), 'plan_quantity');
    }

}
