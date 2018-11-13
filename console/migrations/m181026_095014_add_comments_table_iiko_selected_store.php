<?php

use yii\db\Migration;

class m181026_095014_add_comments_table_iiko_selected_store extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_selected_store` comment "Таблица сведений о доступных складах в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_selected_store}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_selected_store}}', 'store_id', 'Идентификатор склада');
        $this->addCommentOnColumn('{{%iiko_selected_store}}', 'organization_id', 'Идентификатор организации, для которой доступен склад');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_selected_store` comment "";');
        $this->dropCommentFromColumn('{{%iiko_selected_store}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_selected_store}}', 'store_id');
        $this->dropCommentFromColumn('{{%iiko_selected_store}}', 'organization_id');
    }
}
