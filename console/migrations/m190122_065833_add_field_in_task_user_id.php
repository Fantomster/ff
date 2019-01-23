<?php

use yii\db\Migration;

/**
 * Class m190122_065833_add_field_in_task_user_id
 */
class m190122_065833_add_field_in_task_user_id extends Migration
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
        $this->addColumn(\common\models\OuterTask::tableName(), 'user_id', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\common\models\OuterTask::tableName(), 'user_id');
    }
}
