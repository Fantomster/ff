<?php

use yii\db\Migration;

class m181130_122708_add_comments_table_catalog_snapshot extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `catalog_snapshot` comment "Таблица сведений о резервных копиях каталогов товаров поставщиков";');
        $this->addCommentOnColumn('{{%catalog_snapshot}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%catalog_snapshot}}', 'cat_id','Идентификатор каталога, чья копия резервируется');
        $this->addCommentOnColumn('{{%catalog_snapshot}}', 'main_index','Наименование поля, по которому индексируется резервная копия каталога');
        $this->addCommentOnColumn('{{%catalog_snapshot}}', 'currency_id','Идентификатор валюты');
        $this->addCommentOnColumn('{{%catalog_snapshot}}', 'created_at','Дата и время создания записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `catalog_snapshot` comment "";');
        $this->dropCommentFromColumn('{{%catalog_snapshot}}', 'id');
        $this->dropCommentFromColumn('{{%catalog_snapshot}}', 'cat_id');
        $this->dropCommentFromColumn('{{%catalog_snapshot}}', 'main_index');
        $this->dropCommentFromColumn('{{%catalog_snapshot}}', 'currency_id');
        $this->dropCommentFromColumn('{{%catalog_snapshot}}', 'created_at');
    }
}
