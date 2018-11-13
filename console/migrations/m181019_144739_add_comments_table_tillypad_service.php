<?php

use yii\db\Migration;

class m181019_144739_add_comments_table_tillypad_service extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    
    public function safeUp()
    {
        $this->execute('alter table `tillypad_service` comment "Таблица сведений о лицензиях Tillypad";');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'org', 'Идентификатор организации, использующей лицензию Tillypad');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'fd', 'Дата и время начала действия лицензии Tillypad');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'td', 'Дата и время окончания действия лицензии Tillypad');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'status_id', 'Показатель статуса лицензии Tillypad (1 - активна, 0 - не активна)');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'is_deleted', 'Показатель удаления лицензии Tillypad (1 - не удалена, 0 - удалена)');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'object_id', 'Идентификатор ресторана (аппаратный ключ Tillypad - не используется)');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'user_id', 'Идентификатор пользователя, оформившего лицензию Tillypad');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'code', 'ID объекта - не используется');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'name', 'Наименование организации, использующей лицензию Tillypad');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'address', 'Адрес организации, использующей лицензию Tillypad');
        $this->addCommentOnColumn('{{%tillypad_service}}', 'phone', 'Номер телефона организации, использующей лицензию Tillypad');
    }

    public function safeDown()
    {
        $this->execute('alter table `tillypad_service` comment "";');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'id');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'org');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'fd');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'td');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'status_id');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'is_deleted');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'object_id');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'user_id');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'created_at');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'updated_at');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'code');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'name');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'address');
        $this->dropCommentFromColumn('{{%tillypad_service}}', 'phone');
    }
}
