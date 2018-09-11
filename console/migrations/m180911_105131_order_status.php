<?php

use yii\db\Migration;


class m180911_105131_order_status extends Migration
{

    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $columns = [
            'id' => $this->primaryKey(),
            'denom' => $this->string(255)->null()->defaultValue(null),
            'comment' => $this->string(255)->null()->defaultValue(null),
            'comment_edo' => $this->string(255)->null()->defaultValue(null),
        ];
        $this->createTable('{{%order_status}}', $columns, $tableOptions);

        $this->addCommentOnColumn('{{%order_status}}', 'id', 'ID статуса заказа');
        $this->addCommentOnColumn('{{%order_status}}', 'denom', 'ИДЕНТИФИКАТОР СООТВЕТСТВУЮЩЕЙ КОНСТАНТЫ В МОДЕЛИ ORDER');
        $this->addCommentOnColumn('{{%order_status}}', 'comment', 'Общее описание статуса');
        $this->addCommentOnColumn('{{%order_status}}', 'comment_edo', 'Описание статуса заказа, обрабатываемого в системе EDI');

        $rows = [
            [1, 'STATUS_AWAITING_ACCEPT_FROM_VENDOR', 'Ожидает подтверждения от поставщика', 'Заказ отправлен ПОСТАВЩИКУ, документ ORDERSP еще не получен'],
            [2, 'STATUS_AWAITING_ACCEPT_FROM_CLIENT', 'Ожидает подтверждения от ресторана', null],
            [3, 'STATUS_PROCESSING', 'Выполняется. Поставщик подтвердил заказ', 'От ПОСТАВЩИКА получен документ ORDERSP'],
            [4, 'STATUS_DONE', 'Завершен', 'Формируются накладные в учетную систему Ресторана'],
            [5, 'STATUS_REJECTED', 'Отклонен', null],
            [6, 'STATUS_CANCELLED', 'Отменен', 'ПОСТАВЩИК отменил заказ или ПОКУПАТЕЛЬ нажал кнопку "Отменить"'],
            [7, 'STATUS_FORMING', 'Формируется', null],
            [8, null, 'Отправлен поставщиком', 'От ПОСТАВЩИКА получен документ DESADV'],
            [9, null, 'Приемка завершена', 'ПОКУПАТЕЛЬ отправил ПОСТАВЩИКА документ RECADV'],
        ];
        foreach ($rows as $row) {
            $insert = [];
            foreach (array_keys($columns) as $k => $v) {
                $insert[$v] = $row[$k];
            }
            $this->insert('{{%order_status}}', $insert);
        }

    }

    public function safeDown()
    {
        $this->dropTable('{{%order_status}}');
    }

}
