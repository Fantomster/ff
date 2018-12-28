<?php

use common\models\IntegrationSettingFromEmail;
use yii\db\Migration;
use yii\db\Query;

class m181228_111004_alter_password_integration_setting_for_email extends Migration
{
    public $tableName = '{{%integration_setting_from_email}}';

    /**
     * @return bool|void
     * @throws \yii\base\InvalidConfigException
     */
    public function safeUp()
    {
        $settings = (new Query())
            ->from($this->tableName)
            ->all();

        foreach ($settings as $setting) {
            $password = Yii::$app->get('encode')->encrypt($setting['password'], $setting['user']);
            $this->update($this->tableName, ['password' => $password], [
                'id' => $setting['id']
            ]);
        }
    }

    /**
     * @return bool|void
     * @throws \yii\base\InvalidConfigException
     */
    public function safeDown()
    {
        $settings = (new Query())
            ->from($this->tableName)
            ->all();

        foreach ($settings as $setting) {
            $password = Yii::$app->get('encode')->decrypt($setting['password'], $setting['user']);
            $this->update($this->tableName, ['password' => $password], [
                'id' => $setting['id']
            ]);
        }
    }
}
