<?php

use yii\db\Migration;

class m190110_110019_add_comments_table_api_lang extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `api_lang` comment "Таблица сведений об языках переводов в API 1-й версии";');
        $this->addCommentOnColumn('{{%api_lang}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%api_lang}}', 'eng_denom', 'Наименование языка на английском языке');
        $this->addCommentOnColumn('{{%api_lang}}', 'denom', 'Наименование языка на русском языке');
        $this->addCommentOnColumn('{{%api_lang}}', 'code2', 'Двухбуквенное обозначение языка');
        $this->addCommentOnColumn('{{%api_lang}}', 'code3', 'Трёхбуквенное обозначение языка');
        $this->addCommentOnColumn('{{%api_lang}}', 'codenum', 'Числовое обозначение языка');
        $this->addCommentOnColumn('{{%api_lang}}', 'comment', 'Комментарий к языку');
    }

    public function safeDown()
    {
        $this->execute('alter table `api_lang` comment "";');
        $this->dropCommentFromColumn('{{%api_lang}}', 'id');
        $this->dropCommentFromColumn('{{%api_lang}}', 'eng_denom');
        $this->dropCommentFromColumn('{{%api_lang}}', 'denom');
        $this->dropCommentFromColumn('{{%api_lang}}', 'code2');
        $this->dropCommentFromColumn('{{%api_lang}}', 'code3');
        $this->dropCommentFromColumn('{{%api_lang}}', 'codenum');
        $this->dropCommentFromColumn('{{%api_lang}}', 'comment');
    }
}
