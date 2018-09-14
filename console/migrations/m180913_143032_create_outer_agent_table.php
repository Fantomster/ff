<?php

use yii\db\Migration;

/**
 * Handles the creation of table `outer_agent`.
 */
class m180913_143032_create_outer_agent_table extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%outer_agent}}',
            [
                'id'=> $this->primaryKey(11),
                'outer_uid'=> $this->string(45)->null()->comment('Внешний ID'),
                'service_id'=> $this->tinyInteger()->null()->comment('ID сервиса'),
                'name'=> $this->string(255)->null()->comment('Название'),
                'comment'=> $this->string(255)->null()->comment('Комментарий'),
                'vendor_id'=> $this->integer()->null()->comment('ID нашего поставщика'),
                'store_id'=> $this->integer()->null()->comment('ID склада'),
                'payment_delay'=> $this->integer()->null()->comment('Отложенная оплата в днях'),
                'org_id'=> $this->integer(11)->null()->comment('ID организации'),
                'is_deleted'=> $this->tinyInteger()->null()->defaultValue(0)->comment('Статус удаления'),
                'created_at'=> $this->timestamp()->null()->comment('Создано по GMT-0'),
                'updated_at'=> $this->timestamp()->null()->comment('Изменено по GMT-0'),
                'inn' => $this->string(15)->null()->comment('ИНН'),
                'kpp' => $this->string(15)->null()->comment('КПП'),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%outer_agent}}');
    }
}
