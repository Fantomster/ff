<?php

use common\models\IntegrationSetting;
use yii\db\Migration;

/**
 * Class m181014_102312_add_service_id_column_rename_name_column_for_integration_setting
 */
class m181014_102312_add_service_id_column_rename_name_column_for_integration_setting extends Migration
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
//        $this->addColumn('{{%integration_setting}}', 'service_id', $this->integer());
        $settings = IntegrationSetting::find()->all();
        /**@var IntegrationSetting $setting*/
        foreach ($settings as $setting){
            $sName = substr($setting->name, 0, 4);
            $setting->name = substr($setting->name, 5);
            if ($sName == 'rkws'){
                $setting->service_id = 1;
            } elseif ($sName == 'iiko'){
                $setting->service_id = 2;
            } elseif($sName == 'merc'){
                $setting->service_id = 4;
            } elseif($sName == 'ones'){
                $setting->service_id = 8;
            }
            $setting->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181014_102312_add_service_id_column_rename_name_column_for_integration_setting cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181014_102312_add_service_id_column_rename_name_column_for_integration_setting cannot be reverted.\n";

        return false;
    }
    */
}
