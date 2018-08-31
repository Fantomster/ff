<?php

use yii\db\Migration;

class m180831_080354_add_comments_table_one_s_dicconst extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_dicconst` comment "Таблица сведений о настройках интеграции с 1C";');
        $this->addCommentOnColumn('{{%one_s_dicconst}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_dicconst}}', 'denom', 'Название свойства интеграции с 1C');
        $this->addCommentOnColumn('{{%one_s_dicconst}}', 'def_value', 'Значение по умолчанию свойства интеграции с 1C');
        $this->addCommentOnColumn('{{%one_s_dicconst}}', 'comment', 'Комментарий к свойству интеграции с 1C');
        $this->addCommentOnColumn('{{%one_s_dicconst}}', 'type', 'Тип элемента формы для свойства интеграции с 1C (1 - выпадающий список, 2 - поле ввода, 3 - поле для ввода пароля)');
        $this->addCommentOnColumn('{{%one_s_dicconst}}', 'is_active', 'Показатель состояния активности свойства интеграции с 1C');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_dicconst` comment "";');
        $this->dropCommentFromColumn('{{%one_s_dicconst}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_dicconst}}', 'denom');
        $this->dropCommentFromColumn('{{%one_s_dicconst}}', 'def_value');
        $this->dropCommentFromColumn('{{%one_s_dicconst}}', 'comment');
        $this->dropCommentFromColumn('{{%one_s_dicconst}}', 'type');
        $this->dropCommentFromColumn('{{%one_s_dicconst}}', 'is_active');
    }
}
