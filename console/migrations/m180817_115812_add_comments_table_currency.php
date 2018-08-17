<?php

use yii\db\Migration;

class m180817_115812_add_comments_table_currency extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `currency` comment "Таблица сведений о валютах";');
        $this->addCommentOnColumn('{{%currency}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%currency}}', 'text','Наименование валюты');
        $this->addCommentOnColumn('{{%currency}}', 'symbol','Трёхбуквенный код валюты');
        $this->addCommentOnColumn('{{%currency}}', 'num_code','Цифровой код валюты');
        $this->addCommentOnColumn('{{%currency}}', 'iso_code','ISO-код валюты');
        $this->addCommentOnColumn('{{%currency}}', 'signs','Количество знаков(разрядов) после запятой');
        $this->addCommentOnColumn('{{%currency}}', 'is_active','Показатель состояния активности валюты в системе (0 - не активна, 1 - активна)');
        $this->addCommentOnColumn('{{%currency}}', 'old_symbol','Прежнее односимвольное обозначение валюты');
    }

    public function safeDown()
    {
        $this->execute('alter table `currency` comment "";');
        $this->dropCommentFromColumn('{{%currency}}', 'id');
        $this->dropCommentFromColumn('{{%currency}}', 'text');
        $this->dropCommentFromColumn('{{%currency}}', 'symbol');
        $this->dropCommentFromColumn('{{%currency}}', 'num_code');
        $this->dropCommentFromColumn('{{%currency}}', 'iso_code');
        $this->dropCommentFromColumn('{{%currency}}', 'signs');
        $this->dropCommentFromColumn('{{%currency}}', 'is_active');
        $this->dropCommentFromColumn('{{%currency}}', 'old_symbol');
    }
}
