<?php

use yii\db\Migration;

class m160909_151655_catalogs_090916 extends Migration
{
    public function safeUp()
    {
	$this->addColumn('{{%catalog_goods}}', 'discount_percent', $this->integer()->defaultValue(0));
        $this->addColumn('{{%catalog_goods}}', 'discount_fixed', $this->integer()->defaultValue(0));
    }
    public function safeDown()
    {
        $this->dropColumn('{{%catalog_goods}}', 'discount_percent');
        $this->dropColumn('{{%catalog_goods}}', 'discount_fixed');
    }
}
