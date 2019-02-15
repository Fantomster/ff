<?php

use yii\db\Migration;

class m190214_093459_add_column_is_active_table_rk_product extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addColumn('{{%rk_product}}', 'is_active', $this->integer(1)->null()->defaultValue(1));
        $this->addCommentOnColumn('{{%rk_product}}', 'is_active', 'Показатель состояния активности товара в системе R-Keeper (0 - не активен, 1 - активен)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%rk_product}}', 'is_active');
        $this->dropColumn('{{%rk_product}}', 'is_active');
    }
}
