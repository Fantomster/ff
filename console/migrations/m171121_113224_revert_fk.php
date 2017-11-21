<?php

use yii\db\Migration;

/**
 * Class m171121_113224_revert_fk
 */
class m171121_113224_revert_fk extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('{{%fk_cg_cat}}', '{{%catalog_goods}}');
    }

    public function safeDown()
    {
        $this->addForeignKey('{{%fk_cg_cat}}', '{{%catalog_goods}}', 'cat_id', '{{%catalog}}', 'id');
    }
}
