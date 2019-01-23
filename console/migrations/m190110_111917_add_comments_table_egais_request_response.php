<?php

use yii\db\Migration;

class m190110_111917_add_comments_table_egais_request_response extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `egais_request_response` comment "Таблица сведений об операциях с документами в системе ЕГАИС ";');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'act_id', 'Идентификатор акта постановки товаров на баланс');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'doc_id', 'Идентификатор документа в системе ЕГАИС');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'operation_name', 'Наименование операции с документами в системе ГАИС');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'result', 'Показатель успешности операции с документами в системе ЕГАИС (0 - не успешно, 1 - успешно)');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'conclusion', 'Результат операции с документом в системе ЕГАИС');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'date', 'Дата документа в системе ЕГАИС');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'comment', 'Комментарий к документу в системе ЕГАИС');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%egais_request_response}}', 'doc_type', 'Тип документа в системе ЕГАИС');
    }

    public function safeDown()
    {
        $this->execute('alter table `egais_request_response` comment "";');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'id');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'org_id');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'act_id');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'doc_id');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'operation_name');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'result');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'conclusion');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'date');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'comment');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'created_at');
        $this->dropCommentFromColumn('{{%egais_request_response}}', 'doc_type');
    }
}
