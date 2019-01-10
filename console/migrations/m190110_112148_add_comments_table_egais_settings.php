<?php

use yii\db\Migration;

class m190110_112148_add_comments_table_egais_settings extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `egais_settings` comment "Таблица сведений о настройках взаимодействия с системой ЕГАИС";');
        $this->addCommentOnColumn('{{%egais_settings}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%egais_settings}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%egais_settings}}', 'egais_url', 'URL-адрес, по которому возможен канал обмена данных с системой ЕГАИС');
        $this->addCommentOnColumn('{{%egais_settings}}', 'fsrar_id', 'Идентификатор организации-клиента в ФСРАР');
        $this->addCommentOnColumn('{{%egais_settings}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%egais_settings}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `egais_settings` comment "";');
        $this->dropCommentFromColumn('{{%egais_settings}}', 'id');
        $this->dropCommentFromColumn('{{%egais_settings}}', 'org_id');
        $this->dropCommentFromColumn('{{%egais_settings}}', 'egais_url');
        $this->dropCommentFromColumn('{{%egais_settings}}', 'fsrar_id');
        $this->dropCommentFromColumn('{{%egais_settings}}', 'created_at');
        $this->dropCommentFromColumn('{{%egais_settings}}', 'updated_at');
    }
}
