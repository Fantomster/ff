<?php

use yii\db\Migration;

/**
 * Class m180215_115037_order_completion_date
 */
class m180215_115037_order_completion_date extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('order', 'completion_date', $this->timestamp()->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'completion_date');
    }
}
