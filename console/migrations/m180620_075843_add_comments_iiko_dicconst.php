<?php

use yii\db\Migration;

class m180620_075843_add_comments_iiko_dicconst extends Migration
{
    public function init()
    {
        $this->execute('alter table `iiko_dicconst` comment "Таблица сведений о настройках интеграции с IIKO Office";');
        $this->addCommentOnColumn('{{%iiko_dicconst}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_dicconst}}', 'denom', 'Название свойства интеграции с IIKO Office');
        $this->addCommentOnColumn('{{%iiko_dicconst}}', 'def_value', 'Значение по умолчанию свойства интеграции с IIKO Office');
        $this->addCommentOnColumn('{{%iiko_dicconst}}', 'comment', 'Комментарий к свойству интеграции с IIKO Office');
        $this->addCommentOnColumn('{{%iiko_dicconst}}', 'type', 'Тип элемента формы для свойства интеграции с IIKO Office (1 - выпадающий список, 2 - поле ввода, 3 - поле для ввода пароля)');
        $this->addCommentOnColumn('{{%iiko_dicconst}}', 'is_active', 'Показатель состояния активности свойства интеграции с IIKO Office');
    }

    public function safeUp()
    {

    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_dicconst` comment "";');
        $this->dropCommentFromColumn('{{%iiko_dicconst}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_dicconst}}', 'denom');
        $this->dropCommentFromColumn('{{%iiko_dicconst}}', 'def_value');
        $this->dropCommentFromColumn('{{%iiko_dicconst}}', 'comment');
        $this->dropCommentFromColumn('{{%iiko_dicconst}}', 'type');
        $this->dropCommentFromColumn('{{%iiko_dicconst}}', 'is_active');
    }

}
