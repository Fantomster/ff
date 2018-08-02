<?php

use yii\db\Migration;

class m180731_104927_add_comments_table_one_s_contragent extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_contragent` comment "Таблица сведений о контрагентах в системе 1С";');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'cid','Внутренний идентификатор контрагента в системе 1С');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'name','Наименование контрагента в системе 1С');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'inn_kpp','ИНН и КПП контрагента в системе 1С');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'org_id','Идентификатор организации');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_contragent` comment "";');
        $this->dropCommentFromColumn('{{%one_s_contragent}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_contragent}}', 'cid');
        $this->dropCommentFromColumn('{{%one_s_contragent}}', 'name');
        $this->dropCommentFromColumn('{{%one_s_contragent}}', 'inn_kpp');
        $this->dropCommentFromColumn('{{%one_s_contragent}}', 'created_at');
        $this->dropCommentFromColumn('{{%one_s_contragent}}', 'updated_at');
        $this->dropCommentFromColumn('{{%one_s_contragent}}', 'org_id');
    }
}
