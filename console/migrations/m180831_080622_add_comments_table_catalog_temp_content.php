<?php

use yii\db\Migration;

class m180831_080622_add_comments_table_catalog_temp_content extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `catalog_temp_content` comment "Таблица сведений о позициях временных каталогов";');
        $this->addCommentOnColumn('{{%catalog_temp_content}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%catalog_temp_content}}', 'temp_id','Идентификатор временного каталога');
        $this->addCommentOnColumn('{{%catalog_temp_content}}', 'article','Артикул товара');
        $this->addCommentOnColumn('{{%catalog_temp_content}}', 'product','Наименование товара');
        $this->addCommentOnColumn('{{%catalog_temp_content}}', 'price','Цена товара');
        $this->addCommentOnColumn('{{%catalog_temp_content}}', 'units','Идентификатор единицы измерения товара');
        $this->addCommentOnColumn('{{%catalog_temp_content}}', 'note','Примечание');
        $this->addCommentOnColumn('{{%catalog_temp_content}}', 'ed','Наименование единицы измерения товара');
        $this->addCommentOnColumn('{{%catalog_temp_content}}', 'other','Другое');
    }

    public function safeDown()
    {
        $this->execute('alter table `catalog_temp_content` comment "";');
        $this->dropCommentFromColumn('{{%catalog_temp_content}}', 'id');
        $this->dropCommentFromColumn('{{%catalog_temp_content}}', 'temp_id');
        $this->dropCommentFromColumn('{{%catalog_temp_content}}', 'article');
        $this->dropCommentFromColumn('{{%catalog_temp_content}}', 'product');
        $this->dropCommentFromColumn('{{%catalog_temp_content}}', 'price');
        $this->dropCommentFromColumn('{{%catalog_temp_content}}', 'units');
        $this->dropCommentFromColumn('{{%catalog_temp_content}}', 'note');
        $this->dropCommentFromColumn('{{%catalog_temp_content}}', 'ed');
        $this->dropCommentFromColumn('{{%catalog_temp_content}}', 'other');
    }
}
