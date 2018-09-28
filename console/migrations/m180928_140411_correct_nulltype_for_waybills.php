<?php

use yii\db\Migration;

/**
 * Class m180928_140411_correct_nulltype_for_waybills
 */
class m180928_140411_correct_nulltype_for_waybills extends Migration
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
        $this->execute('ALTER TABLE iiko_waybill MODIFY COLUMN num_code varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL NULL COMMENT "Номер документа по приходной накладной";');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180928_140411_correct_nulltype_for_waybills cannot be reverted. But it is OK as we don't need to push NULL back again\n";
        return true;
    }

}
