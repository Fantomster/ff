<?php

use api_web\exceptions\ValidationException;
use common\models\IntegrationSetting;
use api_web\components\Registry;
use yii\db\Migration;

/**
 * Class m181226_144112_add_item_list_for_integration_setting_table
 */
class m181226_144112_add_item_list_for_integration_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $models = IntegrationSetting::findAll(['name' => ['main_org', 'enterprise_guid']]);
        foreach ($models as $model) {
            $model->type = 'input_text';
            $model->save();
        }
        $model = IntegrationSetting::findOne(['name' => 'defGoodGroup']);
        $model->type = 'dropdown_list';
        $model->save();

        $models = IntegrationSetting::findAll(['type' => 'dropdown_list']);
        foreach ($models as $model) {
            switch ($model->name) {
                case 'taxVat':
                    $model->item_list = json_encode(Registry::$nds_list);
                    break;
                case 'useAutoExport':
                case 'useAutoNumber':
                case 'doBackSync':
                case 'useAcceptedDocs':
                case 'useAgentGroup':
                case 'useTaxVat':
                case 'hand_load_only':
                case 'column_number_invoice':
                case 'useWinEncoding':
                    $model->item_list = json_encode([0 => 'Выключено',
                                                     1 => 'Включено',]);
                    break;
                case 'auto_unload_invoice':
                    $model->item_list = json_encode([0 => 'Выключено',
                                                     1 => 'Включено',
                                                     2 => 'Полуавтомат',]);
                    break;

            }
            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181226_144112_add_item_list_for_integration_setting_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181226_144112_add_item_list_for_integration_setting_table cannot be reverted.\n";

        return false;
    }
    */
}
