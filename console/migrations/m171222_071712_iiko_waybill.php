<?php

use yii\db\Migration;

/**
 * Class m171222_071712_iiko_waybill
 */
class m171222_071712_iiko_waybill extends Migration
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
        CREATE TABLE IF NOT EXISTS iiko_waybill
        (
          id            INT AUTO_INCREMENT
            PRIMARY KEY,
          agent_uuid    VARCHAR(36)                         NULL,
          org           INT                                 NULL,
          order_id      INT                                 NULL,
          num_code      INT                                 NULL,
          text_code     VARCHAR(128)                        NULL,
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
        CREATE INDEX iiko_waybill_agent_uuid_index
          ON iiko_waybill (agent_uuid);
        CREATE INDEX iiko_waybill_order_id_index
          ON iiko_waybill (order_id);
        CREATE INDEX iiko_waybill_org_index
          ON iiko_waybill (org);
SQL;
        $this->execute($sql);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('iiko_waybill');
    }
}
