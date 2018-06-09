<?php

use yii\db\Migration;

class m180609_161812_add_comments_table_ooo extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `ooo` comment "Таблица соответствия кратких и полных названий форм собственности организаций";');
        $this->addCommentOnColumn('{{%ooo}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%ooo}}', 'name_short', 'Краткое название форм собственности организаций');
        $this->addCommentOnColumn('{{%ooo}}', 'name_long', 'Полное название форм собственности организаций');
    }

    public function safeDown()
    {
        $this->execute('alter table `ooo` comment "";');
        $this->dropCommentFromColumn('{{%ooo}}', 'id');
        $this->dropCommentFromColumn('{{%ooo}}', 'name_short');
        $this->dropCommentFromColumn('{{%ooo}}', 'name_long');
    }
}
