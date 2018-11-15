<?php

use yii\db\Migration;

class m181115_155217_add_comments_table_franchise_type extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `franchise_type` comment "Таблица сведений о типах франчайзи";');
        $this->addCommentOnColumn('{{%franchise_type}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%franchise_type}}', 'name','Наименование типа франчайзи');
        $this->addCommentOnColumn('{{%franchise_type}}', 'share','Процент дохода от франшизы');
        $this->addCommentOnColumn('{{%franchise_type}}', 'price','Цена за покупку франшизы');
    }

    public function safeDown()
    {
        $this->execute('alter table `franchise_type` comment "";');
        $this->dropCommentFromColumn('{{%franchise_type}}', 'id');
        $this->dropCommentFromColumn('{{%franchise_type}}', 'name');
        $this->dropCommentFromColumn('{{%franchise_type}}', 'share');
        $this->dropCommentFromColumn('{{%franchise_type}}', 'price');
    }
}