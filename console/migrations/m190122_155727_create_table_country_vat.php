<?php

use yii\db\Migration;

class m190122_155727_create_table_country_vat extends Migration
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
            '{{%country_vat}}',
            [
                'id'            => $this->primaryKey(11)->comment('Идентификатор записи в таблице'),
                'uuid'          => $this->string(36)->null()->defaultValue(null)->comment('Уникальный идентификатор государства'),
                'vats'          => $this->string()->null()->defaultValue(null)->comment('Ставки налогов в процентах'),
                'created_at'    => $this->timestamp()->null()->defaultValue(null)->comment('Дата и время создания записи в таблице'),
                'updated_at'    => $this->timestamp()->null()->defaultValue(null)->comment('Дата и время последнего изменения записи в таблице'),
                'created_by_id' => $this->integer(11)->null()->defaultValue(null)->comment('Идентификатор пользователя, создавшего запись'),
                'updated_by_id' => $this->integer(11)->null()->defaultValue(null)->comment('Идентификатор пользователя, последним изменившим запись'),
            ], $tableOptions
        );
        $this->execute('alter table `country_vat` comment "Таблица сведений о ставках налогов в государствах";');

    }

    public function safeDown()
    {
        $this->dropTable('{{%country_vat}}');
    }
}
