<?php

namespace console\controllers;

use api_web\components\Notice;
use common\models\User;
use yii\console\Controller;

class UtilsController extends Controller
{

    /**
     * Отправка Емайлов пользователем, кто у нас ровно неделю
     */
    public function actionSendEmailWeekend()
    {
        $users = User::find()->where(['status' => 1, 'subscribe' => 1])
            ->andWhere('created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)')
            ->all();

        if (!empty($users)) {
            \Yii::$app->language = 'ru';
            foreach ($users as $user) {
                Notice::init('User')->sendEmailWeekend($user);
            }
        }
    }

    /**
     * Отправка Емайлов пользователем, через час после логина
     */
    public function actionSendMessageManager()
    {
        $users = User::find()->where(['status' => 1, 'subscribe' => 1, 'send_manager_message' => 0])
            ->andWhere('first_logged_in_at is not null')
            ->andWhere('first_logged_in_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)')
            ->all()
            ->limit(10);

        if (!empty($users)) {
            \Yii::$app->language = 'ru';
            foreach ($users as $user) {
                Notice::init('User')->sendEmailManagerMessage($user);
                $user->send_manager_message = 1;
                $user->save();
            }
        }
    }

    /**
     * Отправка Емайлов пользователем, через 2 дня после создания
     */
    public function actionSendDemonstration()
    {
        $users = User::find()->where(['status' => 1, 'subscribe' => 1])
            ->andWhere('created_at < DATE_SUB(NOW(), INTERVAL 2 DAY)')
            ->all();

        if (!empty($users)) {
            \Yii::$app->language = 'ru';
            foreach ($users as $user) {
                Notice::init('User')->sendEmailDemonstration($user);
            }
        }
    }

    public function actionAddDeliveries()
    {
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

    public function actionAddNotifications()
    {
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

    public function actionFillChatRecipient()
    {
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

    public function actionCreateNotifications()
    {

    }

    public function actionCheckProductPictures()
    {
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

    public function actionCheckOrganizationPictures()
    {
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

    public function actionTestRedis()
    {
        \Yii::$app->redis->executeCommand('PUBLISH', [
            'channel' => 'test',
            'message' => 'ololo!'
        ]);
    }

    public function actionUpdateMpCategories()
    {
        $categories = \common\models\MpCategory::find()->all();
        foreach ($categories as $category) {
            $category->update();
        }
    }

    public function actionEraseOrganization($orgId)
    {
        $organization = \common\models\Organization::findOne(['id' => $orgId]);
        if (empty($organization)) {
            return;
        }
        echo $organization->name . "\n";
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            \common\models\DeliveryRegions::deleteAll(['supplier_id' => $orgId]);
            \common\models\FranchiseeAssociate::deleteAll(['organization_id' => $orgId]);
            $guides = \common\models\guides\Guide::findAll(['client_id' => $orgId]);
            foreach ($guides as $guide) {
                \common\models\guides\GuideProduct::deleteAll(['guide_id' => $guide->id]);
                $guide->delete();
            }
            \common\models\RelationSuppRest::deleteAll(['supp_org_id' => $orgId]);
            \common\models\RelationSuppRest::deleteAll(['rest_org_id' => $orgId]);
            $orders = ($organization->type_id === \common\models\Organization::TYPE_RESTAURANT) ? \common\models\Order::findAll(['client_id' => $orgId]) : \common\models\Order::findAll(['vendor_id' => $orgId]);
            foreach ($orders as $order) {
                //\common\models\OrderContent::deleteAll(['order_id' => $order->id]);
                foreach ($order->orderContent as $content) {
                    $content->delete();
                }
                foreach ($order->orderChat as $chat) {
                    $chat->delete();
                }
                //\common\models\OrderChat::deleteAll(['order_id' => $this->id]);
                $order->delete();
            }
            $catalogs = \common\models\Catalog::findAll(['supp_org_id' => $orgId]);
            foreach ($catalogs as $catalog) {
                \common\models\CatalogGoods::deleteAll(['cat_id' => $catalog->id]);
            }
            $goodsNotes = \common\models\GoodsNotes::find()
                ->leftJoin('catalog_base_goods', 'catalog_base_goods.id = goods_notes.catalog_base_goods_id')
                ->where(['catalog_base_goods.supp_org_id' => $orgId])
                ->all();
            foreach ($goodsNotes as $note) {
                $note->delete();
            }
            \common\models\CatalogBaseGoods::deleteAll(['supp_org_id' => $orgId]);
            \common\models\Catalog::deleteAll(['supp_org_id' => $orgId]);
            \common\models\RequestCallback::deleteAll(['supp_org_id' => $orgId]);
            $requests = \common\models\Request::findAll(['rest_org_id' => $orgId]);
            foreach ($requests as $request) {
                \common\models\RequestCallback::deleteAll(['request_id' => $this->id]);
                \common\models\RequestCounters::deleteAll(['request_id' => $this->id]);
                $request->delete();
            }
            \common\models\ManagerAssociate::deleteAll(['organization_id' => $orgId]);
            $users = \common\models\User::findAll(['organization_id' => $orgId]);
            foreach ($users as $user) {
                \common\models\ManagerAssociate::deleteAll(['manager_id' => $this->id]);
                $user->emailNotification->delete();
                $user->smsNotification->delete();
                \common\models\UserFcmToken::deleteAll(['user_id' => $this->id]);
                \common\models\UserToken::deleteAll(['user_id' => $this->id]);
                $user->profile->delete();
                $user->delete();
            }
            $organization->buisinessInfo->delete();
            $organization->delete();
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollback();
        }
    }

    public function actionMassErase()
    {
        $organizationsIds = [];
        foreach ($organizationsIds as $organizationId) {
            $this->actionEraseOrganization($organizationId);
        }
    }

    public function actionMassDecode()
    {
        set_time_limit(180);
        do {
            $products = \common\models\CatalogBaseGoods::find()->where("product like '%&#039;%' ")->limit(100)->all();
            foreach ($products as $cbg) {
                $cbg->product = \yii\helpers\Html::decode($cbg->product);
                $cbg->save(false);
                echo $cbg->product . "\n";
            }
        } while ($products);
    }

}
