<?php

use api_web\components\Registry;
use common\models\AllServiceOperation;
use yii\db\Migration;

/**
 * Class m181128_143533_add_service_operation_for_excel_upload
 */
class m181128_143533_add_service_operation_for_excel_upload extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $model = new AllServiceOperation([
            'service_id' => Registry::MC_BACKEND,
            'code' => 1,
            'denom' => 'upload_excel_file',
            'comment' => 'Подготовка к загрузке прайс-листа'
        ]);
        $model->save();
        $model = new AllServiceOperation([
            'service_id' => Registry::MC_BACKEND,
            'code' => 2,
            'denom' => 'uploaded_info',
            'comment' => 'Результат загрузки прайс-листа'
        ]);
        $model->save();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181128_143533_add_service_operation_for_excel_upload cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181128_143533_add_service_operation_for_excel_upload cannot be reverted.\n";

        return false;
    }
    */
}
