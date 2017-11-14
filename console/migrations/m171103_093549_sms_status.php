<?php

use yii\db\Migration;

class m171103_093549_sms_status extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%sms_status}}', [
            'id' => $this->primaryKey(),
            'status' => $this->integer(2),
            'text' => $this->string(250)
        ]);

        $this->createIndex(
            'idx-sms_status',
            '{{%sms_status}}',
            'status'
        );

        $rows = [
            ['status' => 1, 'text' => 'отправлено'],
            ['status' => 2, 'text' => 'сообщение доставлено'],
            ['status' => 3, 'text' => 'время попыток доставить сообщение оператором истекло'],
            ['status' => 5, 'text' => 'сообщение не может быть доставлено (ошибка в номере, номер не существует и т.д.)'],
            ['status' => 8, 'text' => 'сообщение не принято оператором'],
            ['status' => 20, 'text' => 'отправка отменена пользователем'],
            ['status' => 21, 'text' => 'системная ошибка'],
            ['status' => 22, 'text' => 'оператор не сообщил о состоянии сообщения за отведенное время.'],
        ];

        $this->batchInsert('{{%sms_status}}', ['status', 'text'], $rows);
    }

    public function safeDown()
    {
        $this->dropIndex(
            'idx-sms_status',
            '{{%sms_status}}'
        );

        $this->dropTable('{{%sms_status}}');
    }
}
