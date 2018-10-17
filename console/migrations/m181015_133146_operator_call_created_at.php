<?php

use yii\db\Migration;

/**
 * Class m181015_133146_operator_call_created_at
 */
class m181015_133146_operator_call_created_at extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(\common\models\OperatorCall::tableName(), 'created_at', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
