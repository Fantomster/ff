<?php

use api_web\components\Registry;
use common\models\AllServiceOperation;
use yii\db\Migration;

/**
 * Class m181204_120710_add_service_operation
 */
class m181204_120710_add_service_operation extends Migration
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
        (new AllServiceOperation([
            'service_id' => Registry::EGAIS_SERVICE_ID,
            'code' => 1,
            'denom' => 'Getting ticket',
            'comment' => 'Получение тикетов при постановки на баланс',
        ]))->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181204_120710_add_service_operation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181204_120710_add_service_operation cannot be reverted.\n";

        return false;
    }
    */
}
