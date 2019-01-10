<?php

use yii\db\Migration;

/**
 * Class m181108_130108_change_type_columns_producer_in_mercvsd
 */
class m181108_130108_change_type_columns_producer_in_mercvsd extends Migration
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
        $dbName = \common\helpers\DBNameHelper::getApiName();
        $this->execute("ALTER TABLE $dbName.`merc_vsd` 
            CHANGE COLUMN `producer_name` `producer_name` TEXT NULL DEFAULT NULL COMMENT 'Наименование производителя' ,
            CHANGE COLUMN `producer_guid` `producer_guid` TEXT NULL DEFAULT NULL COMMENT 'Глобальный идентификатор предприятия' ;
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
