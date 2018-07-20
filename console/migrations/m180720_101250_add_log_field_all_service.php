<?php

use yii\db\Migration;

/**
 * Class m180720_101250_add_log_field_all_service
 */
class m180720_101250_add_log_field_all_service extends Migration
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
        $this->addColumn('all_service', 'log_field', $this->string());

        $this->update('all_service', ['log_field' => 'guid', 'log_table' => 'rk_tasks'], [
            'id' => \api_web\modules\integration\modules\rkeeper\models\rkeeperService::getServiceId()
        ]);

        $this->update('all_service', ['log_field' => 'guide'], [
            'id' => \api_web\modules\integration\modules\iiko\models\iikoService::getServiceId()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('all_service', 'log_field');
    }
}
