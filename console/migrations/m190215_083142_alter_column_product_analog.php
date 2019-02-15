<?php

use yii\db\Migration;

/**
 * Class m190215_083142_alter_column_product_analog
 */
class m190215_083142_alter_column_product_analog extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(\common\models\ProductAnalog::tableName(), 'coefficient', $this->decimal(10,6)->defaultValue(1.000000));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190215_083142_alter_column_product_analog cannot be reverted.\n";
        return false;
    }
}
