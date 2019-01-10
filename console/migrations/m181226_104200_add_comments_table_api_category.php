<?php

use yii\db\Migration;

class m181226_104200_add_comments_table_api_category extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `api_category` comment "Таблица сведений о категориях товаров в API 1-й версии";');
        $this->addCommentOnColumn('{{%api_category}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%api_category}}', 'fid', 'Идентификатор объекта в таблице');
        $this->addCommentOnColumn('{{%api_category}}', 'denom', 'Наименование категории товаров');
        $this->addCommentOnColumn('{{%api_category}}', 'fd', 'Дата и время начала действия категории товаров');
        $this->addCommentOnColumn('{{%api_category}}', 'td', 'Дата и время окончания действия категории товаров');
        $this->addCommentOnColumn('{{%api_category}}', 'ver', 'Версия реализации');
        $this->addCommentOnColumn('{{%api_category}}', 'up', 'Идентификатор родительской категории товаров');
        $this->addCommentOnColumn('{{%api_category}}', 'comment', 'Комментарий к категории товаров');
    }

    public function safeDown()
    {
        $this->execute('alter table `api_category` comment "";');
        $this->dropCommentFromColumn('{{%api_category}}', 'id');
        $this->dropCommentFromColumn('{{%api_category}}', 'fid');
        $this->dropCommentFromColumn('{{%api_category}}', 'denom');
        $this->dropCommentFromColumn('{{%api_category}}', 'fd');
        $this->dropCommentFromColumn('{{%api_category}}', 'td');
        $this->dropCommentFromColumn('{{%api_category}}', 'ver');
        $this->dropCommentFromColumn('{{%api_category}}', 'up');
        $this->dropCommentFromColumn('{{%api_category}}', 'comment');
    }
}
