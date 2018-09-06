<?php

use yii\db\Migration;

class m180831_080837_add_comments_table_one_s_service extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_service` comment "Таблица сведений о лицензиях 1C";');
        $this->addCommentOnColumn('{{%one_s_service}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_service}}', 'org','Идентификатор организации, использующей лицензию 1C');
        $this->addCommentOnColumn('{{%one_s_service}}', 'fd','Дата и время начала действия лицензии 1C');
        $this->addCommentOnColumn('{{%one_s_service}}', 'td','Дата и время окончания действия лицензии 1C');
        $this->addCommentOnColumn('{{%one_s_service}}', 'status_id','Показатель статуса лицензии 1C (1 - активна, 0 - не активна)');
        $this->addCommentOnColumn('{{%one_s_service}}', 'is_deleted','Показатель удаления лицензии 1C (1 - не удалена, 0 - удалена)');
        $this->addCommentOnColumn('{{%one_s_service}}', 'object_id','Идентификатор ресторана (аппаратный ключ 1C - не используется)');
        $this->addCommentOnColumn('{{%one_s_service}}', 'user_id','Идентификатор пользователя, оформившего лицензию 1C');
        $this->addCommentOnColumn('{{%one_s_service}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%one_s_service}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%one_s_service}}', 'code','ID объекта - не используется');
        $this->addCommentOnColumn('{{%one_s_service}}', 'name','Наименование организации, использующей лицензию 1C');
        $this->addCommentOnColumn('{{%one_s_service}}', 'address','Адрес организации, использующей лицензию 1C');
        $this->addCommentOnColumn('{{%one_s_service}}', 'phone','Номер телефона организации, использующей лицензию 1C');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_service` comment "";');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'org');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'fd');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'td');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'status_id');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'is_deleted');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'object_id');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'user_id');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'created_at');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'updated_at');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'code');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'name');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'address');
        $this->dropCommentFromColumn('{{%one_s_service}}', 'phone');
    }
}
