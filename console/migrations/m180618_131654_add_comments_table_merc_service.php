<?php

use yii\db\Migration;

class m180618_131654_add_comments_table_merc_service extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `merc_service` comment "Таблица сведений о лицензиях ВЕТИС Меркурий";');
        $this->addCommentOnColumn('{{%merc_service}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%merc_service}}', 'org', 'Идентификатор организации, использующей лицензию ВЕТИС Меркурий');
        $this->addCommentOnColumn('{{%merc_service}}', 'fd', 'Дата и время начала действия лицензии ВЕТИС Меркурий');
        $this->addCommentOnColumn('{{%merc_service}}', 'td', 'Дата и время окончания действия лицензии ВЕТИС Меркурий');
        $this->addCommentOnColumn('{{%merc_service}}', 'status_id', 'Показатель статуса лицензии ВЕТИС Меркурий (1 - активна, 0 - не активна)');
        $this->addCommentOnColumn('{{%merc_service}}', 'is_deleted', 'Показатель удаления лицензии ВЕТИС Меркурий (1 - не удалена, 0 - удалена)');
        $this->addCommentOnColumn('{{%merc_service}}', 'object_id', 'Идентификатор ресторана (аппаратный ключ ВЕТИС Меркурий - не используется)');
        $this->addCommentOnColumn('{{%merc_service}}', 'user_id', 'Идентификатор пользователя, оформившего лицензию ВЕТИС Меркурий');
        $this->addCommentOnColumn('{{%merc_service}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%merc_service}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%merc_service}}', 'code', 'ID объекта - не используется');
        $this->addCommentOnColumn('{{%merc_service}}', 'name', 'Наименование организации, использующей лицензию ВЕТИС Меркурий');
        $this->addCommentOnColumn('{{%merc_service}}', 'address', 'Адрес организации, использующей лицензию ВЕТИС Меркурий');
        $this->addCommentOnColumn('{{%merc_service}}', 'phone', 'Номер телефона организации, использующей лицензию ВЕТИС Меркурий');

    }

    public function safeDown()
    {
        $this->execute('alter table `merc_service` comment "";');
        $this->dropCommentFromColumn('{{%merc_service}}', 'id');
        $this->dropCommentFromColumn('{{%merc_service}}', 'org');
        $this->dropCommentFromColumn('{{%merc_service}}', 'fd');
        $this->dropCommentFromColumn('{{%merc_service}}', 'td');
        $this->dropCommentFromColumn('{{%merc_service}}', 'status_id');
        $this->dropCommentFromColumn('{{%merc_service}}', 'is_deleted');
        $this->dropCommentFromColumn('{{%merc_service}}', 'object_id');
        $this->dropCommentFromColumn('{{%merc_service}}', 'user_id');
        $this->dropCommentFromColumn('{{%merc_service}}', 'created_at');
        $this->dropCommentFromColumn('{{%merc_service}}', 'updated_at');
        $this->dropCommentFromColumn('{{%merc_service}}', 'code');
        $this->dropCommentFromColumn('{{%merc_service}}', 'name');
        $this->dropCommentFromColumn('{{%merc_service}}', 'address');
        $this->dropCommentFromColumn('{{%merc_service}}', 'phone');
    }

}
