<?php

use yii\db\Migration;

class m170524_121044_alter_tbl_franchisee_geo_add_col_status extends Migration
{
    public function safeUp() {
        $this->addColumn('{{%franchisee_geo}}', 'status', $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown() {
        $this->dropColumn('{{%franchisee_geo}}', 'status');
    }

}
