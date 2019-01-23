<?php

use yii\db\Migration;

/**
 * Class m190121_091857_add_r13nClause_column
 */
class m190121_091857_add_r13nClause_column extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addColumn('{{%merc_vsd}}', 'r13nClause', $this->integer(11)
            ->defaultValue(0)
            ->comment('Подтверждение соблюдаемых условий перемещения продукции'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%merc_vsd}}', 'r13nClause');
    }
}
