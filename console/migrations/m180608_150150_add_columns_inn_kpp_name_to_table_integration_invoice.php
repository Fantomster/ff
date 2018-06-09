<?php

use yii\db\Migration;


class m180608_150150_add_columns_inn_kpp_name_to_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%integration_invoice}}', 'name_postav', $this->string()->null()->defaultValue(null));
        $this->addColumn('{{%integration_invoice}}', 'inn_postav', $this->string()->null()->defaultValue(null));
        $this->addColumn('{{%integration_invoice}}', 'kpp_postav', $this->string()->null()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%integration_invoice}}', 'name_postav');
        $this->dropColumn('{{%integration_invoice}}', 'inn_postav');
        $this->dropColumn('{{%integration_invoice}}', 'kpp_postav');
    }

}
