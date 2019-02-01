<?php

use yii\db\Migration;

class m190201_102213_add_comments_table_egais_type_write_off extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `egais_type_write_off` comment "Таблица сведений о типах операций списания товаров";');
        $this->addCommentOnColumn('{{%egais_type_write_off}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%egais_type_write_off}}', 'type', 'Тип операции списания товаров');
    }

    public function safeDown()
    {
        $this->execute('alter table `egais_type_write_off` comment "";');
        $this->dropCommentFromColumn('{{%egais_type_write_off}}', 'id');
        $this->dropCommentFromColumn('{{%egais_type_write_off}}', 'type');
    }
}
