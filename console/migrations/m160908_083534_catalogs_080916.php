<?php

use yii\db\Migration;

class m160908_083534_catalogs_080916 extends Migration
{
    public function safeUp()
    {
	$this->addColumn('{{%catalog_base_goods}}', 'supp_org_id', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%catalog_base_goods}}', 'supp_org_id');
    }
}
