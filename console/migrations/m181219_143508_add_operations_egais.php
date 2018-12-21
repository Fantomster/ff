<?php

use yii\db\Migration;

/**
 * Class m181219_143508_add_operations_egais
 */
class m181219_143508_add_operations_egais extends Migration
{
    public $tableName = '{{%all_service_operation}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->batchInsert($this->tableName, ['service_id', 'code', 'denom', 'comment'], [
            [5, 1, 'sendQueryRests', 'Запрос остатков'],
            [5, 2, 'sendActWriteOn', 'Акт постановки на баланс'],
            [5, 3, 'sendActWriteOff', 'Акт списания'],
            [5, 4, 'getAllIncomingDoc', 'Получение всех входящих документов'],
            [5, 5, 'getOneIncomingDoc', 'Получение одного входящего документа'],
            [5, 6, 'queryByTypeDoc', 'Отправка запроса в утм по типу'],
            [5, 7, 'getUrlDoc', 'Запрос url документа'],
            [5, 8, 'parse OneIncomingDoc', 'Парсинг входящего документа'],
            [5, 9, 'parse OneIncomingDoc', 'Парсинг для получения url'],
            [5, 10, 'parse ReplyId', 'Парсинг для получения reply_id'],
            [5, 11, 'save QueryRests', 'Сохранение QueryRests в базу'],
            [5, 12, 'save ActWriteOn', 'Сохранение ActWriteOn в базу'],
            [5, 13, 'save ActWriteOff', 'Сохранение ActWriteOff в базу'],
            [5, 14, 'save TicketAndAct', 'Сохранение Ticket и Акта в базу'],
            [5, 15, 'save ProductAndAct', 'Сохранение Продукта и Акта в базу'],
            [5, 16, 'save Inventory', 'Сохранение Inventory в базу'],
            [5, 17, 'unknown ChargeOnType', 'Неизвестный тип ChargeOn'],
            [5, 18, 'unknown TypeWriteOff', 'Неизвестный тип TypeWriteOff'],
        ]);
    }

    public function safeDown()
    {
        $this->delete($this->tableName, ['service_id' => 5]);
    }
}
