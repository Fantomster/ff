<?php

use yii\db\Migration;

class m180919_133047_outer_category extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {

        $this->createTable('outer_category',[
            'id' => $this->primaryKey()->comment('ID записи данных о категории'),
            'outer_uid' => $this->string(45)->null()->comment('ID записи категории в источнике загрузки данных'),
            'service_id' => $this->tinyInteger()->null()
                ->comment('ID сервиса, с помощью которого была произведена загрузка данной категории'),
            'org_id' => $this->Integer()->null()
                ->comment('ID организации, к которой относится данная категория'),
            'name' => $this->string(255)->null()->comment('Наименование категории'),
            'is_deleted' => $this->tinyInteger(1)->null()->comment('Признак неиспользуемой категории'),
            'created_at' => $this->timestamp()->null()->comment('Метка времени - дата и время создания записи категории'),
            'updated_at' => $this->timestamp()->null()->comment('Метка времени - дата и время последного изменения записи категории'),
            'selected' => $this->tinyInteger(1)->null()->comment('Признак отбора данной категории'),
            'collapsed' => $this->tinyInteger(1)->null()->comment('Признак свернутости данной категории'),
            'tree' => $this->integer()->null()->comment('ID корневого элемента'),
            'left' => $this->integer()->null()->comment('Левое включаемое значение выборки типа nested sets'),
            'right' => $this->integer()->null()->comment('Правое включаемое значение выборки типа nested sets'),
            'level' => $this->integer()->null()->comment('Уровень подчиненности записи (ID родильской записи) для выборки типа nested sets'),
        ]);

        $this->createTable('outer_task',[
            'id' => $this->primaryKey()->comment('ID записи данных о задаче'),
            'service_id' => $this->tinyInteger()->null()
                ->comment('ID сервиса, к которому относится данная задача'),
            'org_id' => $this->Integer()->null()
                ->comment('ID организации, к которой относится данная задача'),
            'outer_guid' => $this->string(45)->null()->comment('ID соотвествующей транзакции во внешнем обработчике'),
            'oper_code' => $this->tinyInteger()->null()->comment('ID атомарного действия во внешнем обработчике [all_service_operation:id]'),
            'requested_at' => $this->timestamp()->null()->comment('Метка времени - дата и время формирования запроса'),
            'responced_at' => $this->timestamp()->null()->comment('Метка времени - дата и время получения ответа в синхронном режиме'),
            'callbacked_at' => $this->timestamp()->null()->comment('Метка времени - дата и время получения ответа в асинхронном режиме'),
            'retry' => $this->tinyInteger()->null()->comment('Порядковый номер последней попытки выполнения задачи в виде отправки запроса'),
            'inner_guid' => $this->string(45)->null()->comment('ID соотвествующей транзакции в нашей системе'),
            'salespoint_id' => $this->string(128)->null()->comment('ID сертификата/лицензии на подключение к системе'),
            'int_status_id' => $this->tinyInteger()->null()->comment('ID статуса задачи в нашей системе'),
            'broker_status_id' => $this->tinyInteger()->null()->comment('ID статуса задачи во внешнем обработчике'),
            'client_status_id' => $this->tinyInteger()->null()->comment('ID конечного статуса задачи во внешнем обработчике'),
            'broker_version' => $this->string(128)->null()->comment('Версия механизма API, задействованного во внешнем обработчике'),
            'total_parts' => $this->tinyInteger()->null()->comment('Общее количество порций получаемых данных (при загрузке данных из источника порциями)'),
            'current_part' => $this->tinyInteger()->null()->comment('Текущий номер порции получаемых данных (при загрузке данных из источника порциями)'),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('outer_task');
        $this->dropTable('outer_category');
    }

}