<?php

use common\models\IntegrationSettingFromEmail;
use yii\db\Migration;

class m181227_083732_alter_password_in_setting_from_email extends Migration
{
    /**
     * @return bool|void
     * @throws \yii\base\InvalidConfigException
     */
    public function safeUp()
    {
        $settingRobots = IntegrationSettingFromEmail::find()->all();
        foreach ($settingRobots as $settingRobot) {
            $settingRobot->password = \Yii::$app->get('encode')->encrypt($settingRobot->password, $settingRobot->user);
            $settingRobot->save();
        }
    }

    /**
     * @return bool|void
     * @throws \yii\base\InvalidConfigException
     */
    public function safeDown()
    {
        $settingRobots = IntegrationSettingFromEmail::find()->all();
        foreach ($settingRobots as $settingRobot) {
            $settingRobot->password = \Yii::$app->get('encode')->decrypt($settingRobot->password, $settingRobot->user);
            $settingRobot->save();
        }
    }
}
