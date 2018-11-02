<?php

use yii\db\Migration;

class m181102_140558_alter_column_active_table_rk_waybill extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->alterColumn("{{%rk_waybill}}", 'active', 'INTEGER(11) NULL DEFAULT 1');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'active', 'Показатель состояния активности приходной накладной (0 - не активна, 1 - активна)');
    }

    public function safeDown()
    {
        $this->alterColumn("{{%rk_waybill}}", 'active', 'INTEGER(11) NULL DEFAULT NULL');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'active', 'Показатель состояния активности приходной накладной (0 - не активна, 1 - активна)');
    }
}
