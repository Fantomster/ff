<?php

use yii\db\Migration;

class m180613_153913_add_comments_table_rk_service extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_service` comment "Таблица сведений об услугах Mixcart интеграции c UCS в системе R-keeper";');
        $this->addCommentOnColumn('{{%rk_service}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_service}}', 'org', 'Идентификатор организации, использующей лицензию UCS в системе R-keeper (использовался ранее, сейчас не используется)');
        $this->addCommentOnColumn('{{%rk_service}}', 'fd', 'Дата и время начала действия лицензии UCS в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_service}}', 'td', 'Дата и время окончания действия лицензии UCS в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_service}}', 'status_id', 'Показатель статуса лицензии UCS в системе R-keeper (1 - активна, 0 - не активна)');
        $this->addCommentOnColumn('{{%rk_service}}', 'is_deleted', 'Показатель удаления лицензии UCS в системе R-keeper (1 - не удалена, 0 - удалена)');
        $this->addCommentOnColumn('{{%rk_service}}', 'object_id', 'Идентификатор ресторана (аппаратный ключ UCS)');
        $this->addCommentOnColumn('{{%rk_service}}', 'user_id', 'Идентификатор пользователя, оформившего лицензию UCS в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_service}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_service}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_service}}', 'code', 'ID объекта');
        $this->addCommentOnColumn('{{%rk_service}}', 'name', 'Наименование организации, использующей лицензию UCS в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_service}}', 'address', 'Адрес организации, использующей лицензию UCS в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_service}}', 'phone', 'Номер телефона организации, использующей лицензию UCS в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_service}}', 'last_active', 'Дата и время последней активности по данной лицензии UCS в системе R-keeper');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_service` comment "";');
        $this->dropCommentFromColumn('{{%rk_service}}', 'id');
        $this->dropCommentFromColumn('{{%rk_service}}', 'org');
        $this->dropCommentFromColumn('{{%rk_service}}', 'fd');
        $this->dropCommentFromColumn('{{%rk_service}}', 'td');
        $this->dropCommentFromColumn('{{%rk_service}}', 'status_id');
        $this->dropCommentFromColumn('{{%rk_service}}', 'is_deleted');
        $this->dropCommentFromColumn('{{%rk_service}}', 'object_id');
        $this->dropCommentFromColumn('{{%rk_service}}', 'user_id');
        $this->dropCommentFromColumn('{{%rk_service}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_service}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_service}}', 'code');
        $this->dropCommentFromColumn('{{%rk_service}}', 'name');
        $this->dropCommentFromColumn('{{%rk_service}}', 'address');
        $this->dropCommentFromColumn('{{%rk_service}}', 'phone');
        $this->dropCommentFromColumn('{{%rk_service}}', 'last_active');
    }

}
