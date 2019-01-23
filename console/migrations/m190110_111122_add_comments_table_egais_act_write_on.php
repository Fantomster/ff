<?php

use yii\db\Migration;

class m190110_111122_add_comments_table_egais_act_write_on extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `egais_act_write_on` comment "Таблица сведений об актах постановки товаров на баланс в системе ЕГАИС";');
        $this->addCommentOnColumn('{{%egais_act_write_on}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%egais_act_write_on}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%egais_act_write_on}}', 'number', 'Номер акта о постановке на баланс');
        $this->addCommentOnColumn('{{%egais_act_write_on}}', 'act_date', 'Дата акта постановки на баланс');
        $this->addCommentOnColumn('{{%egais_act_write_on}}', 'note', 'Примечание к акту постановки на баланс');
        $this->addCommentOnColumn('{{%egais_act_write_on}}', 'type_charge_on', 'Идентификатор типа операции постановки на баланс');
        $this->addCommentOnColumn('{{%egais_act_write_on}}', 'status', 'Статус акта постановки на баланс (0 - не принят на баланс, 1 - принят на баланс)');
        $this->addCommentOnColumn('{{%egais_act_write_on}}', 'reply_id', 'Уникальный идентификатор акта постановки на баланс в системе ЕГАИС');
        $this->addCommentOnColumn('{{%egais_act_write_on}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%egais_act_write_on}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `egais_act_write_on` comment "";');
        $this->dropCommentFromColumn('{{%egais_act_write_on}}', 'id');
        $this->dropCommentFromColumn('{{%egais_act_write_on}}', 'org_id');
        $this->dropCommentFromColumn('{{%egais_act_write_on}}', 'number');
        $this->dropCommentFromColumn('{{%egais_act_write_on}}', 'act_date');
        $this->dropCommentFromColumn('{{%egais_act_write_on}}', 'note');
        $this->dropCommentFromColumn('{{%egais_act_write_on}}', 'type_charge_on');
        $this->dropCommentFromColumn('{{%egais_act_write_on}}', 'status');
        $this->dropCommentFromColumn('{{%egais_act_write_on}}', 'reply_id');
        $this->dropCommentFromColumn('{{%egais_act_write_on}}', 'created_at');
        $this->dropCommentFromColumn('{{%egais_act_write_on}}', 'updated_at');
    }
}
