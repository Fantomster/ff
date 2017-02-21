<?php

use yii\db\Migration;

class m170221_115316_white_list_table_rename_to_buisiness_info extends Migration {

    public function safeUp() {
        $this->renameTable('{{%white_list}}', '{{%buisiness_info}}');
        $this->dropColumn('{{%buisiness_info}}', 'partnership');
    }

    public function safeDown() {
        $this->renameTable('{{%buisiness_info}}', '{{%white_list}}');
        $this->addColumn('{{%white_list}}', 'partnership', $this->boolean()->notNull()->defaultValue(0));
    }

}
