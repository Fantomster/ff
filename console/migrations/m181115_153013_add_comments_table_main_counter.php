<?php

use yii\db\Migration;

class m181115_153013_add_comments_table_main_counter extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `main_counter` comment "Таблица сведений о количестве зарегистрированных поставщиков и ресторанов (не используется)";');
        $this->addCommentOnColumn('{{%main_counter}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%main_counter}}', 'rest_count','Количество зарегистрированных в Mixcart ресторанов');
        $this->addCommentOnColumn('{{%main_counter}}', 'supp_count','Количество зарегистрированных в Mixcart поставщиков');
        $this->addCommentOnColumn('{{%main_counter}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%main_counter}}', 'next','Дата и время следующего подсчёта зарегистрированных в системе ресторанов и поставщиков');
    }

    public function safeDown()
    {
        $this->execute('alter table `main_counter` comment "";');
        $this->dropCommentFromColumn('{{%main_counter}}', 'id');
        $this->dropCommentFromColumn('{{%main_counter}}', 'rest_count');
        $this->dropCommentFromColumn('{{%main_counter}}', 'supp_count');
        $this->dropCommentFromColumn('{{%main_counter}}', 'updated_at');
        $this->dropCommentFromColumn('{{%main_counter}}', 'next');
    }
}
