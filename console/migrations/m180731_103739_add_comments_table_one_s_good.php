<?php

use yii\db\Migration;

class m180731_103739_add_comments_table_one_s_good extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_good` comment "Таблица сведений о товарах в системе 1C";');
        $this->addCommentOnColumn('{{%one_s_good}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_good}}', 'name','Наименование товара');
        $this->addCommentOnColumn('{{%one_s_good}}', 'cid','Внутренний идентификатор товара в системе 1С');
        $this->addCommentOnColumn('{{%one_s_good}}', 'parent_id','Идентификатор родительского товара в системем 1С');
        $this->addCommentOnColumn('{{%one_s_good}}', 'org_id','Идентификатор организации');
        $this->addCommentOnColumn('{{%one_s_good}}', 'measure','Единица измерения товара');
        $this->addCommentOnColumn('{{%one_s_good}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%one_s_good}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%one_s_good}}', 'is_category','Показатель типа товара (0 - товар, 1  - папка)');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_good` comment "";');
        $this->dropCommentFromColumn('{{%one_s_good}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_good}}', 'name');
        $this->dropCommentFromColumn('{{%one_s_good}}', 'cid');
        $this->dropCommentFromColumn('{{%one_s_good}}', 'parent_id');
        $this->dropCommentFromColumn('{{%one_s_good}}', 'org_id');
        $this->dropCommentFromColumn('{{%one_s_good}}', 'measure');
        $this->dropCommentFromColumn('{{%one_s_good}}', 'created_at');
        $this->dropCommentFromColumn('{{%one_s_good}}', 'updated_at');
        $this->dropCommentFromColumn('{{%one_s_good}}', 'is_category');
    }
}
