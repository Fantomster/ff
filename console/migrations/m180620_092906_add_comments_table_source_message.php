<?php

use yii\db\Migration;

class m180620_092906_add_comments_table_source_message extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `source_message` comment "Таблица, содержащая слова и фразы в проекте для переводов на иностранные языки";');
        $this->addCommentOnColumn('{{%source_message}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%source_message}}', 'category', 'Категория слов и фраз в проекте для переводов на иностранные языки');
        $this->addCommentOnColumn('{{%source_message}}', 'message', 'Слово или фраза в проекте (для перевода на иностранные языки)');
    }

    public function safeDown()
    {
        $this->execute('alter table `source_message` comment "";');
        $this->dropCommentFromColumn('{{%source_message}}', 'id');
        $this->dropCommentFromColumn('{{%source_message}}', 'category');
        $this->dropCommentFromColumn('{{%source_message}}', 'message');
    }

}
