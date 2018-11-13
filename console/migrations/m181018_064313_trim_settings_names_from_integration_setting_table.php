<?php

use common\models\IntegrationSetting;
use yii\db\Migration;

/**
 * Class m181018_064313_trim_settings_names_from_integration_setting_table
 */
class m181018_064313_trim_settings_names_from_integration_setting_table extends Migration
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
        $models = IntegrationSetting::find()->all();
        /**@var IntegrationSetting $model*/
        foreach ($models as $model) {
            $model->name = trim($model->name);
            $model->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181018_064313_trim_settings_names_from_integration_setting_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181018_064313_trim_settings_names_from_integration_setting_table cannot be reverted.\n";

        return false;
    }
    */
}
