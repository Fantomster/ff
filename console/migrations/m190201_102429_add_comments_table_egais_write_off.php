<?php

use yii\db\Migration;

class m190201_102429_add_comments_table_egais_write_off extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `egais_write_off` comment "Таблица сведений о списаниях товаров в системе ЕГАИС";');
        $this->addCommentOnColumn('{{%egais_write_off}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%egais_write_off}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%egais_write_off}}', 'identity', 'Порядковый номер документа в универсальном транспортном модуле ЕГАИС');
        $this->addCommentOnColumn('{{%egais_write_off}}', 'act_number', 'Номер акта о списании товаров');
        $this->addCommentOnColumn('{{%egais_write_off}}', 'act_date', 'Дата акта о списании товаров');
        $this->addCommentOnColumn('{{%egais_write_off}}', 'type_write_off', 'Идентификатор типа списания товаров');
        $this->addCommentOnColumn('{{%egais_write_off}}', 'note', 'Примечание к операции списания товаров');
        $this->addCommentOnColumn('{{%egais_write_off}}', 'status', 'Показатель статуса списания товаров (0 - не списан, 1 - списан)');
        $this->addCommentOnColumn('{{%egais_write_off}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%egais_write_off}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `egais_write_off` comment "";');
        $this->dropCommentFromColumn('{{%egais_write_off}}', 'id');
        $this->dropCommentFromColumn('{{%egais_write_off}}', 'org_id');
        $this->dropCommentFromColumn('{{%egais_write_off}}', 'identity');
        $this->dropCommentFromColumn('{{%egais_write_off}}', 'act_number');
        $this->dropCommentFromColumn('{{%egais_write_off}}', 'act_date');
        $this->dropCommentFromColumn('{{%egais_write_off}}', 'type_write_off');
        $this->dropCommentFromColumn('{{%egais_write_off}}', 'note');
        $this->dropCommentFromColumn('{{%egais_write_off}}', 'status');
        $this->dropCommentFromColumn('{{%egais_write_off}}', 'created_at');
        $this->dropCommentFromColumn('{{%egais_write_off}}', 'updated_at');
    }
}
