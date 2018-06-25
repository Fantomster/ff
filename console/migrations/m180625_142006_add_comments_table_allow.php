<?php

use yii\db\Migration;

class m180625_142006_add_comments_table_allow extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `allow` comment "Таблица наименований состояния согласий на определённые действия";');
        $this->addCommentOnColumn('{{%allow}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%allow}}', 'name_allow', 'Наименование состояния согласия на определённые действия');
    }

    public function safeDown()
    {
        $this->execute('alter table `allow` comment "";');
        $this->dropCommentFromColumn('{{%allow}}', 'id');
        $this->dropCommentFromColumn('{{%allow}}', 'name_allow');
    }

}