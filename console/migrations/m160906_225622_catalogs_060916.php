<?php

use yii\db\Migration;

class m160906_225622_catalogs_060916 extends Migration
{
    public function safeUp()
    {
	    $this->addColumn('{{%relation_supp_rest}}', 'status', $this->integer()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%relation_supp_rest}}', 'status');
    }
}
