<?php

use yii\db\Migration;

class m190201_103753_add_comments_table_license extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `license` comment "Таблица сведений о видах лицензий интеграций";');
        $this->addCommentOnColumn('{{%license}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%license}}', 'name', 'Наименование вида лицензии');
        $this->addCommentOnColumn('{{%license}}', 'is_active', 'Показатель активности вида лицензии (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%license}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%license}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%license}}', 'login_allowed', 'Показатель достаточности данной лицензии для входа в систему (0 - не достаточно, 1 - достаточно)');
        $this->addCommentOnColumn('{{%license}}', 'service_id', 'Идентификатор учётного сервиса интеграции');
        $this->addCommentOnColumn('{{%license}}', 'sort_index', 'Индекс сортировки');
    }

    public function safeDown()
    {
        $this->execute('alter table `license` comment "";');
        $this->dropCommentFromColumn('{{%license}}', 'id');
        $this->dropCommentFromColumn('{{%license}}', 'name');
        $this->dropCommentFromColumn('{{%license}}', 'is_active');
        $this->dropCommentFromColumn('{{%license}}', 'created_at');
        $this->dropCommentFromColumn('{{%license}}', 'updated_at');
        $this->dropCommentFromColumn('{{%license}}', 'login_allowed');
        $this->dropCommentFromColumn('{{%license}}', 'service_id');
        $this->dropCommentFromColumn('{{%license}}', 'sort_index');
    }
}
