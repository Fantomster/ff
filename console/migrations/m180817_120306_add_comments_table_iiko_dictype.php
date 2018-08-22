<?php

use yii\db\Migration;

class m180817_120306_add_comments_table_iiko_dictype extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_dictype` comment "Таблица сведений о приходных накладных в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_dictype}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_dictype}}', 'denom','Наименование справочника в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_dictype}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%iiko_dictype}}', 'comment','Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_dictype` comment "";');
        $this->dropCommentFromColumn('{{%iiko_dictype}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_dictype}}', 'denom');
        $this->dropCommentFromColumn('{{%iiko_dictype}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_dictype}}', 'comment');
    }
}