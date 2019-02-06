<?php

use yii\db\Migration;

/**
 * Class m190204_115903_add_status_preorder
 */
class m190204_115903_add_status_preorder extends Migration
{
    const ID = 12;
    /**
     * @return bool
     */
    public function safeUp()
    {
        $model = new \common\models\OrderStatus();
        $model->id = self::ID;
        $model->denom = 'STATUS_PREORDER';
        $model->comment = 'Предзаказ';
        if ($model->save()) {
            return true;
        } else {
            print_r($model->getFirstErrors());
            return false;
        }
    }

    /**
     * @return bool|void
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function safeDown()
    {
        $model = \common\models\OrderStatus::findOne(['id' => self::ID]);
        if ($model) {
            $model->delete();
        }
    }
}
