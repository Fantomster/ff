<?php

namespace console\controllers;

use api\common\models\merc\mercPconst;
use api\common\models\merc\MercStockEntry;
use api\common\models\merc\MercVsd;
use common\components\ecom\providers\Provider;
use common\components\ecom\realization\Realization;
use common\components\edi\EDIIntegration;
use common\models\EcomIntegrationConfig;
use common\models\edi\EdiOrganization;
use common\models\edi\EdiProvider;
use common\models\ES\Product;
use common\models\ES\Supplier;
use common\models\RelationUserOrganization;
use common\models\Role;
use Yii;
use yii\console\Controller;
use common\models\WhiteList;
use common\models\CatalogBaseGoods;
use common\models\Organization;
use api_web\components\Notice;
use common\models\User;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CronController extends Controller
{
    /**
     * Отправка Емайлов пользователем, кто у нас ровно неделю
     *
     * @throws \yii\base\ErrorException
     */
    public function actionSendEmailWeekend()
    {
        $users = User::find()->where([
            'status'            => 1,
            'send_week_message' => 0,
            'language'          => 'ru'
        ])
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
     *
     * @throws \yii\base\ErrorException
     */
    public function actionSendMessageManager()
    {
        $users = User::find()->where([
            'status'               => 1,
            'send_manager_message' => 0,
            'language'             => 'ru'
        ])
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
     *
     * @throws \yii\base\ErrorException
     */
    public function actionSendDemonstration()
    {
        $users = User::find()->where([
            'status'            => 1,
            'send_demo_message' => 0,
            'language'          => 'ru'
        ])
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

    /**
     * @throws \yii\db\Exception
     */
    public function actionCount()
    {
        $restaurants = rand(15, 25);
        $suppliers = rand(5, 10);

        \Yii::$app->db->createCommand(
            "UPDATE main_counter SET "
            . "supp_count = supp_count + {$suppliers}, "
            . "rest_count = rest_count + {$restaurants}"
        )->execute();
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function actionPlusOne()
    {
        $latest = Yii::$app->db->createCommand(
            "SELECT updated_at FROM main_counter LIMIT 1"
        )->queryScalar();

        $now = new \DateTime();
        $latest = new \DateTime($latest);
        $interval = $now->diff($latest, true)->i;
        echo "latest:" . Yii::$app->formatter->asTime($latest, "php:j M Y, H:i:s")
            . "; now:" . Yii::$app->formatter->asTime($now, "php:j M Y, H:i:s")
            . "; diff:" . $interval . "\n";
    }

    /**
     * обновление одного продукта (крон запускается каждые 2 минуты)
     *
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     * @throws \yii\elasticsearch\Exception
     */
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
                $product_category_name = \common\models\MpCategory::find()
                    ->where(['id' => $catalogBaseGoods->category->parent])
                    ->one()
                    ->name;
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

                    if (Product::find()->where(['product_id' => $product_id])->exists()) {

                        $es_product = Product::find()
                            ->where(['product_id' => $product_id])
                            ->one();
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
                        $es_product = new Product();
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
                    if (Product::find()->where(['product_id' => $product_id])->exists()) {
                        $es_product = Product::find()->where(['product_id' => $product_id])->one();
                        $es_product->delete();
                    }
                    $catalogBaseGoods->es_status = 0;
                    $catalogBaseGoods->save(false);
                }
            } catch (\Exception $e) {
                echo($e->getMessage());
                if (Product::find()->where(['product_id' => $catalogBaseGoods->id])->exists()) {
                    $es_product = Product::find()->where(['product_id' => $product_id])->one();
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

    /**
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     * @throws \yii\elasticsearch\Exception
     */
    public function actionUpdateSuppliers()
    {
        $suppliers = Organization::find()
            ->where([
                'type_id'    => Organization::TYPE_SUPPLIER,
                'white_list' => Organization::WHITE_LIST_ON
            ])
            ->andWhere(['in', 'es_status', [
                Organization::ES_UPDATED,
                Organization::ES_DELETED
            ]])
            ->andWhere('locality IS NOT NULL AND locality <> \'undefined\'')
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
                if (Supplier::find()->where(['supplier_id' => $supplier->id])->count() == 0) {
                    $es_supplier = new Supplier();
                    $es_supplier->attributes = [
                        "supplier_id"          => $supplier->id,
                        "supplier_image"       => !empty($supplier->picture) ? $supplier->pictureUrl : '',
                        "supplier_name"        => $supplier->name,
                        "supplier_rating"      => $rating,
                        "supplier_partnership" => $supplier->partnership
                    ];
                    $es_supplier->save();
                }
                if (Supplier::find()->where(['supplier_id' => $supplier->id])->count() > 0) {
                    $es_supplier = Supplier::find()->where(['supplier_id' => $supplier->id])->one();
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
                if (Supplier::find()->where(['supplier_id' => $supplier->id])->count() > 0) {
                    Supplier::findOne(['supplier_id' => $supplier->id])->delete();
                }
                CatalogBaseGoods::updateAll(["es_status" => 2], ["supp_org_id" => $supplier->id]);
            }
            Organization::updateAll(["es_status" => 0, "rating" => $rating], ["id" => $supplier->id]);

            if ($supplier->white_list == 1) {
                CatalogBaseGoods::updateAll([
                    "es_status" => CatalogBaseGoods::ES_UPDATE
                ], [
                    "AND",
                    ["supp_org_id1" => $supplier->id],
                    ["<>", "es_status", CatalogBaseGoods::ES_DELETED]
                ]);
            }
        }
    }

    public function actionMappingOrganizationFromGoogleApiMaps()
    {
        $model = Organization::find()
            ->where('lng IS NOT NULL AND lat IS NOT NULL AND country IS NOT NULL AND administrative_area_level_1 IS NULL')
            ->limit(500)
            ->all();

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

                $organization = Organization::findOne($s->id);
                $organization->administrative_area_level_1 = $location['admin_1'];
                $organization->save();
            }
        }
    }

    public function actionUpdateBlacklist()
    {
        Organization::updateAll(["blacklisted" => true], "blacklisted = 0 AND (name LIKE '%test%' OR name LIKE '%тест%')");
    }

    //handle EDI integration files
    public function actionHandleFiles()
    {
        $ediOrganizations = EdiOrganization::find()->where(['!=', 'login', ''])->all();
        if ($ediOrganizations) {
            foreach ($ediOrganizations as $org) {
                $provider = EdiProvider::findOne(['id' => $org->provider_id]);
                $providerClass = $provider->provider_class;
                $orgTypeId = $org->organization->type_id;

                if ($providerClass == 'LeradataProvider' && $orgTypeId == Organization::TYPE_SUPPLIER) {
                    continue;
                }

                (new EDIIntegration([
                    'orgId'      => $org->organization_id,
                    'providerID' => $org->provider_id
                ]))->handleFilesList();
            }
        }
    }

    //handle EDI integration files queue
    public function actionHandleFilesQueue()
    {
        $ediOrganizations = EdiOrganization::find()->all();
        if ($ediOrganizations) {
            foreach ($ediOrganizations as $org) {
                $provider = EdiProvider::findOne(['id' => $org->provider_id]);
                $providerClass = $provider->provider_class;
                $orgTypeId = $org->organization->type_id;

                if ($providerClass == 'LeradataProvider' && $orgTypeId == Organization::TYPE_SUPPLIER) {
                    continue;
                }

                (new EDIIntegration([
                    'orgId'      => $org->organization_id,
                    'providerID' => $org->provider_id
                ]))->handleFilesListQueue();
            }
        }
    }

    public function actionProcessMercVsd()
    {
        $query = (new Query())
            ->select([
                "vsd_count"       => new Expression("count(mvsd.id)"),
                "organization_id" => "mpconst.org"
            ])
            ->from(["mvsd" => MercVsd::tableName()])
            ->leftJoin([
                "mpconst" => mercPconst::tableName()
            ], "mvsd.recipient_guid = mpconst.value AND mpconst.const_id = 10")
            ->where([
                "mvsd.status" => "CONFIRMED"
            ])
            ->groupBy("mpconst.org")
            ->all(\Yii::$app->db_api);

        $organizations = ArrayHelper::map($query, 'organization_id', 'vsd_count');

        foreach ($organizations as $organization_id => $vsd_count) {
            $organization = Organization::findOne(['id' => $organization_id]);
            if (isset($organization)) {
                $recipients = [];
                $relatedUsers = RelationUserOrganization::findAll([
                    'organization_id' => $organization_id,
                    'is_active'       => true,
                    'role_id'         => [
                        Role::ROLE_RESTAURANT_MANAGER,
                        Role::ROLE_SUPPLIER_MANAGER,
                        Role::ROLE_ADMIN,
                        Role::ROLE_FKEEPER_MANAGER,
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
                foreach ($recipients as $recipient) {
                    Yii::$app->mailer->htmlLayout = '@common/mail/layouts/mail';
                    $mailer = Yii::$app->mailer;
                    $subject = Yii::t('app', 'common.mail.merc_vsd.subject', [
                            'ru' => 'Уведомление о непогашенных ВСД для'
                        ], 'ru') . '  ' . $organization->name;
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
        $organizations = (new Query())
            ->select([
                "organization_id" => "mpconst.org"
            ])
            ->from(["stock" => MercStockEntry::tableName()])
            ->leftJoin([
                "mpconst" => mercPconst::tableName()
            ], new Expression("stock.owner_guid COLLATE utf8_unicode_ci = mpconst.value COLLATE utf8_unicode_ci AND mpconst.const_id = 10"))
            ->where("stock.expiry_date < :expiry_date", [
                ":expiry_date" => new Expression("NOW()")
            ])
            ->groupBy("mpconst.org")
            ->column(\Yii::$app->db_api);

        foreach ($organizations as $organization_id) {
            $organization = Organization::findOne(['id' => $organization_id]);
            if (isset($organization)) {
                $recipients = [];
                $relatedUsers = RelationUserOrganization::findAll([
                    'organization_id' => $organization_id,
                    'is_active'       => true,
                    'role_id'         => [
                        Role::ROLE_RESTAURANT_MANAGER,
                        Role::ROLE_SUPPLIER_MANAGER,
                        Role::ROLE_ADMIN,
                        Role::ROLE_FKEEPER_MANAGER,
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
