<?php

use yii\db\Migration;

class m190201_103959_add_comments_table_license_organization extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `license_organization` comment "Таблица сведений об лицензиях организаций в сервисах интеграций";');
        $this->addCommentOnColumn('{{%license_organization}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%license_organization}}', 'license_id', 'Идентификатор вида лицензии');
        $this->addCommentOnColumn('{{%license_organization}}', 'org_id', 'Идентификатор организации, использующей лицензию');
        $this->addCommentOnColumn('{{%license_organization}}', 'fd', 'Дата и время начала действия лицензии');
        $this->addCommentOnColumn('{{%license_organization}}', 'td', 'Дата и время окончания действия лицензии');
        $this->addCommentOnColumn('{{%license_organization}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%license_organization}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%license_organization}}', 'object_id', 'Идентификатор объекта-ресторана в учётной системе');
        $this->addCommentOnColumn('{{%license_organization}}', 'status_id', 'Показатель статуса лицензии (0 - не активна, 1 - активна)');
        $this->addCommentOnColumn('{{%license_organization}}', 'is_deleted', 'Показатель удаления лицензии (0 - не удалена, 1 - удалена)');
        $this->addCommentOnColumn('{{%license_organization}}', 'price', 'Стоимость лицензии');
    }

    public function safeDown()
    {
        $this->execute('alter table `license_organization` comment "";');
        $this->dropCommentFromColumn('{{%license_organization}}', 'id');
        $this->dropCommentFromColumn('{{%license_organization}}', 'license_id');
        $this->dropCommentFromColumn('{{%license_organization}}', 'org_id');
        $this->dropCommentFromColumn('{{%license_organization}}', 'fd');
        $this->dropCommentFromColumn('{{%license_organization}}', 'td');
        $this->dropCommentFromColumn('{{%license_organization}}', 'created_at');
        $this->dropCommentFromColumn('{{%license_organization}}', 'updated_at');
        $this->dropCommentFromColumn('{{%license_organization}}', 'object_id');
        $this->dropCommentFromColumn('{{%license_organization}}', 'status_id');
        $this->dropCommentFromColumn('{{%license_organization}}', 'is_deleted');
        $this->dropCommentFromColumn('{{%license_organization}}', 'price');
    }
}
