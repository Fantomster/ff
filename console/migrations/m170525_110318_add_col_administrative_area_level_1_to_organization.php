<?php

use yii\db\Migration;

class m170525_110318_add_col_administrative_area_level_1_to_organization extends Migration
{
    public function safeUp() {
        $this->addColumn('{{%organization}}', 'administrative_area_level_1',  $this->string()->null());
    }

    public function safeDown() {
        $this->dropColumn('{{%organization}}', 'administrative_area_level_1');
    }
}
