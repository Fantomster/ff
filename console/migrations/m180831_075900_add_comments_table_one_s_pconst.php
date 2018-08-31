<?php

use yii\db\Migration;

class m180831_075900_add_comments_table_one_s_pconst extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_pconst` comment "Таблица сведений о значениях настроек интеграции ресторанов в системе 1С";');
        $this->addCommentOnColumn('{{%one_s_pconst}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_pconst}}', 'const_id', 'Идентификатор настройки интеграции');
        $this->addCommentOnColumn('{{%one_s_pconst}}', 'org', 'Идентификатор организации-ресторана');
        $this->addCommentOnColumn('{{%one_s_pconst}}', 'value', 'Значение настройки интеграции');
        $this->addCommentOnColumn('{{%one_s_pconst}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%one_s_pconst}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_pconst` comment "";');
        $this->dropCommentFromColumn('{{%one_s_pconst}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_pconst}}', 'const_id');
        $this->dropCommentFromColumn('{{%one_s_pconst}}', 'org');
        $this->dropCommentFromColumn('{{%one_s_pconst}}', 'value');
        $this->dropCommentFromColumn('{{%one_s_pconst}}', 'created_at');
        $this->dropCommentFromColumn('{{%one_s_pconst}}', 'updated_at');
    }
}
