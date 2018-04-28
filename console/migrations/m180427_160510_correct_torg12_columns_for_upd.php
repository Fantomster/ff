<?php

use yii\db\Migration;

/**
 * Class m180427_160510_correct_torg12_columns_for_upd
 */
class m180427_160510_correct_torg12_columns_for_upd extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("update integration_torg12_columns set value = 'сумма.*с.*ндс.*|стоимость.*товаров.*с налогом.*всего' where name = 'sum_with_tax';");
        $this->execute("update integration_torg12_columns set value = 'сумма.*без.*ндс.*|стоимость.*товаров.*без налога.*всего' where name = 'sum_without_tax';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180427_160510_correct_torg12_columns_for_upd cannot be reverted. But it is OK\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180427_160510_correct_torg12_columns_for_upd cannot be reverted.\n";

        return false;
    }
    */
}
