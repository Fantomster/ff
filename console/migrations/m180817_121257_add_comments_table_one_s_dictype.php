<?php

use yii\db\Migration;

class m180817_121257_add_comments_table_one_s_dictype extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_dictype` comment "Таблица сведений о приходных накладных в системе 1C";');
        $this->addCommentOnColumn('{{%one_s_dictype}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_dictype}}', 'denom','Наименование справочника в системе 1C');
        $this->addCommentOnColumn('{{%one_s_dictype}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%one_s_dictype}}', 'comment','Комментарий (не используется)');
        $this->addCommentOnColumn('{{%one_s_dictype}}', 'contr','Название контроллера, вызываемого при загрузке справочника');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_dictype` comment "";');
        $this->dropCommentFromColumn('{{%one_s_dictype}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_dictype}}', 'denom');
        $this->dropCommentFromColumn('{{%one_s_dictype}}', 'created_at');
        $this->dropCommentFromColumn('{{%one_s_dictype}}', 'comment');
        $this->dropCommentFromColumn('{{%one_s_dictype}}', 'contr');
    }
}
