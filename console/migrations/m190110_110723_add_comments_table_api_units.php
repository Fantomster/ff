<?php

use yii\db\Migration;

class m190110_110723_add_comments_table_api_units extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `api_units` comment "Таблица сведений об единицах измерения в API 1-й версии";');
        $this->addCommentOnColumn('{{%api_units}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%api_units}}', 'fid', 'Идентификатор объекта в таблице');
        $this->addCommentOnColumn('{{%api_units}}', 'denom', 'Наименование единицы измерения товаров');
        $this->addCommentOnColumn('{{%api_units}}', 'fd', 'Дата и время начала действия единицы измерения товаров');
        $this->addCommentOnColumn('{{%api_units}}', 'td', 'Дата и время окончания действия единицы измерения товаров');
        $this->addCommentOnColumn('{{%api_units}}', 'ver', 'Версия реализации');
        $this->addCommentOnColumn('{{%api_units}}', 'comment', 'Комментарий к единице измерения товаров');
    }

    public function safeDown()
    {
        $this->execute('alter table `api_units` comment "";');
        $this->dropCommentFromColumn('{{%api_units}}', 'id');
        $this->dropCommentFromColumn('{{%api_units}}', 'fid');
        $this->dropCommentFromColumn('{{%api_units}}', 'denom');
        $this->dropCommentFromColumn('{{%api_units}}', 'fd');
        $this->dropCommentFromColumn('{{%api_units}}', 'td');
        $this->dropCommentFromColumn('{{%api_units}}', 'ver');
        $this->dropCommentFromColumn('{{%api_units}}', 'comment');
    }
}
