<?php

use yii\db\Migration;

class m181019_084219_add_column_service_id_table_iiko_waybill extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addColumn('{{%iiko_waybill}}', 'service_id', $this->integer()->defaultValue(2));
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'service_id', 'Идентификатор сервиса интеграции (2 - IIKO, 10 - Tillypad)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'service_id');
        $this->dropColumn('{{%iiko_waybill}}', 'service_id');
    }
}
