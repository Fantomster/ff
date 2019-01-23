<?php

use yii\db\Migration;

/**
 * Class m190111_093104_refactor_default_value_setting
 */
class m190111_093104_refactor_default_value_setting extends Migration
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
        $ids = (new \yii\db\Query())->select('id')
            ->from(\common\models\IntegrationSetting::tableName())
            ->where(['name' => ['application_id', 'application_secret']])
            ->column($this->db);

        $this->update(\common\models\IntegrationSetting::tableName(), ['default_value' => null], ['id' => $ids]);
        $this->alterColumn(\common\models\IntegrationSettingValue::tableName(), 'value', $this->string(255)->null());
        $this->update(\common\models\IntegrationSettingValue::tableName(), [
            'value' => null
        ], [
            'id'    => $ids,
            'value' => "0"
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190111_093104_refactor_default_value_setting cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190111_093104_refactor_default_value_setting cannot be reverted.\n";

        return false;
    }
    */
}
