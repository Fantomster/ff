<?php

use yii\db\Migration;

class m181130_122104_add_comments_table_category extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `category` comment "Таблица сведений о категориях товаров";');
        $this->addCommentOnColumn('{{%category}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%category}}', 'name','Наименование категории товаров');
    }

    public function safeDown()
    {
        $this->execute('alter table `category` comment "";');
        $this->dropCommentFromColumn('{{%category}}', 'id');
        $this->dropCommentFromColumn('{{%category}}', 'name');
    }
}
