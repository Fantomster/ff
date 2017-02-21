<?php

use yii\db\Migration;

class m170221_152105_add_reward_to_buisiness_info_table extends Migration {

    public function safeUp() {
        $this->addColumn('{{%buisiness_info}}', 'reward', $this->decimal(10,2)->notNull()->defaultValue(0.0));
    }

    public function safeDown() {
        $this->dropColumn('{{%buisiness_info}}', 'reward');
    }

}
