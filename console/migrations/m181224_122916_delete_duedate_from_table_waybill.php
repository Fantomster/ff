<?php

use yii\db\Migration;

class m181224_122916_delete_duedate_from_table_waybill extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->dropColumn('{{%waybill}}', 'outer_duedate');
        $this->dropColumn('{{%waybill}}', 'is_duedate');
    }

    public function safeDown()
    {
        $this->addColumn('{{%waybill}}', 'outer_duedate', $this->datetime()->defaultValue(null));
        $this->addColumn('{{%waybill}}', 'is_duedate', $this->tinyInteger(3)->defaultValue(null));
        $this->addCommentOnColumn('{{%waybill}}', 'outer_duedate', 'Не используется');
        $this->addCommentOnColumn('{{%waybill}}', 'is_duedate', 'Показатель состояния просроченности, не используется');
    }
}
