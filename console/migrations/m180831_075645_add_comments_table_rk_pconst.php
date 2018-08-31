<?php

use yii\db\Migration;

class m180831_075645_add_comments_table_rk_pconst extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_pconst` comment "Таблица сведений о значениях настроек интеграции ресторанов в системе R-Keeper";');
        $this->addCommentOnColumn('{{%rk_pconst}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_pconst}}', 'const_id', 'Идентификатор настройки интеграции');
        $this->addCommentOnColumn('{{%rk_pconst}}', 'org', 'Идентификатор организации-ресторана');
        $this->addCommentOnColumn('{{%rk_pconst}}', 'value', 'Значение настройки интеграции');
        $this->addCommentOnColumn('{{%rk_pconst}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_pconst}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_pconst` comment "";');
        $this->dropCommentFromColumn('{{%rk_pconst}}', 'id');
        $this->dropCommentFromColumn('{{%rk_pconst}}', 'const_id');
        $this->dropCommentFromColumn('{{%rk_pconst}}', 'org');
        $this->dropCommentFromColumn('{{%rk_pconst}}', 'value');
        $this->dropCommentFromColumn('{{%rk_pconst}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_pconst}}', 'updated_at');
    }
}
