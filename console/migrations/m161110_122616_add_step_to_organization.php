<?php

use yii\db\Migration;

class m161110_122616_add_step_to_organization extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'step', $this->integer()->notNull()->defaultValue(1));
        $this->update('{{%organization}}', ['step' => 0]);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'step');
    }
}
