<?php

use yii\db\Migration;

/**
 * Class m180614_194854_add_munit_to_all_map
 */
class m180614_194854_add_munit_to_all_map extends Migration
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
        $this->addColumn('all_map', 'munit_rid', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('all_map', 'munit_rid');
    }
}
