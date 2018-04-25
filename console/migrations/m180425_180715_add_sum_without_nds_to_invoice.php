<?php

use yii\db\Migration;

/**
 * Class m180425_180715_add_sum_without_nds_to_invoice
 */
class m180425_180715_add_sum_without_nds_to_invoice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("insert into integration_torg12_columns (name,value,regular_expression) values ('sum_without_tax','сумма.*без.*ндс.*',1);");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180425_180715_add_sum_without_nds_to_invoice cannot be reverted. But it is OK\n";

        return true;
    }


}
