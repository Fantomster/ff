<?php

use yii\db\Migration;

class m180609_172614_add_comments_table_integration_torg12_columns extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `integration_torg12_columns` comment "Таблица соответствия псевдонимов и списков параметров регулярного поиска при парсинге накладной от поставщика";');
        $this->addCommentOnColumn('{{%integration_torg12_columns}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%integration_torg12_columns}}', 'name', 'Псевдоним регулярного поиска при парсинге накладной от поставщика');
        $this->addCommentOnColumn('{{%integration_torg12_columns}}', 'value', 'Список параметров регулярного поиска при парсинге накладной от поставщика');
        $this->addCommentOnColumn('{{%integration_torg12_columns}}', 'regular_expression', 'Уровень глубины поиска по регулярному выражению при парсинге накладной от поставщика');
    }

    public function safeDown()
    {
        $this->execute('alter table `integration_torg12_columns` comment "";');
        $this->dropCommentFromColumn('{{%integration_torg12_columns}}', 'id');
        $this->dropCommentFromColumn('{{%integration_torg12_columns}}', 'name');
        $this->dropCommentFromColumn('{{%integration_torg12_columns}}', 'value');
        $this->dropCommentFromColumn('{{%integration_torg12_columns}}', 'regular_expression');
    }

}
