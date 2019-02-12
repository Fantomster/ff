<?php

use yii\db\Migration;

/**
 * Class m190206_094640_add_lazy_vendor_type
 */
class m190206_094640_add_lazy_vendor_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $model = \common\models\OrganizationType::findOne(['id' => \common\models\Organization::TYPE_SUPPLIER]);
        $model->name = 'Подключенный поставщик';
        if($model->save()) {
            $new = new \common\models\OrganizationType();
            $new->name = 'Поставщик';
            $new->save();
        }
    }

    /**
     * @return bool|void
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function safeDown()
    {
        $model = \common\models\OrganizationType::findOne(['name' => 'Поставщик']);
        $model->delete();

        $model = \common\models\OrganizationType::findOne(['id' => \common\models\Organization::TYPE_SUPPLIER]);
        $model->name = 'Поставщик';
        $model->save();
    }
}
