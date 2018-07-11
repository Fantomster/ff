<?php

use yii\db\Migration;

/**
 * Handles the creation of table `merc_stock_entry`.
 */
class m180706_125413_create_merc_stock_entry_table extends Migration
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
        $this->createTable('{{%merc_stock_entry}}', [
            'id' => $this->primaryKey(),
            'guid' => $this->string(255)->null()->defaultValue(null)->unique(),
            'uuid' => $this->string(255)->null()->defaultValue(null),
            'owner_guid' => $this->string(255)->null()->defaultValue(null),
            'active' => $this->smallInteger(1)->null()->defaultValue(null),
            'last' => $this->smallInteger(1)->null()->defaultValue(null),
            'status' => $this->Integer()->null()->defaultValue(null),
            'create_date' => $this->dateTime()->null()->defaultValue(null),
            'update_date' => $this->dateTime()->null()->defaultValue(null),
            'previous' => $this->string(255)->null()->defaultValue(null),
            'next' => $this->string(255)->null()->defaultValue(null),
            'entryNumber' => $this->string(255)->null()->defaultValue(null),
            'product_type' => $this->smallInteger(2)->null()->defaultValue(null),
            'product_name' => $this->string(255)->null()->defaultValue(null),
            'amount' => $this->decimal(10,3)->null()->defaultValue(null),
            'unit' =>  $this->string(50)->null()->defaultValue(null),
            'gtin' => $this->string(50)->null()->defaultValue(null),
            'article' => $this->string(255)->null()->defaultValue(null),
            'production_date' => $this->string(255)->null()->defaultValue(null),
            'expiry_date' => $this->string(255)->null()->defaultValue(null),
            'batch_id' => $this->string(255)->null()->defaultValue(null),
            'perishable' => $this->smallInteger(1)->null()->defaultValue(null),
            'producer_name' => $this->string(255)->null()->defaultValue(null),
            'producer_guid' => $this->string(255)->null()->defaultValue(null),
            'low_grade_cargo' => $this->smallInteger(1)->null()->defaultValue(null),
            'vsd_uuid' =>  $this->string(255)->null()->defaultValue(null),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('merc_stock_entry');
    }
}
