<?php

use yii\db\Migration;

class m181115_161324_add_comments_fields_assorti10 extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%rk_waybill}}', 'is_duedate', 'Показатель состояния просроченности (не используется)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'is_duedate');
    }
}