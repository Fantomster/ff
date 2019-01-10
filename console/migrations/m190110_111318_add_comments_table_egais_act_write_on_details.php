<?php

use yii\db\Migration;

class m190110_111318_add_comments_table_egais_act_write_on_details extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `egais_act_write_on_details` comment "Таблица сведений о товарах в актах постановки товаров на баланс в системе ЕГАИС";');
        $this->addCommentOnColumn('{{%egais_act_write_on_details}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%egais_act_write_on_details}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%egais_act_write_on_details}}', 'act_write_on_id', 'Идентификатор акта постановки товаров на баланс в системе ЕГАИС');
        $this->addCommentOnColumn('{{%egais_act_write_on_details}}', 'act_reg_id', 'Идентификатор отдельного товара, поставленного на баланс');
        $this->addCommentOnColumn('{{%egais_act_write_on_details}}', 'number', 'Номер акта о постановке на баланс');
        $this->addCommentOnColumn('{{%egais_act_write_on_details}}', 'identity', 'Количество товара, поставленного на баланс');
        $this->addCommentOnColumn('{{%egais_act_write_on_details}}', 'in_form_f1_reg_id', 'Идентификатор товара в базе ЕГАИС по форме 1');
        $this->addCommentOnColumn('{{%egais_act_write_on_details}}', 'f2_reg_id', 'Идентификатор товара в базе ЕГАИС по форме 2');
        $this->addCommentOnColumn('{{%egais_act_write_on_details}}', 'status', 'Показатель статуса товара, ставящегося на баланс (0 - не поставлен на баланс, 1 - поставлен на баланс)');
        $this->addCommentOnColumn('{{%egais_act_write_on_details}}', 'created_at', 'Дата и время создания записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `egais_act_write_on_details` comment "";');
        $this->dropCommentFromColumn('{{%egais_act_write_on_details}}', 'id');
        $this->dropCommentFromColumn('{{%egais_act_write_on_details}}', 'org_id');
        $this->dropCommentFromColumn('{{%egais_act_write_on_details}}', 'act_write_on_id');
        $this->dropCommentFromColumn('{{%egais_act_write_on_details}}', 'act_reg_id');
        $this->dropCommentFromColumn('{{%egais_act_write_on_details}}', 'number');
        $this->dropCommentFromColumn('{{%egais_act_write_on_details}}', 'identity');
        $this->dropCommentFromColumn('{{%egais_act_write_on_details}}', 'in_form_f1_reg_id');
        $this->dropCommentFromColumn('{{%egais_act_write_on_details}}', 'f2_reg_id');
        $this->dropCommentFromColumn('{{%egais_act_write_on_details}}', 'status');
        $this->dropCommentFromColumn('{{%egais_act_write_on_details}}', 'created_at');
    }
}
