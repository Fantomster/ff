<?php

use yii\db\Migration;

class m181012_074952_add_comments_table_catalog extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `catalog` comment "Таблица сведений о каталогах товаров поставщиков";');
        $this->addCommentOnColumn('{{%catalog}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%catalog}}', 'type','Тип каталога товаров (0 - не каталог, 1 - базовый каталог, 2 - индивидуальный каталог)');
        $this->addCommentOnColumn('{{%catalog}}', 'supp_org_id','Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%catalog}}', 'name','Наименование каталога товаров поставщика');
        $this->addCommentOnColumn('{{%catalog}}', 'status','Статус каталога товаров (0 - не действующий, 1 - действующий)');
        $this->addCommentOnColumn('{{%catalog}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%catalog}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%catalog}}', 'currency_id','Идентификатор валюты');
        $this->addCommentOnColumn('{{%catalog}}', 'main_index','Имя индекса для полнотекстового поиска');
        $this->addCommentOnColumn('{{%catalog}}', 'mapping','Альтернативные варианты названия при полнотекстовом поиске');
        $this->addCommentOnColumn('{{%catalog}}', 'index_column','Идентификатор поля для индексации при полнотекстовом поиске');
    }

    public function safeDown()
    {
        $this->execute('alter table `catalog` comment "";');
        $this->dropCommentFromColumn('{{%catalog}}', 'id');
        $this->dropCommentFromColumn('{{%catalog}}', 'type');
        $this->dropCommentFromColumn('{{%catalog}}', 'supp_org_id');
        $this->dropCommentFromColumn('{{%catalog}}', 'name');
        $this->dropCommentFromColumn('{{%catalog}}', 'status');
        $this->dropCommentFromColumn('{{%catalog}}', 'created_at');
        $this->dropCommentFromColumn('{{%catalog}}', 'updated_at');
        $this->dropCommentFromColumn('{{%catalog}}', 'currency_id');
        $this->dropCommentFromColumn('{{%catalog}}', 'main_index');
        $this->dropCommentFromColumn('{{%catalog}}', 'mapping');
        $this->dropCommentFromColumn('{{%catalog}}', 'index_column');
    }
}
