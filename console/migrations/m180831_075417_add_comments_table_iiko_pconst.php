<?php

use yii\db\Migration;

class m180831_075417_add_comments_table_iiko_pconst extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_pconst` comment "Таблица сведений о значениях настроек интеграции ресторанов в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_pconst}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_pconst}}', 'const_id', 'Идентификатор настройки интеграции');
        $this->addCommentOnColumn('{{%iiko_pconst}}', 'org', 'Идентификатор организации-ресторана');
        $this->addCommentOnColumn('{{%iiko_pconst}}', 'value', 'Значение настройки интеграции');
        $this->addCommentOnColumn('{{%iiko_pconst}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%iiko_pconst}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_pconst` comment "";');
        $this->dropCommentFromColumn('{{%iiko_pconst}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_pconst}}', 'const_id');
        $this->dropCommentFromColumn('{{%iiko_pconst}}', 'org');
        $this->dropCommentFromColumn('{{%iiko_pconst}}', 'value');
        $this->dropCommentFromColumn('{{%iiko_pconst}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_pconst}}', 'updated_at');
    }
}
