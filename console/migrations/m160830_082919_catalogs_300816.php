<?php

use yii\db\Migration;

class m160830_082919_catalogs_300816 extends Migration
{
    public function safeUp()
    {
	    $this->addColumn('{{%catalog_base_goods}}', 'status', $this->integer()->defaultValue(0));
        
        $this->dropColumn('{{%relation_category}}', 'relation_supp_rest_id');
        $this->addColumn('{{%relation_category}}', 'relation_rest_org_id', $this->integer()->defaultValue(0));
        $this->addColumn('{{%relation_category}}', 'relation_supp_org_id', $this->integer()->defaultValue(0));
        
        $this->addColumn('{{%relation_supp_rest}}', 'status', $this->integer()->defaultValue(0));
        $this->addColumn('{{%relation_supp_rest}}', 'invite', $this->integer()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%catalog_base_goods}}', 'status');
        $this->addColumn('{{%relation_category}}', 'relation_supp_rest_id', $this->integer()->null());
        $this->dropColumn('{{%relation_category}}', 'relation_rest_org_id');
        $this->dropColumn('{{%relation_category}}', 'relation_supp_org_id');
        
        $this->dropColumn('{{%relation_supp_rest}}', 'status');
        $this->dropColumn('{{%relation_supp_rest}}', 'invite');
    }
}
