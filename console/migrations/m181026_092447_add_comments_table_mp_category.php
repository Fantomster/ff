<?php

use yii\db\Migration;

class m181026_092447_add_comments_table_mp_category extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `mp_category` comment "Таблица сведений о категориях товаров в Маркет Плейс";');
        $this->addCommentOnColumn('{{%mp_category}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%mp_category}}', 'name','Наименование категории товаров в Маркет Плейс');
        $this->addCommentOnColumn('{{%mp_category}}', 'parent','Идентификатор родительской категории товаров в Маркет Плейс');
        $this->addCommentOnColumn('{{%mp_category}}', 'slug','slug-псевдоним категории товаров в Маркет Плейс');
        $this->addCommentOnColumn('{{%mp_category}}', 'title','Заголовок к категории товаров Маркет Плейс');
        $this->addCommentOnColumn('{{%mp_category}}', 'text','Текст к категории товаров в Маркет Плейс');
        $this->addCommentOnColumn('{{%mp_category}}', 'description','Описание категории товаров в Маркет Плейс');
        $this->addCommentOnColumn('{{%mp_category}}', 'keywords','Ключевые слова категории товаров в Маркет Плейс');
    }

    public function safeDown()
    {
        $this->execute('alter table `mp_category` comment "";');
        $this->dropCommentFromColumn('{{%mp_category}}', 'id');
        $this->dropCommentFromColumn('{{%mp_category}}', 'name');
        $this->dropCommentFromColumn('{{%mp_category}}', 'parent');
        $this->dropCommentFromColumn('{{%mp_category}}', 'slug');
        $this->dropCommentFromColumn('{{%mp_category}}', 'title');
        $this->dropCommentFromColumn('{{%mp_category}}', 'text');
        $this->dropCommentFromColumn('{{%mp_category}}', 'description');
        $this->dropCommentFromColumn('{{%mp_category}}', 'keywords');
    }
}
