<?php

use yii\db\Migration;

class m190215_110322_add_comments_fields_assorti18 extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%rk_service}}', 'code', 'Идентификатор объекта');
        $this->addCommentOnColumn('{{%iiko_product}}', 'is_active', 'Показатель состояния активности товара в системе IIKO (0 - не активен, 1 - активен)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%rk_service}}', 'code');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'is_active');
    }
}
