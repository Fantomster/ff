<?php

use yii\db\Migration;
use \yii\db\Schema;

/**
 * Class m180829_070159_add_waybill_content_table
 */
class m180829_070159_create_waybill_content_table extends Migration
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
        
        $this->createTable('{{%waybill_content}}', [
            'id'               => Schema::TYPE_PK,
            'waybill_id'       => Schema::TYPE_INTEGER . ' NOT NULL',
            'order_content_id' => Schema::TYPE_INTEGER . ' NULL',
            'product_outer_id' => Schema::TYPE_INTEGER . ' NULL',
            'quantity_waybill' => Schema::TYPE_FLOAT . ' NULL',
            'price_waybill'    => Schema::TYPE_FLOAT . ' NULL',
            'vat_waybill'      => Schema::TYPE_FLOAT . ' NULL',
            'merc_uuid'        => Schema::TYPE_STRING . ' NULL',
            'unload_status'    => Schema::TYPE_TINYINT . ' NOT NULL DEFAULT 1',
        ], $tableOptions);
        
        // creates index for column `waybill_id`, maybe need for 'order_content_id' column?
        $this->createIndex(
            'idx-waybill_content-waybill_id',
            '{{%waybill_content}}',
            'waybill_id'
        );
        
        // add foreign key for table `waybill`
        $this->addForeignKey(
            '{{%fk-waybill_content-waybill_id}}',
            '{{%waybill_content}}',
            'waybill_id',
            '{{%waybill}}',
            'id',
            'CASCADE'
        );
        
    }
    
    public function safeDown()
    {
        // drops foreign key for table `waybill`
        $this->dropForeignKey(
            '{{%fk-waybill_content-waybill_id}}',
            '{{%waybill_content}}'
        );
        
        // drops index for column `author_id`
        $this->dropIndex(
            'idx-waybill_content-waybill_id',
            '{{%waybill_content}}'
        );
        $this->dropTable('{{%waybill_content}}');
    }
}
