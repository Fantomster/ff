<?php

namespace console\controllers;

use common\components\ecom\providers\Provider;
use common\components\ecom\realization\Realization;
use common\components\EComIntegration;
use common\components\edi\EDIIntegration;
use common\models\EcomIntegrationConfig;
use common\models\edi\EdiOrganization;
use common\models\edi\EdiProvider;
use Yii;
use yii\web\View;
use yii\console\Controller;
use common\models\WhiteList;
use common\models\CatalogBaseGoods;
use common\models\Organization;
use api_web\components\Notice;
use common\models\User;

//`php yii cron/count`
class CronController extends Controller
{

    /**
     * Отправка Емайлов пользователем, кто у нас ровно неделю
     */
    public function actionSendEmailWeekend()
    {
        $users = User::find()->where(['status' => 1, 'send_week_message' => 0, 'language' => 'ru'])
            ->andWhere('created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)')
            ->all();

        if (!empty($users)) {
            \Yii::$app->language = 'ru';
            foreach ($users as $user) {
                if ($user->subscribe) {
                    Notice::init('User')->sendEmailWeekend($user);
                }
                $user->send_week_message = 1;
                $user->save();
            }
        }
    }

    /**
     * Отправка Емайлов пользователем, через час после логина
     */
    public function actionSendMessageManager()
    {
        $users = User::find()->where(['status' => 1, 'send_manager_message' => 0, 'language' => 'ru'])
            ->andWhere('first_logged_in_at is not null')
            ->andWhere('first_logged_in_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)')
            ->limit(10)
            ->all();

        if (!empty($users)) {
            \Yii::$app->language = 'ru';
            foreach ($users as $user) {
                if ($user->subscribe) {
                    Notice::init('User')->sendEmailManagerMessage($user);
                }
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
        $users = User::find()->where(['status' => 1, 'send_demo_message' => 0, 'language' => 'ru'])
            ->andWhere('created_at < DATE_SUB(NOW(), INTERVAL 2 DAY)')
            ->all();

        if (!empty($users)) {
            \Yii::$app->language = 'ru';
            foreach ($users as $user) {
                if ($user->subscribe) {
                    Notice::init('User')->sendEmailDemonstration($user);
                }
                $user->send_demo_message = 1;
                $user->save();
            }
        }
    }

    public function actionCount()
    {
        $restourants = rand(15, 25);
        $suppliers = rand(5, 10);
        $sql = "update main_counter set supp_count = supp_count + $suppliers, rest_count = rest_count + $restourants ";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionPlusOne()
    {
        $query = "SELECT updated_at FROM main_counter LIMIT 1";
        $latest = Yii::$app->db->createCommand($query)->queryScalar();
        $now = new \DateTime();
        $latest = new \DateTime($latest);
        $randomInterval = rand(3, 15);
        $interval = $now->diff($latest, true)->i;
        echo "latest:" . Yii::$app->formatter->asTime($latest, "php:j M Y, H:i:s") . ";now:" . Yii::$app->formatter->asTime($now, "php:j M Y, H:i:s") . ";diff:" . $interval . "\n";
    }

    //обновление одного продукта (крон запускается каждые 2 минуты)
    public function actionUpdateCollection()
    {
        $base = CatalogBaseGoods::find()
            ->andWhere('category_id is not null')
            ->andWhere(['in', 'es_status', [1, 2]])
            ->limit(500)
            ->all();

        foreach ($base as $catalogBaseGoods) {
            try {
                $product_id = $catalogBaseGoods->id;
                $product_image = !empty($catalogBaseGoods->image) ? $catalogBaseGoods->imageUrl : '';
                $product_name = $catalogBaseGoods->product;
                $product_supp_id = $catalogBaseGoods->supp_org_id;
                $product_supp_name = $catalogBaseGoods->vendor->name;
                $product_price = $catalogBaseGoods->price;
                $product_currency = $catalogBaseGoods->catalog->currency->symbol;
                $product_category_id = $catalogBaseGoods->category->parent;
                $product_category_name = \common\models\MpCategory::find()->where(['id' => $catalogBaseGoods->category->parent])->one()->name;
                $product_category_sub_id = $catalogBaseGoods->category->id;
                $product_category_sub_name = $catalogBaseGoods->category->name;
                $product_show_price = $catalogBaseGoods->mp_show_price;
                $product_created_at = $catalogBaseGoods->created_at;
                $product_partnership = $catalogBaseGoods->vendor->partnership;
                $product_rating = $catalogBaseGoods->vendor->rating;

                if (!empty($product_image)) {
                    $product_rating = $product_rating + 5;
                }
                if (!empty($product_show_price)) {
                    $product_rating = $product_rating + 5;
                }

                if (($catalogBaseGoods->es_status == 1) &&
                    ($catalogBaseGoods->market_place == 1) &&
                    ($catalogBaseGoods->deleted == 0) &&
                    ($catalogBaseGoods->vendor->white_list == 1) &&
                    ($catalogBaseGoods->status == 1)) {

                    if (\common\models\ES\Product::find()->where(['product_id' => $product_id])->exists()) {

                        $es_product = \common\models\ES\Product::find()->where(['product_id' => $product_id])->one();
                        $es_product->attributes = [
                            "product_id"                => $product_id,
                            "product_image"             => $product_image,
                            "product_name"              => $product_name,
                            "product_supp_id"           => $product_supp_id,
                            "product_supp_name"         => $product_supp_name,
                            "product_price"             => $product_price,
                            "product_currency"          => $product_currency,
                            "product_category_id"       => $product_category_id,
                            "product_category_name"     => $product_category_name,
                            "product_category_sub_id"   => $product_category_sub_id,
                            "product_category_sub_name" => $product_category_sub_name,
                            "product_show_price"        => $product_show_price,
                            "product_created_at"        => $product_created_at,
                            "product_rating"            => $product_rating,
                            "product_partnership"       => $product_partnership
                        ];
                        $es_product->save();
                    } else {
                        $es_product = new \common\models\ES\Product();
                        $es_product->attributes = [
                            "product_id"                => $product_id,
                            "product_image"             => $product_image,
                            "product_name"              => $product_name,
                            "product_supp_id"           => $product_supp_id,
                            "product_supp_name"         => $product_supp_name,
                            "product_price"             => $product_price,
                            "product_currency"          => $product_currency,
                            "product_category_id"       => $product_category_id,
                            "product_category_name"     => $product_category_name,
                            "product_category_sub_id"   => $product_category_sub_id,
                            "product_category_sub_name" => $product_category_sub_name,
                            "product_show_price"        => $product_show_price,
                            "product_created_at"        => $product_created_at,
                            "product_rating"            => $product_rating,
                            "product_partnership"       => $product_partnership
                        ];
                        $es_product->save();
                    }
                    $catalogBaseGoods->es_status = 0;
                    $catalogBaseGoods->rating = $product_rating;
                    $catalogBaseGoods->save(false);
                } else {
                    if (\common\models\ES\Product::find()->where(['product_id' => $product_id])->exists()) {
                        $es_product = \common\models\ES\Product::find()->where(['product_id' => $product_id])->one();
                        $es_product->delete();
                    }
                    $catalogBaseGoods->es_status = 0;
                    $catalogBaseGoods->save(false);
                }
            } catch (\Exception $e) {
                echo($e->getMessage());
                if (\common\models\ES\Product::find()->where(['product_id' => $catalogBaseGoods->id])->exists()) {
                    $es_product = \common\models\ES\Product::find()->where(['product_id' => $product_id])->one();
                    $es_product->delete();
                }
                $catalogBaseGoods->es_status = 0;
                $catalogBaseGoods->save(false);
            }
        }
    }

    public function actionUpdateCategory()
    {
        $model = \common\models\MpCategory::find()->where('parent is not null')->all();
        foreach ($model as $name) {
            $category = new \common\models\ES\Category();
            $category_id = $name->parent;
            $category_slug = $name->slug;
            $category_sub_id = $name->id;
            $category_name = $name->name;
//            if(\common\models\ES\Category::find()->where(['category_sub_id'=>$category_sub_id]) && \common\models\ES\Category::find()->exists()){
//            $category = \common\models\ES\Category::find()->where(['category_sub_id'=>$category_sub_id])->one();   
//            $category->attributes = [
//                "category_id" => $category_id,
//                "category_slug" => $category_slug,
//                "category_sub_id" => $category_sub_id,
//                "category_name" => $category_name
//            ];
//            $category->save();    
//            }else{
            $category->attributes = [
                "category_id"     => $category_id,
                "category_slug"   => $category_slug,
                "category_sub_id" => $category_sub_id,
                "category_name"   => $category_name
            ];
            $category->save();
//            }
        }
    }

    public function actionUpdateSuppliers()
    {
        $suppliers = Organization::find()
            ->where([
                'type_id'    => Organization::TYPE_SUPPLIER,
                'white_list' => Organization::WHITE_LIST_ON])
            ->andWhere(['in', 'es_status', [
                Organization::ES_UPDATED,
                Organization::ES_DELETED
            ]])
            ->andWhere('locality is not null and locality <> \'undefined\'')
            ->limit(20)
            ->all();
        foreach ($suppliers as $supplier) {
            $rating = 0;
            if ($supplier->partnership) {
                $rating = $rating + 16;
            }
            if ($supplier->picture) {
                $rating = $rating + 5;
            }
            if ($supplier->contact_name) {
                $rating = $rating + 2;
            }
            if ($supplier->phone) {
                $rating = $rating + 2;
            }
            if ($supplier->email) {
                $rating = $rating + 2;
            }
            if ($supplier->address) {
                $rating = $rating + 2;
            }
            if ($supplier->about) {
                $rating = $rating + 2;
            }

            if ($supplier->es_status == Organization::ES_UPDATED) {
                if (\common\models\ES\Supplier::find()->where(['supplier_id' => $supplier->id])->count() == 0) {
                    $es_supplier = new \common\models\ES\Supplier();
                    $es_supplier->attributes = [
                        "supplier_id"          => $supplier->id,
                        "supplier_image"       => !empty($supplier->picture) ? $supplier->pictureUrl : '',
                        "supplier_name"        => $supplier->name,
                        "supplier_rating"      => $rating,
                        "supplier_partnership" => $supplier->partnership
                    ];
                    $es_supplier->save();
                }
                if (\common\models\ES\Supplier::find()->where(['supplier_id' => $supplier->id])->count() > 0) {
                    $es_supplier = \common\models\ES\Supplier::find()->where(['supplier_id' => $supplier->id])->one();
                    $es_supplier->attributes = [
                        "supplier_image"       => !empty($supplier->picture) ? $supplier->pictureUrl : '',
                        "supplier_name"        => $supplier->name,
                        "supplier_rating"      => $rating,
                        "supplier_partnership" => $supplier->partnership
                    ];
                    $es_supplier->save();
                }
            }
            if ($supplier->es_status == Organization::ES_DELETED) {
                if (\common\models\ES\Supplier::find()->where(['supplier_id' => $supplier->id])->count() > 0) {
                    $es_supplier = \common\models\ES\Supplier::find()->where(['supplier_id' => $supplier->id])->one();
                    $es_supplier->delete();
                }
                Yii::$app->db->createCommand("update " . CatalogBaseGoods::tableName() . " set "
                    . "es_status = " . Organization::ES_DELETED . " "
                    . "where supp_org_id = " . $supplier->id)->execute();
            }
            Yii::$app->db->createCommand("update organization set "
                . "es_status = " . Organization::ES_INACTIVE . ","
                . "rating = " . $rating . " "
                . "where id = " . $supplier->id)->execute();
            if ($supplier->white_list == 1) {
                Yii::$app->db->createCommand("update " . CatalogBaseGoods::tableName() . " set "
                    . "es_status = " . CatalogBaseGoods::ES_UPDATE . " "
                    . "where supp_org_id = " . $supplier->id . " and "
                    . "es_status <> " . CatalogBaseGoods::ES_DELETED)->execute();
            }
        }
    }

    public function actionUpdateOrganizationRating()
    {

    }

    public function actionUpdateProductRating()
    {

    }

    public function actionMappingOrganizationFromGoogleApiMaps()
    {
        $model = Organization::find()->where('lng is not null and lat is not null and country is not null and administrative_area_level_1 is null')->limit(500)->all();
        foreach ($model as $s) {
            $address_url = 'https://maps.googleapis.com/maps/api/geocode/json?key=' . Yii::$app->params['google-api']['key-id'] . '&latlng=' . $s->lat . ',' . $s->lng . '&language=ru&sensor=false';
            $address_json = json_decode(file_get_contents($address_url));
            if (!empty($address_json->results[0]->address_components)) {
                $address_data = $address_json->results[0]->address_components;
                $location = [];
                $location['locality'] = '';
                $location['admin_1'] = '';
                $location['country'] = '';
                foreach ($address_data as $component) {
                    switch ($component->types) {
                        case in_array('locality', $component->types):
                            $location['locality'] = $component->long_name;
                            break;
                        case in_array('administrative_area_level_1', $component->types):
                            $location['admin_1'] = $component->long_name;
                            break;
                        case in_array('country', $component->types):
                            $location['country'] = $component->long_name;
                            break;
                    }
                }

                $country = $location['country'];
                $locality = $location['locality'];
                $administrative_area_level_1 = $location['admin_1'];

                $organization = Organization::findOne($s->id);
                $organization->administrative_area_level_1 = $administrative_area_level_1;
                $organization->save();
            }
        }
    }

    public function actionSendMailNewRequests()
    {
        //
    }

    public function actionUpdateBlacklist()
    {
        Organization::updateAll(["blacklisted" => true], "blacklisted = 0 AND (name LIKE '%test%' OR name LIKE '%тест%')");
    }

    //handle EDI integration files
    public function actionHandleFiles()
    {
        $ediOrganizations = EdiOrganization::find()->all();
        if ($ediOrganizations) {
            foreach ($ediOrganizations as $organization) {
                $orgId = $organization->organization_id;
                $providerID = $organization->provider_id;
                $provider = EdiProvider::findOne(['id' => $providerID]);
                if ($provider == 'LeradataProvider' && $organization->organization->type_id == Organization::TYPE_SUPPLIER) continue;
                $ediIntegration = new EDIIntegration(['orgId' => $orgId, 'providerID' => $providerID]);
                $ediIntegration->handleFilesList();
            }
        }
    }

    //handle EDI integration files queue
    public function actionHandleFilesQueue()
    {
        $ediOrganizations = EdiOrganization::find()->all();
        if ($ediOrganizations) {
            foreach ($ediOrganizations as $organization) {
                $orgId = $organization->organization_id;
                $providerID = $organization->provider_id;
                $provider = EdiProvider::findOne(['id' => $providerID]);
                if ($provider == 'LeradataProvider' && $organization->organization->type_id == Organization::TYPE_SUPPLIER) continue;
                $ediIntegration = new EDIIntegration(['orgId' => $orgId, 'providerID' => $providerID]);
                $ediIntegration->handleFilesListQueue();
            }
        }
    }

    public function actionProcessMercVsd()
    {
        $organizations = \yii\helpers\ArrayHelper::map(Yii::$app->db_api->CreateCommand("
            SELECT count(mvsd.id) AS vsd_count, mpconst.org AS organization_id
            FROM merc_vsd AS mvsd LEFT JOIN merc_pconst AS mpconst ON mvsd.recipient_guid = mpconst.value AND mpconst.const_id = 10
            WHERE mvsd.status = 'CONFIRMED'
            GROUP BY mpconst.org;
        ")->queryAll(), 'organization_id', 'vsd_count');
        var_dump($organizations);

        foreach ($organizations as $organization_id => $vsd_count) {
            $organization = Organization::findOne(['id' => $organization_id]);
            if (isset($organization)) {
                $recipients = [];
                $relatedUsers = \common\models\RelationUserOrganization::findAll([
                    'organization_id' => $organization_id,
                    'is_active'       => true,
                    'role_id'         => [
                        \common\models\Role::ROLE_RESTAURANT_MANAGER,
                        \common\models\Role::ROLE_SUPPLIER_MANAGER,
                        \common\models\Role::ROLE_ADMIN,
                        \common\models\Role::ROLE_FKEEPER_MANAGER,
                    ],
                ]);
                foreach ($relatedUsers as $relatedUser) {
                    if ($relatedUser->user->emailNotification->merc_vsd) {
                        $recipients[] = $relatedUser->user->email;
                    }
                }
                foreach ($organization->additionalEmail as $addEmail) {
                    if ($addEmail->merc_vsd) {
                        $recipients[] = $addEmail->email;
                    }
                }
                var_dump($recipients);
                foreach ($recipients as $recipient) {
                    Yii::$app->mailer->htmlLayout = '@common/mail/layouts/mail';
                    $mailer = Yii::$app->mailer;
                    $subject = Yii::t('app', 'common.mail.merc_vsd.subject', ['ru' => 'Уведомление о непогашенных ВСД для'], 'ru') . '  ' . $organization->name;
                    $mailer->compose('merc_vsd', compact("vsd_count"))
                        ->setTo($recipient)
                        ->setSubject($subject)
                        ->send();
                }
            }
        }
    }

    public function actionProcessMercStockExpiry()
    {
        $organizations = Yii::$app->db_api->CreateCommand("
            SELECT mpconst.org AS organization_id
            FROM merc_stock_entry AS stock 
            LEFT JOIN merc_pconst AS mpconst ON stock.owner_guid COLLATE utf8_unicode_ci = mpconst.value COLLATE utf8_unicode_ci AND mpconst.const_id = 10
            WHERE stock.expiry_date < now()
            GROUP BY mpconst.org;
        ")->queryColumn();
        var_dump($organizations);

        foreach ($organizations as $organization_id) {
            $organization = Organization::findOne(['id' => $organization_id]);
            if (isset($organization)) {
                $recipients = [];
                $relatedUsers = \common\models\RelationUserOrganization::findAll([
                    'organization_id' => $organization_id,
                    'is_active'       => true,
                    'role_id'         => [
                        \common\models\Role::ROLE_RESTAURANT_MANAGER,
                        \common\models\Role::ROLE_SUPPLIER_MANAGER,
                        \common\models\Role::ROLE_ADMIN,
                        \common\models\Role::ROLE_FKEEPER_MANAGER,
                    ],
                ]);
                foreach ($relatedUsers as $relatedUser) {
                    if ($relatedUser->user->emailNotification->merc_stock_expiry) {
                        $recipients[] = $relatedUser->user->email;
                    }
                }
                foreach ($organization->additionalEmail as $addEmail) {
                    if ($addEmail->merc_stock_expiry) {
                        $recipients[] = $addEmail->email;
                    }
                }
                var_dump($recipients);
                foreach ($recipients as $recipient) {
                    Yii::$app->mailer->htmlLayout = '@common/mail/layouts/mail';
                    $mailer = Yii::$app->mailer;
                    $subject = Yii::t('app', 'common.mail.merc_stock_expiry.subject', ['ru' => 'Уведомление о проблемной продукции'], 'ru') . '  ' . $organization->name;
                    $mailer->compose('merc_stock_expiry')
                        ->setTo($recipient)
                        ->setSubject($subject)
                        ->send();
                }
            }
        }
    }

}
