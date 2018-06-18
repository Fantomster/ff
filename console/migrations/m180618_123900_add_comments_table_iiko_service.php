<?php

use yii\db\Migration;

class m180618_123900_add_comments_table_iiko_service extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_service` comment "Таблица сведений о лицензиях IIKO";');
        $this->addCommentOnColumn('{{%iiko_service}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_service}}', 'org', 'Идентификатор организации, использующей лицензию IIKO');
        $this->addCommentOnColumn('{{%iiko_service}}', 'fd', 'Дата и время начала действия лицензии IIKO');
        $this->addCommentOnColumn('{{%iiko_service}}', 'td', 'Дата и время окончания действия лицензии IIKO');
        $this->addCommentOnColumn('{{%iiko_service}}', 'status_id', 'Показатель статуса лицензии IIKO (1 - активна, 0 - не активна)');
        $this->addCommentOnColumn('{{%iiko_service}}', 'is_deleted', 'Показатель удаления лицензии IIKO (1 - не удалена, 0 - удалена)');
        $this->addCommentOnColumn('{{%iiko_service}}', 'object_id', 'Идентификатор ресторана (аппаратный ключ IIKO - не используется)');
        $this->addCommentOnColumn('{{%iiko_service}}', 'user_id', 'Идентификатор пользователя, оформившего лицензию IIKO');
        $this->addCommentOnColumn('{{%iiko_service}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%iiko_service}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%iiko_service}}', 'code', 'ID объекта - не используется');
        $this->addCommentOnColumn('{{%iiko_service}}', 'name', 'Наименование организации, использующей лицензию IIKO');
        $this->addCommentOnColumn('{{%iiko_service}}', 'address', 'Адрес организации, использующей лицензию IIKO');
        $this->addCommentOnColumn('{{%iiko_service}}', 'phone', 'Номер телефона организации, использующей лицензию IIKO');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_service` comment "";');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'org');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'fd');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'td');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'status_id');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'is_deleted');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'object_id');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'user_id');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'updated_at');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'code');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'name');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'address');
        $this->dropCommentFromColumn('{{%iiko_service}}', 'phone');
    }

}
