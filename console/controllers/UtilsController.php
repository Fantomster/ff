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

    public function actionAddNotifications() {
        $users = \common\models\User::find()
                ->leftJoin('email_notification', 'user.id = email_notification.user_id')
                ->leftJoin('sms_notification', 'user.id = sms_notification.user_id')
                ->where('email_notification.id IS NULL')
                ->orWhere('sms_notification.id IS NULL')
                ->limit(300)
                ->all();
        foreach ($users as $user) {
            if (empty($user->emailNotification)) {
                $emailNotification = new \common\models\notifications\EmailNotification();
                $emailNotification->user_id = $user->id;
                $emailNotification->orders = true;
                $emailNotification->requests = true;
                $emailNotification->changes = true;
                $emailNotification->invites = true;
                $emailNotification->save();
            }
            if (empty($user->smsNotification)) {
                $smsNotification = new \common\models\notifications\SmsNotification();
                $smsNotification->user_id = $user->id;
                $smsNotification->orders = true;
                $smsNotification->requests = true;
                $smsNotification->changes = true;
                $smsNotification->invites = true;
                $smsNotification->save();
            }
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

    public function actionCreateNotifications() {
        
    }

    public function actionCheckProductPictures() {
        $products = \common\models\CatalogBaseGoods::find()->where("image is not null")->andWhere("deleted = 0")->all();
        foreach ($products as $product) {
            if ($product->image) {
                $headers = get_headers($product->imageUrl);
                if ($headers[0] == 'HTTP/1.1 403 Forbidden') {
                    echo $product->id;
                    $product->image = null;
                    $product->save();
                    echo " - fixed\n";
                }
            }
        }
    }

    public function actionCheckOrganizationPictures() {
        $organizations = \common\models\Organization::find()->where("picture is not null")->all();
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

    public function actionTestRedis() {
        \Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'test',
                'message' => 'ololo!'
            ]);
    }
    
    public function actionUpdateMpCategories() {
        $categories = \common\models\MpCategory::find()->all();
        foreach ($categories as $category) {
            $category->update();
        }
    }
}
