<?php

use yii\db\Migration;

class m180919_090324_add_comments_table_rk_units extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_units` comment "Таблица сведений об единицах измерений в системе R-Keeper";');
        $this->addCommentOnColumn('{{%rk_units}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_units}}', 'denom', 'Наименование единицы измерения');
        $this->addCommentOnColumn('{{%rk_units}}', 'comment', 'Комментарий');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_units` comment "";');
        $this->dropCommentFromColumn('{{%rk_units}}', 'id');
        $this->dropCommentFromColumn('{{%rk_units}}', 'denom');
        $this->dropCommentFromColumn('{{%rk_units}}', 'comment');
    }
}
