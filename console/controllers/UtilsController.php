<?php

namespace console\controllers;

use yii\console\Controller;

class UtilsController extends Controller
{
    public function actionAddDeliveries() {
        $vendors = \common\models\Organization::find()
                ->leftJoin('delivery', 'organization.id = delivery.vendor_id')
                ->where(['type_id' => \common\models\Organization::TYPE_SUPPLIER])
                ->andWhere('delivery.vendor_id IS NULL')
                ->all();
        foreach ($vendors as $vendor) {
            $delivery = new \common\models\Delivery();
            $delivery->vendor_id = $vendor->id;
            $delivery->save();
            echo "Delivery info for '$vendor->name' (id:$vendor->id) created \n";
        }
    }
}