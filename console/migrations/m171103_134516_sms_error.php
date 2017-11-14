<?php

use yii\db\Migration;

class m171103_134516_sms_error extends Migration
{
    private $tableName = '{{%sms_error}}';

    public function safeUp()
    {
        $this->createTable($this->tableName,[
            'id' => $this->primaryKey(),
            'date' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'message' => $this->string(),
            'target' => $this->string(),
            'error' => $this->string()
        ]);
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
