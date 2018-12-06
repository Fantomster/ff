<?php

use yii\db\Migration;

/**
 * Class m181206_134812_alter_column_koef_to_decimal
 */
class m181206_134812_alter_column_koef_to_decimal extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%waybill_content}}', 'koef', $this->decimal(10, 6));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181206_134812_alter_column_koef_to_decimal cannot be reverted.\n";

        return false;
    }
}