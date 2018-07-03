<?php

use yii\db\Migration;

/**
 * Class m180702_083520_create_tables_for_one_s_waybill_data
 */
class m180702_083520_create_tables_for_one_s_waybill_data extends Migration
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
        $this->createTable('one_s_waybill_status', [
            'id' => $this->primaryKey(),
            'denom' => $this->string(),
            'comment' => $this->string()->null()
        ]);

        $this->insert('one_s_waybill_status', ['denom' => 'Сформирована']);
        $this->insert('one_s_waybill_status', ['denom' => 'Выгружена']);

        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS one_s_waybill
        (
          id            INT AUTO_INCREMENT
            PRIMARY KEY,
          agent_uuid    VARCHAR(36)                         NULL,
          org           INT                                 NULL,
          order_id      INT                                 NULL,
          num_code      INT                                 NULL,
          readytoexport INT                                 NULL,
          status_id     INT                                 NULL,
          store_id      INT                                 NULL,
          note          VARCHAR(255)                        NULL,
          is_duedate    INT DEFAULT '0'                     NULL,
          active        INT DEFAULT '1'                     NULL,
          vat_included  INT                                 NULL,
          doc_date      TIMESTAMP                           NULL,
          created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
          exported_at   TIMESTAMP                           NULL,
          updated_at    TIMESTAMP                           NULL
        );
        CREATE INDEX one_s_waybill_agent_uuid_index
          ON one_s_waybill (agent_uuid);
        CREATE INDEX one_s_waybill_order_id_index
          ON one_s_waybill (order_id);
        CREATE INDEX one_s_waybill_org_index
          ON one_s_waybill (org);
SQL;
        $this->execute($sql);


        $sql = <<<SQL
        CREATE TABLE one_s_waybill_data
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
        CREATE INDEX one_s_waybill_data_waybill_id_index
          ON one_s_waybill_data (waybill_id);
SQL;
        $this->execute($sql);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('one_s_waybill_status');
        $this->dropTable('one_s_waybill');
        $this->dropTable('one_s_waybill_data');
    }
}
