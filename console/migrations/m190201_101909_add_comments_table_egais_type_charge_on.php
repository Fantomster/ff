<?php

use yii\db\Migration;

class m190201_101909_add_comments_table_egais_type_charge_on extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `egais_type_charge_on` comment "Таблица сведений о типах операции постановки на баланс в системе ЕГАИС";');
        $this->addCommentOnColumn('{{%egais_type_charge_on}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%egais_type_charge_on}}', 'type', 'Тип операции постановки на баланс');
    }

    public function safeDown()
    {
        $this->execute('alter table `egais_type_charge_on` comment "";');
        $this->dropCommentFromColumn('{{%egais_type_charge_on}}', 'id');
        $this->dropCommentFromColumn('{{%egais_type_charge_on}}', 'type');
    }
}
