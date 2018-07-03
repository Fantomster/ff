<?php

use yii\db\Migration;

class m180703_084304_add_comments_table_rk_dicconst extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_dicconst` comment "Таблица сведений о настройках интеграции с R-keeper";');
        $this->addCommentOnColumn('{{%rk_dicconst}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_dicconst}}', 'denom', 'Название свойства интеграции с R-keeper');
        $this->addCommentOnColumn('{{%rk_dicconst}}', 'def_value', 'Значение по умолчанию свойства интеграции с R-keeper');
        $this->addCommentOnColumn('{{%rk_dicconst}}', 'comment', 'Комментарий к свойству интеграции с R-keeper');
        $this->addCommentOnColumn('{{%rk_dicconst}}', 'type', 'Тип элемента формы для свойства интеграции с R-keeper (1 - выпадающий список, 2 - поле ввода, 3 - поле для ввода пароля)');
        $this->addCommentOnColumn('{{%rk_dicconst}}', 'is_active', 'Показатель состояния активности свойства интеграции с R-keeper');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_dicconst` comment "";');
        $this->dropCommentFromColumn('{{%rk_dicconst}}', 'id');
        $this->dropCommentFromColumn('{{%rk_dicconst}}', 'denom');
        $this->dropCommentFromColumn('{{%rk_dicconst}}', 'def_value');
        $this->dropCommentFromColumn('{{%rk_dicconst}}', 'comment');
        $this->dropCommentFromColumn('{{%rk_dicconst}}', 'type');
        $this->dropCommentFromColumn('{{%rk_dicconst}}', 'is_active');
    }

}
