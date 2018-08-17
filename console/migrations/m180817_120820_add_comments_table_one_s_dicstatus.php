<?php

use yii\db\Migration;

class m180817_120820_add_comments_table_one_s_dicstatus extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_dicstatus` comment "Таблица сведений о названиях статусов запроса на закачку справочников в системе 1C";');
        $this->addCommentOnColumn('{{%one_s_dicstatus}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_dicstatus}}', 'denom', 'Название статуса запроса на закачку справочников в системе 1C');
        $this->addCommentOnColumn('{{%one_s_dicstatus}}', 'comment', 'Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_dicstatus` comment "";');
        $this->dropCommentFromColumn('{{%one_s_dicstatus}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_dicstatus}}', 'denom');
        $this->dropCommentFromColumn('{{%one_s_dicstatus}}', 'comment');
    }
}
