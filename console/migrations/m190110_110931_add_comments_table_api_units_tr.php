<?php

use yii\db\Migration;

class m190110_110931_add_comments_table_api_units_tr extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `api_units_tr` comment "Таблица сведений о переводах наименований единиц измерения товаров в API 1-й версии";');
        $this->addCommentOnColumn('{{%api_units_tr}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%api_units_tr}}', 'fid', 'Идентификатор объекта в таблице');
        $this->addCommentOnColumn('{{%api_units_tr}}', 'lang', 'Идентификатор языка перевода');
        $this->addCommentOnColumn('{{%api_units_tr}}', 'denom', 'Наименование единицы измерения товаров, переведённое на данный язык');
        $this->addCommentOnColumn('{{%api_units_tr}}', 'comment', 'Комментарий к переводу единицы измерения товаров');
    }

    public function safeDown()
    {
        $this->execute('alter table `api_units_tr` comment "";');
        $this->dropCommentFromColumn('{{%api_units_tr}}', 'id');
        $this->dropCommentFromColumn('{{%api_units_tr}}', 'fid');
        $this->dropCommentFromColumn('{{%api_units_tr}}', 'lang');
        $this->dropCommentFromColumn('{{%api_units_tr}}', 'denom');
        $this->dropCommentFromColumn('{{%api_units_tr}}', 'comment');
    }
}
