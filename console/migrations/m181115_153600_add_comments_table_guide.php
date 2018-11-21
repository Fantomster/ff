<?php

use yii\db\Migration;

class m181115_153600_add_comments_table_guide extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `guide` comment "Таблица сведений о шаблонах заказов";');
        $this->addCommentOnColumn('{{%guide}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%guide}}', 'client_id','Идентификатор организации-поставщика, которому осуществляется заказ');
        $this->addCommentOnColumn('{{%guide}}', 'type','Тип шаблона заказа (1 - "Часто заказываемые товары", 2 - "Шаблон заказа")');
        $this->addCommentOnColumn('{{%guide}}', 'name','Наименование шаблона заказа');
        $this->addCommentOnColumn('{{%guide}}', 'deleted','Показатель статуса удалёния шаблона заказа (0 - не удалён, 1 - удалён)');
        $this->addCommentOnColumn('{{%guide}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%guide}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%guide}}', 'color','Цвет шаблона заказа в шестнадцатеричном формате');
    }

    public function safeDown()
    {
        $this->execute('alter table `guide` comment "";');
        $this->dropCommentFromColumn('{{%guide}}', 'id');
        $this->dropCommentFromColumn('{{%guide}}', 'client_id');
        $this->dropCommentFromColumn('{{%guide}}', 'type');
        $this->dropCommentFromColumn('{{%guide}}', 'name');
        $this->dropCommentFromColumn('{{%guide}}', 'deleted');
        $this->dropCommentFromColumn('{{%guide}}', 'created_at');
        $this->dropCommentFromColumn('{{%guide}}', 'updated_at');
        $this->dropCommentFromColumn('{{%guide}}', 'color');
    }
}