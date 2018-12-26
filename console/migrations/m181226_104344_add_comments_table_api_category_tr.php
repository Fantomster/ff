<?php

use yii\db\Migration;

class m181226_104344_add_comments_table_api_category_tr extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `api_category_tr` comment "Таблица сведений о переводах категорий товаров в API 1-й версии";');
        $this->addCommentOnColumn('{{%api_category_tr}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%api_category_tr}}', 'fid', 'Идентификатор объекта в таблице');
        $this->addCommentOnColumn('{{%api_category_tr}}', 'lang', 'Идентификатор языка перевода');
        $this->addCommentOnColumn('{{%api_category_tr}}', 'denom', 'Наименование категории товаров, переведённое на данный язык');
        $this->addCommentOnColumn('{{%api_category_tr}}', 'comment', 'Комментарий к переводу категории товаров');
    }

    public function safeDown()
    {
        $this->execute('alter table `api_category_tr` comment "";');
        $this->dropCommentFromColumn('{{%api_category_tr}}', 'id');
        $this->dropCommentFromColumn('{{%api_category_tr}}', 'fid');
        $this->dropCommentFromColumn('{{%api_category_tr}}', 'lang');
        $this->dropCommentFromColumn('{{%api_category_tr}}', 'denom');
        $this->dropCommentFromColumn('{{%api_category_tr}}', 'comment');
    }
}
