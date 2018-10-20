<?php

use yii\db\Migration;

/**
 * Class m181020_133847_delete_waibill_columns
 */
class m181020_133847_delete_waibill_columns extends Migration
{
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
