<?php

use yii\db\Migration;

/**
 * Class m180614_194452_add_pdenom_to_all_map
 */
class m180614_194452_add_pdenom_to_all_map extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }


    public function safeUp()
    {
        $this->addColumn('all_map', 'pdenom', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('all_map', 'pdenom');
    }

}
