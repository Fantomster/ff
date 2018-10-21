<?php

use yii\db\Migration;

/**
 * Class m181019_103016_float_fields
 */
class m181019_103016_float_fields extends Migration
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
        $this->alterColumn(\common\models\WaybillContent::tableName(), 'sum_with_vat', $this->float());
        $this->alterColumn(\common\models\WaybillContent::tableName(), 'sum_without_vat', $this->float());
        $this->alterColumn(\common\models\WaybillContent::tableName(), 'price_with_vat', $this->float());
        $this->alterColumn(\common\models\WaybillContent::tableName(), 'price_without_vat', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(\common\models\WaybillContent::tableName(), 'sum_with_vat', $this->integer());
        $this->alterColumn(\common\models\WaybillContent::tableName(), 'sum_without_vat', $this->integer());
        $this->alterColumn(\common\models\WaybillContent::tableName(), 'price_with_vat', $this->integer());
        $this->alterColumn(\common\models\WaybillContent::tableName(), 'price_without_vat', $this->integer());
    }
}
