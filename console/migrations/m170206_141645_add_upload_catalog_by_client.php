<?php

use yii\db\Migration;

class m170206_141645_add_upload_catalog_by_client extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%relation_supp_rest}}', 'uploaded_catalog', $this->string()->null());
        $this->addColumn('{{%relation_supp_rest}}', 'uploaded_processed', $this->boolean()->notNull()->defaultValue(true));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%relation_supp_rest}}', 'uploaded_catalog');
        $this->dropColumn('{{%relation_supp_rest}}', 'uploaded_processed');
    }
}
