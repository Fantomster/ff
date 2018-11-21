<?php

use yii\db\Migration;

/**
 * Class m181121_090506_add_index_document_list
 */
class m181121_090506_add_index_document_list extends Migration
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
        $this->createIndex('idx_oav_org_id', \common\models\OuterAgent::tableName(), ['org_id']);
        $this->createIndex('idx_oav_service_id', \common\models\OuterAgent::tableName(), ['service_id']);
        $this->createIndex('idx_oav_org_id_service_id', \common\models\OuterAgent::tableName(), ['org_id', 'service_id']);
        $this->createIndex('idx_waybill_acquirer_id', \common\models\Waybill::tableName(), ['acquirer_id']);
        $this->createIndex('idx_waybill_service_id', \common\models\Waybill::tableName(), ['service_id']);
        $this->createIndex('idx_waybill_acquirer_id_service_id', \common\models\Waybill::tableName(), ['acquirer_id', 'service_id']);
        $this->createIndex('idx_waybill_acquirer_id_service_id_id', \common\models\Waybill::tableName(), ['acquirer_id', 'service_id', 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_oav_org_id', \common\models\OuterAgent::tableName());
        $this->dropIndex('idx_oav_service_id', \common\models\OuterAgent::tableName());
        $this->dropIndex('idx_oav_org_id_service_id', \common\models\OuterAgent::tableName());
        $this->dropIndex('idx_waybill_acquirer_id', \common\models\Waybill::tableName());
        $this->dropIndex('idx_waybill_service_id', \common\models\Waybill::tableName());
        $this->dropIndex('idx_waybill_acquirer_id_service_id', \common\models\Waybill::tableName());
        $this->dropIndex('idx_waybill_acquirer_id_service_id_id', \common\models\Waybill::tableName());
    }
}
