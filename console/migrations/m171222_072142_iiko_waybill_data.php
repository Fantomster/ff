<?php

use yii\db\Migration;

/**
 * Class m171222_072142_iiko_waybill_data
 */
class m171222_072142_iiko_waybill_data extends Migration
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
        $sql = <<<SQL
        CREATE TABLE iiko_waybill_data
        (
          id           INT AUTO_INCREMENT
            PRIMARY KEY,
          waybill_id   INT                                 NOT NULL,
          product_id   INT                                 NULL,
          product_rid  INT                                 NULL,
          munit        VARCHAR(10)                         NULL,
          org          INT                                 NULL,
          vat          INT                                 NULL,
          vat_included INT                                 NULL,
          sum          DOUBLE                              NULL,
          quant        DOUBLE                              NULL,
          defsum       DOUBLE                              NULL,
          defquant     DOUBLE                              NULL,
          koef         DOUBLE                              NULL,
          created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
          updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL
        );
        CREATE INDEX iiko_waybill_data_waybill_id_index
          ON iiko_waybill_data (waybill_id);
SQL;
        $this->execute($sql);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('iiko_waybill_data');
    }
}
