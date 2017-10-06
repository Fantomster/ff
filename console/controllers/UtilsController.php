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

    public function actionEraseOrganization($orgId) {
        $organization = \common\models\Organization::findOne(['id' => $orgId]);
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

    public function MassErase() {
        $organizationsIds = [3471,
            3472,
            3473,
            3475,
            3478,
            3479,
            3480,
            3481,
            3482,
            3484,
            3485,
            3486,
            3487,
            3488,
            3490,
            3491,
            3492,
            3494,
            3495,
            3496,
            3497,
            3498,
            3499,
            3500,
            3501,
            3502,
            3504,
            3505,
            3506,
            3509,
            3510,
            3511,
            3512,
            3513,
            3513,
            3515,
            3516,
            3517,
            3518,
            3519,
            3520,
            3521,
            3522,
            3523,
            3524,
            3525,
            3526,
            3527,
            3528,
            3529,
            3530,
            3531,
            3532,
            3534,
            3535,
            3536,
            3537,
            3538,
            3539,
            3540,
            3541,
            3547,
            3548,
            3549,
            3550,
            3551,
            3554,
            3555,
            3556,
            3558,
            3560,
            3651,
            3562,
            3563,
            3564,
            3565,
            3566,
            3568,
            3570,
            3571,
            3572,
            3573,
            3574,
            3577,
            3578,
            3579,
            3580,
            3581,
            3582,
            3583,
            3584,
            3610,
            3611,
            3612,
            3613,
            3614,
            3615,
            3616,
            3617,
            3618,
            3619,
            3621,
            3642,
            3643,
            3644,
            3645,
            3646,
            3647,
            3648,
            3649,
            3650,
            3651,
            3652,
            3653,
            3654,
            3655,
            3656,
            3657,
            3658,
            3659,
            3660,
            3661,
            3662,
            3663,
            3664,
            3665,
            3666,
            3667,
            3668,
            3669,
            3670,
            3671,
            3672,
            3673,
            3674,
            3675,
            3676,
            3678,
            3679,
            3681,
            3682,
            3683,
            3684,
            3685,
            3686,
            3687,
            3688,
            3689,
            3690,
            3691,
            3692,
            3693,
            3694,
            3695,
            3696,
            3697,
            3698,
            3699,
            3700,
            3701,
            3702,
            3711,
            3712,
            3713,
            3714,
            3715,
            3716,
            3717,
            3718,
            3719,
            3721,
            3722,
            3723,
            3724,
            3725,
            3726,
            3727,
            3722,
            3723,
            3724,
            3725,
            3726,
            3727,
            3728,
            3729,
            3730,
            3731,
            3732,
            3733,
            3734,
            3735,
            3736,
            3738,
            3739,
            3740,
            3741,
            3742,
            3743,
            3744,
            3745,
            3746,
            3747,
            3748,
            3749,
            3750,
            3751,
            3752,
            3753,
            3754,
            3755,
            3756,
            3753,
            3758,
            3759,
            3760,
            3761,
            3762,
            3763,
            3764,
            3765,
            3766,
            3784,
            3791,
            3815,
            3816,
            3817,
            3818,
            3819,
            3820,
            3821,
            3822,
            3823,
            3824,
            3825,
            3828,
            3829,
            3830,
            3831,
            3832,
            3833,
            3834,
            3835,
            3836,
            3837,
            3838,
            3840,
            3843,
            3844,
            3845,
            3846,
            3848,
            3849,
            3850,
            3851,
            3852,
            3853,
            3854,
            3855,
            3856,
            3857,
            3861,
            3865,
            3884,
            3895,
            3897,
            3900,
            3901,
            3902,
            3904,
            3905,
            3906,
            3907,
            3908,
            3909,
            3910,
            3911,
            3912,
            3913,
            3914,
            3915,
            3916,
            3917,
            3918,
            3919,
            3921,
            3930,
            3931,
            3932,
            3934,
            3935,
            3936,
            3939,
            3940,
            3941,
            3944,
            3946,
            3947,
            3949,
            3950,
            3951,
            3952,
            3970,
            3971,
            3972,
            3975,
            3976,
            3977,
            3978,
            3979,
            3980,
            3981,
            3984,
            3985,
            3986,
            3987,
            3988,
            3989,
            3990,
            3994,
            3995,
            3996,
            3998,
            3999,
            4000,
            4001,
            4003,
            4014,
            4015,
            4016,
            4017,
            4018,
            4019,
            4020,
            4021,
            4022,
            4023,
            4024,
            4025,
            4028,
            4029,
            4030,
            4031,
            4032,
            4033,
            4034,
            4035,
            4036,
            4037,
            4038,
            4040,
            4041,
            4042,
            4044,
            4045,
            4046,
            4195,
            4246,
            4248,
            4255,
            4256,
            4257,
            4310,
            4553,
            5743,];
        foreach ($organizationsIds as $organizationId) {
            $this->actionEraseOrganization($organizationId);
        }
    }

}
