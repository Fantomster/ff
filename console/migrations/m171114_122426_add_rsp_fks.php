<?php

use yii\db\Migration;

class m171114_122426_add_rsp_fks extends Migration
{
    public function safeUp()
    {
        $this->addForeignKey('{{%fk_rsr_client}}', '{{%relation_supp_rest}}', 'rest_org_id', '{{%organization}}', 'id');
        $this->addForeignKey('{{%fk_rsr_vendor}}', '{{%relation_supp_rest}}', 'supp_org_id', '{{%organization}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_rsr_client}}', '{{%relation_supp_rest}}');
        $this->dropForeignKey('{{%fk_rsr_vendor}}', '{{%relation_supp_rest}}');
    }
}
