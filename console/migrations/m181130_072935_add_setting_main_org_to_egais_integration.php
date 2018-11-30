<?php

use api_web\components\Registry;
use common\models\IntegrationSetting;
use yii\db\Migration;

/**
 * Class m181130_072935_add_setting_main_org_to_egais_integration
 */
class m181130_072935_add_setting_main_org_to_egais_integration extends Migration
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
        (new IntegrationSetting([
            'name' => 'egais_url',
            'default_value' => '',
            'comment' => 'Адрес УТМ для запросов',
            'type' => 'input_text',
            'is_active' => 1,
            'service_id' => Registry::EGAIS_SERVICE_ID,
        ]))->save();

        (new IntegrationSetting([
            'name' => 'fsrar_id',
            'default_value' => '',
            'comment' => 'Идентификатор организации в ФС РАР',
            'type' => 'input_text',
            'is_active' => 1,
            'service_id' => Registry::EGAIS_SERVICE_ID,
        ]))->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181130_072935_add_setting_main_org_to_egais_integration cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181130_072935_add_setting_main_org_to_egais_integration cannot be reverted.\n";

        return false;
    }
    */
}
