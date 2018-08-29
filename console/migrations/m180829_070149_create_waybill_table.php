<?php

use yii\db\Migration;
use \yii\db\Schema;

/**
 * Class m180829_070149_add_waybill_table
 */
class m180829_070149_create_waybill_table extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%waybill}}', [
            'id'                => Schema::TYPE_PK,
            'acquirer_id'       => Schema::TYPE_INTEGER . ' NOT NULL',
            'bill_status_id'    => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'readytoexport'     => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'service_id'        => Schema::TYPE_INTEGER . ' NOT NULL',
            'outer_number_code' => Schema::TYPE_STRING . '(45)' . ' NULL',
            'outer_number_additional' => Schema::TYPE_STRING . '(45)' . ' NULL',
            'outer_store_uuid' => Schema::TYPE_STRING . '(36)' . ' NULL',
            'outer_duedate' => Schema::TYPE_DATETIME  . ' NULL',
            'outer_note' => Schema::TYPE_STRING . '(45)' . ' NULL',
            'outer_order_date' => Schema::TYPE_STRING . '(45)' . ' NULL',
            'outer_contractor_uuid' => Schema::TYPE_STRING . '(36)' . ' NULL',
            'vat_included' => Schema::TYPE_INTEGER . ' NULL',
        ], $tableOptions);
    }
    
    public function safeDown()
    {
        $this->dropTable('{{%waybill}}');
    }
}
