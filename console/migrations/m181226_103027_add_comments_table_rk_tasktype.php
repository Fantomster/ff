<?php

use yii\db\Migration;

class m181226_103027_add_comments_table_rk_tasktype extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_tasktype` comment "Таблица сведений о типах действий с системой R-Keeper";');
        $this->addCommentOnColumn('{{%rk_tasktype}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_tasktype}}', 'code', 'Код типа действия с системой R-Keeper');
        $this->addCommentOnColumn('{{%rk_tasktype}}', 'denom', 'Наименование типа действия с системой R-Keeper');
        $this->addCommentOnColumn('{{%rk_tasktype}}', 'comment', 'Комментарий к типу действия с системой R-Keeper');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_tasktype` comment "";');
        $this->dropCommentFromColumn('{{%rk_tasktype}}', 'id');
        $this->dropCommentFromColumn('{{%rk_tasktype}}', 'code');
        $this->dropCommentFromColumn('{{%rk_tasktype}}', 'denom');
        $this->dropCommentFromColumn('{{%rk_tasktype}}', 'comment');
    }
}
