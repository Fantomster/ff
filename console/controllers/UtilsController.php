<?php

namespace console\controllers;

use yii\console\Controller;

class UtilsController extends Controller {

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

    public function actionFillChatRecipient() {
        $emptyRecipientMessages = \common\models\OrderChat::find()
                ->where(['recipient_id' => 0])
                ->all();
        foreach ($emptyRecipientMessages as $message) {
            $order = $message->order;
            $senderId = $message->sentBy->organization_id;
            if ($order->client_id == $senderId) {
                $message->recipient_id = $order->vendor_id;
            } else {
                $message->recipient_id = $order->client_id;
            }
            if ($message->save()) {
                echo 'Recipient set for message #' . $message->id . " \n";
            }
        }
    }

    public function actionCheckProductPictures() {
        //
    }

    public function actionCheckOrganizationPictures() {
        $organizations = \common\models\Organization::find()->all();
        foreach ($organizations as $organization) {
            if ($organization->picture) {
                $headers = get_headers($organization->pictureUrl);
                if ($headers[0] == 'HTTP/1.1 403 Forbidden') {
                    echo $organization->id;
                    $organization->picture = null;
                    $organization->save();
                    echo " - fixed\n";
                }
            }
        }
    }

}
