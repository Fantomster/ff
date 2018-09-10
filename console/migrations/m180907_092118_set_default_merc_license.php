<?php

use yii\db\Migration;

/**
 * Class m180907_092118_set_default_merc_license
 */
class m180907_092118_set_default_merc_license extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("UPDATE `merc_service` SET `code` = '1'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("UPDATE `merc_service` SET `code` = null");

        return false;
    }

}
