<?php

use yii\db\Migration;

/**
 * Class m181015_091522_add_indexses_to_merc_log
 */
class m181015_091522_add_indexses_to_merc_log extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('merc_log');
        $this->execute("ALTER TABLE `api`.`merc_log` 
                            ADD INDEX `ix_merc_log_id` (`id` ASC),
                            ADD INDEX `ix_merc_log_action` (`action` ASC),
                            ADD INDEX `ix_merc_log_localTransaction` (`localTransactionId` ASC),
                            ADD INDEX `ix_merc_log_user_id` (`user_id` ASC),
                            ADD INDEX `ix_merc_log_org_id` (`organization_id` ASC);");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181015_091522_add_indexses_to_merc_log cannot be reverted.\n";

        return false;
    }
}
