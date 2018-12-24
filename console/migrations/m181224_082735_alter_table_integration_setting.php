<?php

use api_web\components\Registry;
use yii\db\Migration;

class m181224_082735_alter_table_integration_setting extends Migration
{
    public $tableName = '{{%integration_setting}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addColumn($this->tableName, 'required_moderation', $this->tinyInteger()
            ->defaultValue(0)
            ->comment('Настройка сервиса обязательна к модерации'));

        $this->update($this->tableName, ['required_moderation' => 1], [
            'service_id' => Registry::RK_SERVICE_ID,
            'name' => 'code'
        ]);

    }

    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'required_moderation');
    }
}
