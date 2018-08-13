<?php

use yii\db\Migration;

class m180731_104034_add_comments_table_one_s_store extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_store` comment "Таблица сведений о складах в системе 1С";');
        $this->addCommentOnColumn('{{%one_s_store}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_store}}', 'cid','Внутренний идентификатор склада в системе 1С');
        $this->addCommentOnColumn('{{%one_s_store}}', 'name','Наименование склада');
        $this->addCommentOnColumn('{{%one_s_store}}', 'address','Адрес склада');
        $this->addCommentOnColumn('{{%one_s_store}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%one_s_store}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%one_s_store}}', 'org_id','Идентификатор организации');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_store` comment "";');
        $this->dropCommentFromColumn('{{%one_s_store}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_store}}', 'cid');
        $this->dropCommentFromColumn('{{%one_s_store}}', 'name');
        $this->dropCommentFromColumn('{{%one_s_store}}', 'address');
        $this->dropCommentFromColumn('{{%one_s_store}}', 'created_at');
        $this->dropCommentFromColumn('{{%one_s_store}}', 'updated_at');
        $this->dropCommentFromColumn('{{%one_s_store}}', 'org_id');
    }
}
