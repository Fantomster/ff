<?php

use yii\db\Migration;

class m170305_095052_alter_reward_in_buisiness_info_table extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%buisiness_info}}', 'reward', $this->decimal(10,2)->null()->defaultValue(0.0));
    }

    public function safeDown()
    {
        $this->alterColumn('{{%buisiness_info}}', 'reward', $this->decimal(10,2)->notNull()->defaultValue(0.0));
    }
}
