<?php

use yii\db\Migration;

/**
 * Class m171212_104032_billing_logs
 */
class m171212_104032_billing_logs extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('billing_logs', [
            'id' => $this->primaryKey(),
            'message' => $this->text()->null(),
            'date' => $this->timestamp()->null(),
            'url' => $this->string(255)->null(),
            'method' => $this->string(10)->null(),
            'headers' => $this->text()->null(),
            'ip' => $this->string(17)->null()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('billing_logs');
    }
}
