<?php

use yii\db\Migration;

/**
 * Class m180201_150350_field_type_for_waybill_data
 */
class m180201_150350_field_type_for_waybill_data extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE `rk_waybill_data` CHANGE COLUMN `quant` `quant` DOUBLE(12,3) DEFAULT NULL');
        $this->execute('ALTER TABLE `rk_waybill_data` CHANGE COLUMN `defquant` `defquant` DOUBLE(12,3) DEFAULT NULL');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180201_150350_field_type_for_waybill_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180201_150350_field_type_for_waybill_data cannot be reverted.\n";

        return false;
    }
    */
}
