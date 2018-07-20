<?php

use yii\db\Migration;

class m180703_084919_add_comments_table_iiko_dicstatus extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    public function safeUp()
    {
        $this->execute('alter table `iiko_dicstatus` comment "Таблица сведений о названиях статусов запроса на закачку справочников в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_dicstatus}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_dicstatus}}', 'denom', 'Название статуса запроса на закачку справочников в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_dicstatus}}', 'comment', 'Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_dicstatus` comment "";');
        $this->addCommentOnColumn('{{%iiko_dicstatus}}', 'id', '');
        $this->addCommentOnColumn('{{%iiko_dicstatus}}', 'denom', '');
        $this->addCommentOnColumn('{{%iiko_dicstatus}}', 'comment', '');
    }

}
