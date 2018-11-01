<?php

use yii\db\Migration;

/**
 * Class m181101_111533_update_column_batch_id_in_merc_vsd
 */
class m181101_111533_update_column_batch_id_in_merc_vsd extends Migration
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
            $this->execute("ALTER TABLE `merc_vsd` 
            CHANGE COLUMN `batch_id` `batch_id` TEXT NULL DEFAULT NULL COMMENT 'Уникальный идентификатор производственной партии продукции' ;
            ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181101_111533_update_column_batch_id_in_merc_vsd cannot be reverted.\n";

        return false;
    }
}
