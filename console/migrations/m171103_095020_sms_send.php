<?php

use yii\db\Migration;

class m171103_095020_sms_send extends Migration
{
    private $tableName = '{{%sms_send}}';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'sms_id' => $this->string(),
            'status_id' => $this->integer(2)->defaultValue(1),
            'text' => $this->text(),
            'target' => $this->string(),
            'created_at' => $this->timestamp()->null()->defaultValue(null),
            'updated_at' => $this->timestamp()->null()->defaultValue(null),
            'provider' => $this->string()
        ]);

        // creates index for column `status`
        $this->createIndex(
            'idx-sms_send_status',
            $this->tableName,
            'status_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-sms_status',
            $this->tableName,
            'status_id',
            'sms_status',
            'status'
        );
    }

    public function safeDown()
    {
        $this->dropIndex(
            'idx-sms_send_status',
            $this->tableName
        );

        $this->dropForeignKey(
            'fk-sms_status',
            $this->tableName
        );

        $this->dropTable($this->tableName);
    }
}
