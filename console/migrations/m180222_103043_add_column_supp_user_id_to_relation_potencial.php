<?php

use yii\db\Migration;

/**
 * Class m180222_103043_add_column_supp_user_id_to_relation_potencial
 */
class m180222_103043_add_column_supp_user_id_to_relation_potencial extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%relation_supp_rest_potential}}', 'supp_user_id', $this->integer()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%relation_supp_rest_potential}}', 'supp_user_id');
    }
}
