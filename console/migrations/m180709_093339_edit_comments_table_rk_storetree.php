<?php

use yii\db\Migration;

class m180709_093339_edit_comments_table_rk_storetree extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%rk_storetree}}', 'type', 'Тип элемента (1 - простой элемент, 2 - папка с элементами)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'type');
    }
}
