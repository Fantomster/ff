<?php

use yii\db\Migration;

/**
 * Class m190121_125658_add_locationProsperity_column
 */
class m190121_125658_add_locationProsperity_column extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addColumn('{{%merc_vsd}}', 'location_prosperity', $this->string(255)
            ->comment('Благополучие местности'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%merc_vsd}}', 'location_prosperity');
    }
}
