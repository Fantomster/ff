<?php

use yii\db\Migration;

/**
 * Class m181020_133847_delete_waibill_columns
 */
class m181020_133847_delete_waibill_columns extends Migration
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
        $this->renameColumn(\common\models\Waybill::tableName(), 'bill_status_id', 'status_id');
        $this->dropColumn(\common\models\Waybill::tableName(), 'order_id');
        $this->dropColumn(\common\models\Waybill::tableName(), 'readytoexport');
        $this->dropColumn(\common\models\Waybill::tableName(), 'is_deleted');
        $this->dropColumn(\common\models\WaybillContent::tableName(), 'unload_status');
        $this->renameColumn(\common\models\WaybillContent::tableName(), 'product_outer_id', 'outer_product_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181020_133847_delete_waibill_columns cannot be reverted.\n";
        return false;
    }
}
