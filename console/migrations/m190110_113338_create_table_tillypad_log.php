<?php

use yii\db\Migration;

/**
 * Class m190110_113338_create_table_tillypad_log
 */
class m190110_113338_create_table_tillypad_log extends Migration
{
    public $tableName = '{{%tillypad_log}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id'              => $this->primaryKey()->comment('Идентификатор записи в таблице'),
            'operation_code'  => $this->string(25)->comment('Код операции, по которой следует запрос в систему'),
            'request'         => $this->text()->comment('Запрос в систему'),
            'response'        => $this->text()->comment('Ответ от системы на запрос'),
            'user_id'         => $this->integer()->comment('Идентификатор пользователя, осуществившего запрос в систему'),
            'organization_id' => $this->integer()->comment('Идентификатор организации, в которой работает пользователь, осуществивший запрос в систему'),
            'type'            => $this->string()->defaultValue('success')->comment('Тип успешности запроса в систему (error - запрос вернул ошибку, success - запрос завершился успешно)'),
            'request_at'      => $this->timestamp()->defaultValue(null)->comment('Дата и время отправки запроса в систему'),
            'response_at'     => $this->timestamp()->defaultValue(null)->comment('Дата и время получения ответа на запрос от системы'),
            'guide'           => $this->string(32)->notNull()->comment('Уникальный идентификатор запроса'),
            'ip'              => $this->string()->comment('IP-адрес, с которого был осуществлён запрос в систему'),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
