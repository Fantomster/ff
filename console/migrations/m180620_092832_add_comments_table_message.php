<?php

use yii\db\Migration;

class m180620_092832_add_comments_table_message extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `message` comment "Таблица, содержащая переводы слов и фраз в проекте на иностранные языки";');
        $this->addCommentOnColumn('{{%message}}', 'id', 'Идентификатор записи в таблице source_message (внешний ключ)');
        $this->addCommentOnColumn('{{%message}}', 'language', 'Двухбуквенное обозначение языка');
        $this->addCommentOnColumn('{{%message}}', 'translation', 'Перевод слова или фразы в проекте на иностранный язык');
    }

    public function safeDown()
    {
        $this->execute('alter table `message` comment "";');
        $this->dropCommentFromColumn('{{%message}}', 'id');
        $this->dropCommentFromColumn('{{%message}}', 'language');
        $this->dropCommentFromColumn('{{%message}}', 'translation');
    }

}
