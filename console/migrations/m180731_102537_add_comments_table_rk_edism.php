<?php

use yii\db\Migration;

class m180731_102537_add_comments_table_rk_edism extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_edism` comment "Таблица сведений о единицах измерения в системе R-Keeper";');
        $this->addCommentOnColumn('{{%rk_edism}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_edism}}', 'acc', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%rk_edism}}', 'rid', 'Идентификатор единицы измерения в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_edism}}', 'denom', 'Наименование единицы измерения в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_edism}}', 'ratio', 'Коэффициент пересчёта к базовой единице в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_edism}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_edism}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_edism}}', 'comment', 'Комментарий');
        $this->addCommentOnColumn('{{%rk_edism}}', 'group_rid', 'Идентификатор группы единиц измерения в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_edism}}', 'group_name', 'Наименование группы единиц измерения в системе R-keeper');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_edism` comment "";');
        $this->dropCommentFromColumn('{{%rk_edism}}', 'id');
        $this->dropCommentFromColumn('{{%rk_edism}}', 'acc');
        $this->dropCommentFromColumn('{{%rk_edism}}', 'rid');
        $this->dropCommentFromColumn('{{%rk_edism}}', 'denom');
        $this->dropCommentFromColumn('{{%rk_edism}}', 'ratio');
        $this->dropCommentFromColumn('{{%rk_edism}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_edism}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_edism}}', 'comment');
        $this->dropCommentFromColumn('{{%rk_edism}}', 'group_rid');
        $this->dropCommentFromColumn('{{%rk_edism}}', 'group_name');
    }
}
