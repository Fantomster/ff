<?php

use yii\db\Migration;

/**
 * Class m181118_194527_lenght_400_agent_name
 */
class m181118_194527_lenght_400_agent_name extends Migration
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
        $this->alterColumn(\common\models\OuterAgentNameWaybill::tableName(), 'name', $this->string(400)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
