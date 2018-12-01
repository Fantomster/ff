<?php

use yii\db\Migration;

class m181130_122244_add_comments_table_catalog_temp extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `catalog_temp` comment "Таблица сведений о временных каталогах товаров поставщиков";');
        $this->addCommentOnColumn('{{%catalog_temp}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%catalog_temp}}', 'cat_id','Идентификатор каталога');
        $this->addCommentOnColumn('{{%catalog_temp}}', 'user_id','Идентификатор пользователя, соаздавшего временный каталог');
        $this->addCommentOnColumn('{{%catalog_temp}}', 'excel_file','Наименование файла Excel, содержащего временный каталог товаров');
        $this->addCommentOnColumn('{{%catalog_temp}}', 'mapping','Номера столбцов в файле и атрибуты товаров, которые в этих столбцах содержатся');
        $this->addCommentOnColumn('{{%catalog_temp}}', 'index_column','Наименование столбца в файле, по которому индексируется временный каталог');
        $this->addCommentOnColumn('{{%catalog_temp}}', 'created_at','Дата и время создания записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `catalog_temp` comment "";');
        $this->dropCommentFromColumn('{{%catalog_temp}}', 'id');
        $this->dropCommentFromColumn('{{%catalog_temp}}', 'cat_id');
        $this->dropCommentFromColumn('{{%catalog_temp}}', 'user_id');
        $this->dropCommentFromColumn('{{%catalog_temp}}', 'excel_file');
        $this->dropCommentFromColumn('{{%catalog_temp}}', 'mapping');
        $this->dropCommentFromColumn('{{%catalog_temp}}', 'index_column');
        $this->dropCommentFromColumn('{{%catalog_temp}}', 'created_at');
    }
}
