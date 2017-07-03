<?php

use yii\db\Migration;

class m170523_131643_alter_tbl_franchisee_geo extends Migration
{
    public function safeUp() {
        $this->alterColumn('{{%franchisee_geo}}', 'belongs_to', $this->boolean()->notNull()->defaultValue(0));
        $this->renameColumn('{{%franchisee_geo}}', 'belongs_to', 'exception');
        $this->renameColumn('{{%franchisee_geo}}', 'city', 'locality');
        $this->addColumn('{{%franchisee_geo}}', 'administrative_area_level_1',  $this->string()->null());
    }

    public function safeDown() {
        $this->renameColumn('{{%franchisee_geo}}', 'exception', 'belongs_to');
        $this->renameColumn('{{%franchisee_geo}}', 'locality', 'city');
        $this->dropColumn('{{%franchisee_geo}}', 'administrative_area_level_1');
    }
}
