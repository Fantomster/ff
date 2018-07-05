<?php

namespace common\models;

use api\common\models\iiko\iikoService;
use api\common\models\one_s\OneSService;
use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercService;
use api\common\models\merc\MercVsd;
use api\common\models\RkServicedata;
use backend\controllers\OdinsobshController;
use Mpdf\Tag\Q;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use common\behaviors\ImageUploadBehavior;
use Imagine\Image\ManipulatorInterface;
use common\models\guides\Guide;

/**
 * This is the model class for table "organization".
 *
 * @property integer $id
 * @property integer $type_id
 * @property string $name
 * @property string $city
 * @property string $address
 * @property string $zip_code
 * @property string $phone
 * @property string $email
 * @property string $website
 * @property string $created_at
 * @property string $updated_at
 * @property string $legal_entity
 * @property string $contact_name
 * @property string $about
 * @property string $picture
 * @property string $es_status
 * @property bool $partnership
 * @property integer $rating
 * @property integer $allow_editing
 * @property integer $is_allowed_for_franchisee
 * @property integer $is_work
 * @property double $lat
 * @property double $lng
 * @property string $country
 * @property string $locality
 * @property string $route
 * @property string $street_number
 * @property string $place_id
 * @property string $administrative_area_level_1
 * @property string $formatted_address
 * @property string $franchisee_sorted
 * @property string $inn
 * @property integer $parent_id
 *
 * @property OrganizationType $type
 * @property Delivery $delivery
 * @property User $users
 * @property OrderChat $unreadMessages
 * @property OrderChat $unreadSystem
 * @property string $pictureUrl
 * @property RatingStars $ratingStars
 * @property RatingPercent $ratingPercent
 * @property BuisinessInfo $buisinessInfo
 * @property FranchiseeAssociate $franchiseeAssociate
 * @property RelationSuppRest $associates
 * @property integer $managersCount
 * @property integer $productsCount
 * @property Guide $favorite
 * @property Guide[] $guides
 * @property Catalog $baseCatalog
 */
class Organization extends \yii\db\ActiveRecord {

    const FRANCHISEE_SORTED = 1;
    const FRANCHISEE_UNSORTED = 2;
    const TYPE_RESTAURANT = 1;
    const TYPE_SUPPLIER = 2;
    const TYPE_FRANCHISEE = 3;
    const WHITE_LIST_OFF = 0;
    const WHITE_LIST_ON = 1;
    const STEP_OK = 0;
    const STEP_SET_INFO = 1;
    const STEP_ADD_VENDOR = 2; //restaurants only
    const STEP_ADD_CATALOG = 3; //vendors only
    const STEP_TUTORIAL = 4;
    const DEFAULT_AVATAR = '/images/rest-noavatar.gif';
    const DEFAULT_VENDOR_AVATAR = '/images/vendor-noavatar.gif';
    const DEFAULT_RESTAURANT_AVATAR = '/images/restaurant-noavatar.gif';
    const ES_INACTIVE = 0;
    const ES_UPDATED = 1;
    const ES_DELETED = 2;
    const MAX_RATING = 31;
    const STATUS_WHITELISTED = 0;
    const STATUS_BLACKISTED = 1;
    const STATUS_UNSORTED = 2;
    const RELATION_INVITED = 1; //есть связь с поставщиком invite_on
    const RELATION_INVITE_IN_PROGRESS = 2; //поставщику было отправлено приглашение, но поставщик еще не добавил этот ресторан
    const NO_AUTH_ADD_RELATION_AND_CATALOG = 3; //поставщик не авторизован // добавляем к базовому каталогу поставщика каталог ресторана и создаем связь
    const THIS_IS_RESTAURANT = 4; //email ресторана
    const NEW_VENDOR = 5; //нет в базе такого email
    const AUTH_SEND_INVITE = 6; //поставщик авторизован invite

    public $resourceCategory = 'org-picture';
    public $manager_ids;
    public $gln_code;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'organization';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            ['name', 'required', 'on' => ['complete', 'settings'], 'message' => Yii::t('app', 'Пожалуйста, напишите название вашей организации')],
            ['name', 'required', 'on' => 'invite', 'message' => Yii::t('app', 'Пожалуйста, напишите название организации')],
            ['type_id', 'required', 'on' => 'register', 'message' => Yii::t('app', 'Укажите, Вы покупаете или продаете?')],
            [['type_id'], 'required'],
            //[['name', 'city', 'address'], 'required', 'on' => 'complete'],
            [['address', 'place_id', 'lat', 'lng'], 'required', 'on' => ['complete', 'settings'], 'message' => Yii::t('app', 'Установите точку на карте, путем ввода адреса в поисковую строку.')],
            [['id', 'type_id', 'step', 'es_status', 'rating', 'franchisee_sorted', 'manager_id', 'blacklisted'], 'integer'],
            [['created_at', 'updated_at', 'white_list', 'partnership', 'inn'], 'safe'],
            [['name', 'inn', 'city', 'address', 'zip_code', 'phone', 'email', 'website', 'legal_entity', 'contact_name', 'country', 'locality', 'route', 'street_number', 'place_id', 'formatted_address', 'administrative_area_level_1'], 'string', 'max' => 255],
            [['gln_code'], 'integer', 'min' => 1000000000000, 'max' => 99999999999999999, 'tooSmall' => 'Too small value', 'tooBig' => 'To big value'],
            [['gln_code'], 'unique'],
            [['name', 'city', 'address', 'zip_code', 'phone', 'website', 'legal_entity', 'contact_name', 'about'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['phone'], \borales\extensions\phoneInput\PhoneInputValidator::className()],
            [['email'], 'email'],
            [['lat', 'lng'], 'number'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationType::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['gln_code'], 'exist', 'skipOnError' => true, 'targetClass' => EdiOrganization::className(), 'targetAttribute' => ['id' => 'organization_id']],
            [['picture'], 'image', 'extensions' => 'jpg, jpeg, gif, png', 'on' => 'settings'],
            [['is_allowed_for_franchisee', 'is_work'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
            [
                'class' => ImageUploadBehavior::className(),
                'attribute' => 'picture',
                'scenarios' => ['settings'],
                'path' => '@app/web/upload/temp',
                'url' => '/upload/temp',
                'thumbs' => [
                    'picture' => ['width' => 420, 'height' => 236, 'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND],
                ],
            ],
        ];
    }

    public function getRouteText() {
        return $this->route == 'undefined' ? '' : $this->route;
    }

    public function getStreetText() {
        return $this->street_number == 'undefined' ? '' : $this->street_number;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'type_id' => Yii::t('app', 'common.models.business_type', ['ru' => 'Тип бизнеса']),
            'name' => Yii::t('app', 'common.models.organization_name', ['ru' => 'Название организации']),
            'city' => Yii::t('app', 'common.models.city_three', ['ru' => 'Город']),
            'address' => Yii::t('app', 'common.models.address', ['ru' => 'Адрес']),
            'zip_code' => Yii::t('app', 'common.models.index', ['ru' => 'Индекс']),
            'phone' => Yii::t('app', 'common.models.phone_three', ['ru' => 'Телефон']),
            'email' => Yii::t('app', 'common.models.org_email', ['ru' => 'Email организации']),
            'website' => Yii::t('app', 'common.models.web_site', ['ru' => 'Веб-сайт']),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'legal_entity' => Yii::t('app', 'common.models.jur_name_three', ['ru' => 'Название юридического лица']),
            'contact_name' => Yii::t('app', 'common.models.contact_name', ['ru' => 'ФИО контактного лица']),
            'about' => Yii::t('app', 'common.models.org_info', ['ru' => 'Информация об организации']),
            'picture' => Yii::t('app', 'common.models.avatar', ['ru' => 'Аватар']),
            'white_list' => Yii::t('app', 'common.models.accepted_for_f_market', ['ru' => 'Одобрено для f-market']),
            'partnership' => Yii::t('app', 'common.models.partnership', ['ru' => 'Партнерство']),
            'lat' => Yii::t('app', 'Lat'),
            'lng' => Yii::t('app', 'Lng'),
            'country' => Yii::t('app', 'common.models.country_four', ['ru' => 'Страна']),
            'administrative_area_level_1' => Yii::t('app', 'common.models.region_three', ['ru' => 'Область']),
            'locality' => Yii::t('app', 'common.models.city_four', ['ru' => 'Город']),
            'route' => Yii::t('app', 'common.models.city_five', ['ru' => 'Улица']),
            'street_number' => Yii::t('app', 'common.models.house', ['ru' => 'Дом']),
            'place_id' => Yii::t('app', 'Place ID'),
            'formatted_address' => Yii::t('app', 'Formatted Address'),
            'franchisee_sorted' => Yii::t('app', 'common.models.settled_franchisee', ['ru' => 'Назначен Франшизы']),
            'manager_id' => Yii::t('app', 'common.models.manager', ['ru' => 'Менеджер']),
            'cat_id' => Yii::t('app', 'common.models.catalogue', ['ru' => 'Каталог']),
            'is_allowed_for_franchisee' => Yii::t('app', 'common.models.let_franchisee', ['ru' => 'Разрешить франчайзи вход в данный Личный Кабинет']),
            'is_work' => Yii::t('app', 'common.models.is_work', ['ru' => 'Поставщик работает в системе']),
            'gln_code' => Yii::t('app', 'GLN-код'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelationUserOrganization() {
        return $this->hasMany(RelationUserOrganization::className(), ['organization_id' => 'id']);
    }

    public function getEdiOrganization(): ActiveQuery {
        return $this->hasOne(EdiOrganization::className(), ['organization_id' => 'id']);
    }

    public function getGlnCode() {
        return $this->ediOrganization->gln_code;
    }

    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            $this->es_status = Organization::ES_UPDATED;

            return true;
        }
        return false;
    }

    public static function get_value($id) {
        $model = Organization::find()->where(["id" => $id])->one();
        if (!empty($model)) {
            return $model;
        }
        return null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType() {
        return $this->hasOne(OrganizationType::className(), ['id' => 'type_id']);
    }

    /**
     * @return int|null
     */
    public function getAllow_editing() {
        if ($this->type_id != self::TYPE_SUPPLIER) {
            return null;
        }
        return abs($this->is_work - 1);
    }

    /**
     * get available categories for restaurant
     *
     * @return array
     */
    public function getRestaurantCategories() {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return [];
        }
        $categories = ArrayHelper::map(Category::find()
                                ->select(['id', 'name'])
                                ->orderBy(['name' => SORT_ASC])
                                ->asArray()
                                ->all(), 'id', 'name');
        $categories[''] = 'Все категории';
        ksort($categories);
        return $categories;
    }

    /**
     * get list of suppliers for selected categories
     *
     * @return array
     */
    public function getSuppliers($category_id = '', $all = true, $notMap=true) {
        if ($this->type_id !== Organization::TYPE_RESTAURANT && !$all) {
            return [];
        }
        $query = RelationSuppRest::find()
                ->select(['organization.id', 'organization.name'])
                ->leftJoin('organization', 'organization.id = relation_supp_rest.supp_org_id')
                ->leftJoin('relation_category', 'relation_category.supp_org_id = relation_supp_rest.supp_org_id');
//        if (!$all) {
        $query->where(['relation_supp_rest.rest_org_id' => $this->id]);
//        }
        $query->andWhere(['relation_supp_rest.deleted' => false]);
        if ($category_id) {
            $query = $query->andWhere(['relation_category.category_id' => $category_id]);
        }
        if($notMap){
            $vendors = ArrayHelper::map($query->orderBy(['organization.name' => SORT_ASC])
                ->asArray()
                ->all(), 'id', 'name');
        }else{
            $vendors = $query->orderBy(['organization.name' => SORT_ASC])
                ->asArray()
                ->all();
        }

        if ($all) {
            $vendors[''] = Yii::t('app', 'common.models.all_vendors', ['ru' => 'Все поставщики']);
        }
        ksort($vendors);
        return $vendors;
    }

    public function getSuppliersTorg12($category_id = '', $all = true, $notMap=true) {
        if ($this->type_id !== Organization::TYPE_RESTAURANT && !$all) {
            return [];
        }
        $query = RelationSuppRest::find()
            ->select(['organization.id', 'organization.name as text'])
            ->leftJoin('organization', 'organization.id = relation_supp_rest.supp_org_id')
            ->leftJoin('relation_category', 'relation_category.supp_org_id = relation_supp_rest.supp_org_id');
//        if (!$all) {
        $query->where(['relation_supp_rest.rest_org_id' => $this->id]);
//        }
        $query->andWhere(['relation_supp_rest.deleted' => false]);
        if ($category_id) {
            $query = $query->andWhere(['relation_category.category_id' => $category_id]);
        }
        if($notMap){
            $vendors = ArrayHelper::map($query->orderBy(['organization.name' => SORT_ASC])
                ->asArray()
                ->all(), 'id', 'name');
        }else{
            $vendors = $query->orderBy(['organization.name' => SORT_ASC])
                ->asArray()
                ->all();
        }

        if ($all) {
            $vendors[''] = Yii::t('app', 'common.models.all_vendors', ['ru' => 'Все поставщики']);
        }
        ksort($vendors);
        return $vendors;
    }

    /**
     * get list of clients
     *
     * @return array
     */
    public function getClients($all = true) {
        if ($this->type_id !== Organization::TYPE_SUPPLIER) {
            return [];
        }

        $query = RelationSuppRest::find()
                ->select(['organization.id as id', 'organization.name as name'])
                ->joinWith('client', false)
                ->where(['relation_supp_rest.supp_org_id' => $this->id])
                ->orderBy(['organization.name' => SORT_ASC]);

        $clients = ArrayHelper::map($query
                                ->asArray()
                                ->all(), 'id', 'name');

        if ($all) {
            $clients[''] = Yii::t('message', 'market.views.site.rest.all', ['ru' => 'Все рестораны']);
        }
        ksort($clients);
        return $clients;
    }

    /**
     * get base catalog
     */
    public function getBaseCatalog() {
        return Catalog::findOne(['supp_org_id' => $this->id, 'type' => Catalog::BASE_CATALOG]);
    }

    /**
     * @param null $vendor_id
     * @return string
     */
    public function getCatalogs($vendor_id = null) {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return '0';
        }
        //$vendor_id = (int)$vendor_id;
        $query = RelationSuppRest::find()
                ->select(['relation_supp_rest.cat_id as cat_id'])
                ->leftJoin('catalog', 'relation_supp_rest.cat_id = catalog.id')
                ->where(['relation_supp_rest.rest_org_id' => $this->id, 'relation_supp_rest.deleted' => false])
                ->andWhere(['catalog.status' => Catalog::STATUS_ON]);
        if ($vendor_id) {
            $query->andFilterWhere(['relation_supp_rest.supp_org_id' => $vendor_id]);
        }
        $catalogs = ArrayHelper::getColumn($query->asArray()->all(), 'cat_id');
        if (empty($catalogs)) {
            return '-1';
        }
        return implode(",", $catalogs);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDelivery() {
        if ($this->type_id !== Organization::TYPE_SUPPLIER) {
            return null;
        }
        return $this->hasOne(Delivery::className(), ['vendor_id' => 'id']);
    }

    /**
     * Список регионов доставки и исключения
     * @return array
     */
    public function getDeliveryRegionAsArray() {
        $result = [];

        if (isset($this->deliveryRegionsAllow)) {
            foreach ($this->deliveryRegionsAllow as $row) {
                $result['allow'][] = $row->attributes;
            }
        }
        if (isset($this->deliveryRegionsExclude)) {
            foreach ($this->deliveryRegionsExclude as $row) {
                $result['exclude'][] = $row->attributes;
            }
        }
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryRegionsAllow() {
        if ($this->type_id == Organization::TYPE_SUPPLIER) {
            return $this->hasMany(DeliveryRegions::className(), ['supplier_id' => 'id'])->andWhere(['exception' => 0]);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryRegionsExclude() {
        if ($this->type_id == Organization::TYPE_SUPPLIER) {
            return $this->hasMany(DeliveryRegions::className(), ['supplier_id' => 'id'])->andWhere(['exception' => 1]);
        }
    }

    /**
     * Метод возвращает корзину организации//пользователя
     * @return array|CartContent[]|mixed
     */
    public function _getCart() {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return [];
        }
        //Запрос
        $query = Cart::find()->where(['organization_id' => $this->id]);
        /**
         * Если включат индивидуальные настройки корзины
         * Сейчас тупо заглушка, если будет настрйка, нужно будет вписать
         */
        if (isset($individual_cart_enable)) {
            $query->andWhere(['user_id' => Yii::$app->user->id]);
        }

        //Получаем все корзины
        $carts = $query->all();

        if (empty($carts)) {
            return [];
        }

        //Собираем результаты, все строки с позициями
        $result = [];
        foreach ($carts as $cart) {
            $result = ArrayHelper::merge($result, $cart->cartContents);
        }

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCart() {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return [];
        }
        return Order::find()->where(['client_id' => $this->id, 'status' => Order::STATUS_FORMING])->all();
    }

    /**
     * @return integer
     */
    public function getCartCount() {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return 0;
        }
        return (new Query())->from('cart as c')
                        ->innerJoin('cart_content as cc', 'c.id = cc.cart_id')
                        ->andWhere(['c.organization_id' => $this->id])
                        ->count();
    }

    /*
     * @return integer
     */

    public function getNewOrdersCount($manager_id = null) {
        $result = 0;
        switch ($this->type_id) {
            case self::TYPE_RESTAURANT:
                $result = Order::find()->where([
                            'client_id' => $this->id,
                            'status' => [Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT]]
                        )->count();
                break;
            case self::TYPE_SUPPLIER:
                if (isset($manager_id)) {
                    $maTable = ManagerAssociate::tableName();
                    $orderTable = Order::tableName();
                    $result = Order::find()
                            ->leftJoin("$maTable", "$maTable.organization_id = `$orderTable`.client_id")
                            ->where([
                                'vendor_id' => $this->id,
                                "$maTable.manager_id" => $manager_id,
                                'status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]])
                            ->count();
                } else {
                    $result = Order::find()->where([
                                'vendor_id' => $this->id,
                                'status' => [Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT]]
                            )->count();
                }
                break;
        }
        return $result;
    }

    public function getNewClientCount($manager_id = null) {
        $result = 0;
        switch ($this->type_id) {
            case self::TYPE_RESTAURANT:
                $result = 0;
                break;
            case self::TYPE_SUPPLIER:
                $result = RelationSuppRest::find()->where([
                            'supp_org_id' => $this->id,
                            'invite' => [RelationSuppRest::INVITE_OFF]]
                        )->count();
                break;
        }
        return $result;
    }

    public function getEarliestOrderDate() {
        $today = new \DateTime();
        $result = $today->format('d.m.Y');
        switch ($this->type_id) {
            case self::TYPE_RESTAURANT:
                $firstOrder = Order::find()
                        ->where(['client_id' => $this->id])
                        ->orderBy(['created_at' => SORT_ASC])
                        ->limit(1)
                        ->one();
                break;
            case self::TYPE_SUPPLIER:
                $firstOrder = Order::find()
                        ->where(['vendor_id' => $this->id])
                        ->orderBy(['created_at' => SORT_ASC])
                        ->limit(1)
                        ->one();
                break;
        }
        if ($firstOrder) {
            $result = $firstOrder->created_at;
        }
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers() {
        $userTable = User::tableName();
        $relationTable = RelationUserOrganization::tableName();

        $query = User::find();
        $query->leftJoin($relationTable, "$relationTable.user_id = $userTable.id")
                ->where("$relationTable.organization_id = $this->id");
        $query->multiple = true;

        return $query;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdditionalEmail() {
        return $this->hasMany(AdditionalEmail::className(), ['organization_id' => 'id']);
    }

    /*
     * @return \yii\db\ActiveQuery
     */

    public function getUnreadMessages() {
        $roleId = Yii::$app->getUser()->identity->role->id;
        $userId = Yii::$app->user->id;

        if ($roleId == Role::ROLE_SUPPLIER_EMPLOYEE) {
            $sql = 'SELECT `order_chat`.*, ord.`client_id` AS vid, ma.`manager_id` FROM `order_chat` INNER JOIN '
                    . '(SELECT MIN(`order_chat`.`id`) as id, `order_chat`.`order_id` FROM `order_chat` '
                    . 'WHERE (`order_chat`.`recipient_id` = ' . $this->id . ') '
                    . 'AND ((`order_chat`.`is_system`=0) '
                    . 'AND (`order_chat`.`viewed`=0)) '
                    . 'GROUP BY `order_chat`.`order_id` ) as oc2 ON `order_chat`.`id` = oc2.`id` '
                    . 'LEFT JOIN `order` AS ord ON ord.`id` = `order_chat`.`order_id` '
                    . 'LEFT JOIN `manager_associate` AS ma ON ord.`client_id` = ma.`organization_id` '
                    . 'WHERE ma.`manager_id` = ' . $userId . ' '
                    . 'ORDER BY `order_chat`.`created_at` DESC';
        } else {
            $sql = 'SELECT `order_chat`.* FROM `order_chat` INNER JOIN '
                    . '(SELECT MIN(`order_chat`.`id`) as id, `order_chat`.`order_id` FROM `order_chat` '
                    . 'WHERE (`order_chat`.`recipient_id` = ' . $this->id . ') '
                    . 'AND ((`order_chat`.`is_system`=0) '
                    . 'AND (`order_chat`.`viewed`=0)) '
                    . 'GROUP BY `order_chat`.`order_id` ) as oc2 ON `order_chat`.`id` = oc2.`id`'
                    . 'ORDER BY `order_chat`.`created_at` DESC';
        }

        return OrderChat::findBySql($sql)->all();


//        return OrderChat::find()
//                ->leftJoin('order', 'order.id = order_chat.order_id')
//                ->where('(order.client_id=' . $this->id . ') OR (order.vendor_id=' . $this->id . ')')
//                ->andWhere(['order_chat.is_system' => 0, 'order_chat.viewed' => 0])
//                ->all();
    }

    /*
     * @return \yii\db\ActiveQuery
     */

    public function getUnreadNotifications() {
        $roleId = Yii::$app->getUser()->identity->role->id;
        $userId = Yii::$app->user->id;
        if ($roleId == Role::ROLE_SUPPLIER_EMPLOYEE) {
            $sql = 'SELECT `order_chat`.*, ord.`client_id` AS vid, ma.`manager_id` FROM `order_chat` INNER JOIN '
                    . '(SELECT MIN(`order_chat`.`id`) as id, `order_chat`.`order_id` FROM `order_chat` '
                    . 'WHERE (`order_chat`.`recipient_id` = ' . $this->id . ') '
                    . 'AND ((`order_chat`.`is_system`=1) '
                    . 'AND (`order_chat`.`viewed`=0)) '
                    . 'GROUP BY `order_chat`.`order_id` ) as oc2 ON `order_chat`.`id` = oc2.`id` '
                    . 'LEFT JOIN `order` AS ord ON ord.`id` = `order_chat`.`order_id` '
                    . 'LEFT JOIN `manager_associate` AS ma ON ord.`client_id` = ma.`organization_id` '
                    . 'WHERE ma.`manager_id` = ' . $userId . ' '
                    . 'ORDER BY `order_chat`.`created_at` DESC';
        } else {
            $sql = 'SELECT `order_chat`.* FROM `order_chat` INNER JOIN '
                    . '(SELECT MIN(`order_chat`.`id`) as id, `order_chat`.`order_id` FROM `order_chat` '
                    . 'WHERE (`order_chat`.`recipient_id` = ' . $this->id . ') '
                    . 'AND ((`order_chat`.`is_system`=1) '
                    . 'AND (`order_chat`.`viewed`=0)) '
                    . 'GROUP BY `order_chat`.`order_id` ) as oc2 ON `order_chat`.`id` = oc2.`id`'
                    . 'ORDER BY `order_chat`.`created_at` DESC';
        }
        return OrderChat::findBySql($sql)->all();
//        return OrderChat::find()
//                ->leftJoin('order', 'order.id = order_chat.order_id')
//                ->where('(order.client_id=' . $this->id . ') OR (order.vendor_id=' . $this->id . ')')
//                ->andWhere(['order_chat.is_system' => 1, 'order_chat.viewed' => 0])
//                ->all();
    }

    public function setMessagesRead() {
        $sql = "UPDATE `order_chat` SET `viewed` = 1 WHERE (`recipient_id`=$this->id) AND (`is_system`=0)";
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function setNotificationsRead() {
        $sql = "UPDATE `order_chat` SET `viewed` = 1 WHERE (`recipient_id`=$this->id) AND (`is_system`=1)";
        Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * @return array
     */
    public function getDisabledDeliveryDays() {
        $result = [];
        if ($this->type_id !== Organization::TYPE_SUPPLIER) {
            return $result;
        }
        $delivery = $this->delivery;
        if (!isset($delivery->sun) || !$delivery->sun) {
            $result[] = 0;
        }
        if (!isset($delivery->mon) || !$delivery->mon) {
            $result[] = 1;
        }
        if (!isset($delivery->tue) || !$delivery->tue) {
            $result[] = 2;
        }
        if (!isset($delivery->wed) || !$delivery->wed) {
            $result[] = 3;
        }
        if (!isset($delivery->thu) || !$delivery->thu) {
            $result[] = 4;
        }
        if (!isset($delivery->fri) || !$delivery->fri) {
            $result[] = 5;
        }
        if (!isset($delivery->sat) || !$delivery->sat) {
            $result[] = 6;
        }
        if (count($result) == 7) {
            $result = [];
        }
        return $result;
    }

    public function afterSave($insert, $changedAttributes) {
        if ($insert && ($this->type_id == self::TYPE_SUPPLIER)) {
            $delivery = new Delivery();
            $delivery->vendor_id = $this->id;
            $delivery->save();

            //Если нет главного каталога, создаем
//            if (!Catalog::find()->where(['supp_org_id' => $this->id, 'type' => 1])->exists()) {
//                $catalog = new Catalog([
//                    'type' => 1,
//                    'supp_org_id' => $this->id,
//                    'status' => 1,
//                    'created_at' => new \yii\db\Expression('NOW()'),
//                    'updated_at' => new \yii\db\Expression('NOW()'),
//                    'name' => Yii::t('app', 'Главный каталог'),
//                ]);
//                $catalog->save();
//            }
        }

        parent::afterSave($insert, $changedAttributes);
        //Определяем франча
        $this->setFranchise();

        if (!is_a(Yii::$app, 'yii\console\Application'))
            if (!$insert)
                \api\modules\v1\modules\mobile\components\notifications\NotificationOrganization::actionOrganization($this);
    }

    public function markViewed($orderId) {
        return OrderChat::updateAll(['viewed' => 1], ['order_id' => $orderId, 'recipient_id' => $this->id]);
    }

    public function getBuisinessInfo() {
        return $this->hasOne(BuisinessInfo::className(), ['organization_id' => 'id']);
    }

    public function getFranchiseeAssociate() {
        return $this->hasOne(FranchiseeAssociate::className(), ['organization_id' => 'id']);
    }

    public function getProfile() {
        return $this->hasOne(Profile::className(), ['user_id' => 'manager_id']);
    }

    public function getFranchisee() {
        return $this->hasOne(Franchisee::className(), ['id' => 'franchisee_id'])
                        ->viaTable('franchisee_associate', ['organization_id' => 'id']);
    }

    public function getFranchiseeManagerInfo() {
        $sql = 'SELECT `franchisee`.* FROM `organization` 
        JOIN `franchisee_associate` ON `organization`.id = `franchisee_associate`.`organization_id`
        JOIN `franchisee` ON `franchisee_associate`.`franchisee_id` = `franchisee`.`id` 
        WHERE `organization`.`id` = ' . $this->id;
        return Franchisee::findBySql($sql)->one();
    }

    /**
     * @return string url to avatar image
     */
    public function getPictureUrl() {
        if ($this->type_id == self::TYPE_SUPPLIER) {
            return $this->picture ? $this->getThumbUploadUrl('picture', 'picture') : Yii::$app->params['pictures']['vendor-noavatar'];
        }
        if ($this->type_id == self::TYPE_RESTAURANT) {
            return $this->picture ? $this->getThumbUploadUrl('picture', 'picture') : Yii::$app->params['pictures']['client-noavatar'];
        }
        return $this->picture ? $this->getThumbUploadUrl('picture', 'picture') : Yii::$app->params['pictures']['org-noavatar'];
    }

    public function inviteVendor($vendor, $invite, $includeBaseCatalog = false, $fromMarket = false) {
        if ($this->type_id !== self::TYPE_RESTAURANT) {
            return false;
        }

        $relation = new RelationSuppRest();
        $relation->supp_org_id = $vendor->id;
        $relation->rest_org_id = $this->id;
        $relation->invite = $invite;
        $relation->is_from_market = $fromMarket;
        $baseCatalog = Catalog::findOne(['supp_org_id' => $vendor->id, 'type' => Catalog::BASE_CATALOG]);
        if ($includeBaseCatalog && $baseCatalog) {
            $relation->cat_id = $baseCatalog;
        }

        $rows = User::find()->where(['organization_id' => $vendor->id, 'role_id' => Role::ROLE_SUPPLIER_MANAGER])->all();
        foreach ($rows as $row) {
            $managerAssociate = ManagerAssociate::findOne(['manager_id' => $row->id, 'organization_id' => $this->id]);
            if (!$managerAssociate) {
                $managerAssociate = new ManagerAssociate();
                $managerAssociate->manager_id = $row->id;
                $managerAssociate->organization_id = $this->id;
                $managerAssociate->save();
            }
        }
        return $relation->save();
    }

    public function getClientsCount() {
        if ($this->type_id === self::TYPE_RESTAURANT) {
            return 0;
        }
        return RelationSuppRest::find()->where(['supp_org_id' => $this->id, 'invite' => RelationSuppRest::INVITE_ON])->count();
    }

    public function getOrdersCount() {
        if ($this->type_id === self::TYPE_RESTAURANT) {
            return 0;
        }
        return Order::find()->where(['vendor_id' => $this->id, 'status' => Order::STATUS_DONE])->count();
    }

    public function getMarketGoodsCount() {
        if ($this->type_id === self::TYPE_RESTAURANT) {
            return 0;
        }
        return CatalogBaseGoods::find()
                        ->where([
                            'supp_org_id' => $this->id,
                            'deleted' => CatalogBaseGoods::DELETED_OFF,
                            'market_place' => CatalogBaseGoods::MARKETPLACE_ON])
                        ->groupBy(['category_id'])
                        ->count();
    }

    public function getRatingStars() {
        return number_format($this->rating / (self::MAX_RATING / 5), 1);
    }

    public function getRatingPercent() {
        return (($this->rating / (self::MAX_RATING / 5)) / 5 * 100);
    }

    public function getCatalogsList() {
        if ($this->type_id !== Organization::TYPE_SUPPLIER) {
            return [];
        }
        $catalogs = ArrayHelper::map(Catalog::find()
                                ->select(['id', 'name'])
                                ->where(['supp_org_id' => $this->id, 'status' => 1])
                                ->orderBy(['name' => SORT_ASC])
                                ->asArray()
                                ->all(), 'id', 'name');
        foreach ($catalogs as $id => &$catalog) {
            $catalogs[$id] = Yii::t('app', $catalog);
        }
        return $catalogs;
    }

    public function getManagersList() {
        $usrTable = User::tableName();
        $profTable = Profile::tableName();

        $managers = ArrayHelper::map(User::find()
                                ->joinWith('profile')
                                ->select(["$usrTable.id as id", "$profTable.full_name as name"])
                                ->where(["$usrTable.organization_id" => $this->id])
                                ->orderBy(['name' => SORT_ASC])
                                ->asArray()
                                ->all(), 'id', 'name');
        return $managers;
    }

    public function getAssociatedManagersList($vendor_id) {
        $usrTable = User::tableName();
        $profTable = Profile::tableName();
        $assocTable = ManagerAssociate::tableName();

        $managers = ArrayHelper::map(User::find()
                                ->joinWith('profile')
                                ->joinWith('associated')
                                ->select(["$usrTable.id as id", "$profTable.full_name as name"])
                                ->where(["$usrTable.organization_id" => $vendor_id, "$assocTable.organization_id" => $this->id])
                                ->orderBy(['name' => SORT_ASC])
                                ->asArray()
                                ->all(), 'id', 'name');
        return $managers;
    }

    public function getAssociatedManagers($vendor_id) {
        $usrTable = User::tableName();
        $assocTable = ManagerAssociate::tableName();
        $relationTable = RelationUserOrganization::tableName();

        return User::find()
                        ->leftJoin($assocTable, "$assocTable.manager_id = $usrTable.id")
                        ->leftJoin($relationTable, "$relationTable.user_id = $assocTable.manager_id")
                        ->where(["$assocTable.organization_id" => $this->id, "$relationTable.organization_id" => $vendor_id])
                        ->all();
    }

    public function hasActiveUsers() {
        return User::find()->where(['organization_id' => $this->id, 'status' => User::STATUS_ACTIVE])->count();
    }

    public function getManagersCount() {
        if ($this->type_id === Organization::TYPE_RESTAURANT) {
            return User::find()->where(['organization_id' => $this->id, 'role_id' => Role::ROLE_RESTAURANT_MANAGER])->count();
        }
        if ($this->type_id === Organization::TYPE_SUPPLIER) {
            return User::find()->where(['organization_id' => $this->id, 'role_id' => Role::ROLE_SUPPLIER_MANAGER])->count();
        }
        return 0;
    }

    public function getFavorite() {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return null;
        }
        return $this->hasOne(Guide::className(), ['client_id' => 'id', 'type' => Guide::TYPE_FAVORITE]);
    }

    public function getGuides() {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return [];
        }
        return $this->hasMany(Guide::className(), ['client_id' => 'id', 'type' => Guide::TYPE_GUIDE]);
    }

    /**
     * @return organization managers data provider
     */
    public function getOrganizationManagersDataProvider() {
        $usrTable = User::tableName();
        $profTable = Profile::tableName();
        $query = User::find()
                ->leftJoin("$profTable", "$profTable.user_id = $usrTable.id")
                ->select(["$usrTable.id as id", "$usrTable.email as email", "$profTable.full_name as name", "$profTable.phone"])
                ->where(["$usrTable.organization_id" => $this->id])
                ->orderBy(['name' => SORT_ASC]);
        $managersDataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'name',
                ],
            ],
        ]);
        return $managersDataProvider;
    }

    /**
     * @return organization data query
     */
    protected function getOrganizationQuery($organization_id, $type = 'supp', $currency_id = 1) {
        $type_id = ($type == 'supp') ? Organization::TYPE_SUPPLIER : Organization::TYPE_RESTAURANT;
        $prefix = ($type == 'rest') ? 'supp' : 'rest';
        $name = ($type == 'rest') ? 'client' : 'vendor';
        return "SELECT self_registered, org.id as id, org.name as name,
                org.created_at as created_at, org.contact_name as contact_name, org.phone as phone, (select count(id) from relation_supp_rest where " . $type . "_org_id=org.id) as clientCount, 
                (select count(id) from relation_supp_rest where " . $type . "_org_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY ) as clientCount_prev30, 
                (select count(id) from `order` where " . $name . "_id=org.id and status in (1,2,3,4)) as orderCount,
                (select count(id) from `order` where " . $name . "_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY ) as orderCount_prev30,
                (select sum(total_price) from `order` where " . $name . "_id=org.id and currency_id=$currency_id and status in (1,2,3,4)) as orderSum,
                (select sum(total_price) from `order` where " . $name . "_id=org.id and currency_id=$currency_id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY ) as orderSum_prev30
                FROM `relation_supp_rest` AS rel
                LEFT JOIN  `organization` AS org ON org.id = rel." . $type . "_org_id
                LEFT JOIN  `franchisee_associate` AS fa ON rel." . $type . "_org_id = fa.organization_id
                WHERE rel." . $prefix . "_org_id = " . $organization_id . " and org.type_id=" . $type_id;
    }

    public function sendGenerationPasswordEmail($user, $isFranchise = false) {
        $userToken = new UserToken();
        $userTokenType = $userToken::TYPE_PASSWORD_RESET;
        if ($userTokenType) {
            $userToken = $userToken::generate($user->id, $userTokenType);
        }
        $mailer = Yii::$app->mailer;
        $email = $user->email;
        $subject = Yii::$app->id . " - " . Yii::t('app', 'common.config.params.pass', ['ru' => 'Создание пароля для входа в систему MixCart']);
        $mailer->compose('changePassword', compact(['userToken', 'isFranchise']))
                ->setTo($email)
                ->setSubject($subject)
                ->send();
    }

    public function getAssociatedRequestsList($franchisee_id) {
        $search = ['like', 'product', \Yii::$app->request->get('search') ?: ''];
        $dataListRequest = new ActiveDataProvider([
            'query' => Request::find()->leftJoin('franchisee_associate', "franchisee_associate.organization_id = request.rest_org_id")->where(['franchisee_associate.franchisee_id' => $franchisee_id])->andWhere($search)->orderBy('request.id DESC'),
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);
        return $dataListRequest;
    }

    public function getClientsExportColumns() {
        return [
            [
                'label' => Yii::t('app', 'common.models.number_two', ['ru' => 'Номер']),
                'value' => 'id',
            ],
            [
                'label' => Yii::t('app', 'common.models.name', ['ru' => 'Название']),
                'value' => 'name',
            ],
            [
                'label' => Yii::t('app', 'common.models.amount_vendor', ['ru' => 'Кол-во поставщиков']),
                'value' => 'vendorCount',
            ],
            [
                'label' => Yii::t('app', 'common.models.orders_amount', ['ru' => 'Кол-во заказов']),
                'value' => 'orderCount',
            ],
            [
                'label' => Yii::t('app', 'common.models.order_sum', ['ru' => 'Сумма заказов']),
                'value' => 'orderSum',
            ],
            [
                'label' => Yii::t('app', 'common.models.reg_date', ['ru' => 'Дата регистрации']),
                'value' => 'created_at',
            ],
            [
                'label' => Yii::t('app', 'common.models.contact', ['ru' => 'Контакт']),
                'value' => 'contact_name',
            ],
            [
                'label' => Yii::t('app', 'common.models.phone_four', ['ru' => 'Телефон']),
                'value' => 'phone',
            ],
        ];
    }

    public function getVendorsExportColumns() {
        return [
            [
                'label' => Yii::t('app', 'common.models.number_three', ['ru' => 'Номер']),
                'value' => 'id',
            ],
            [
                'label' => Yii::t('app', 'common.models.name_four', ['ru' => 'Название']),
                'value' => 'name',
            ],
            [
                'label' => Yii::t('app', 'common.models.rest_amount', ['ru' => 'Кол-во ресторанов']),
                'value' => 'clientCount',
            ],
            [
                'label' => Yii::t('app', 'common.models.orders_amount_two', ['ru' => 'Кол-во заказов']),
                'value' => 'orderCount',
            ],
            [
                'label' => Yii::t('app', 'common.models.order_sum_two', ['ru' => 'Сумма заказов']),
                'value' => 'orderSum',
            ],
            [
                'label' => Yii::t('app', 'common.models.register_date', ['ru' => 'Дата регистрации']),
                'value' => 'created_at',
            ],
            [
                'label' => Yii::t('app', 'common.models.contact_two', ['ru' => 'Контакт']),
                'value' => 'contact_name',
            ],
            [
                'label' => Yii::t('app', 'common.models.phone_five', ['ru' => 'Телефон']),
                'value' => 'phone',
            ],
        ];
    }

    /**
     *
     * return count of products
     *
     * @return integer
     */
    public function getProductsCount() {
        if ($this->type_id !== self::TYPE_SUPPLIER) {
            return 0;
        }
        return CatalogBaseGoods::find()->where(['supp_org_id' => $this->id, 'status' => CatalogBaseGoods::STATUS_ON, 'deleted' => CatalogBaseGoods::DELETED_OFF])->count();
    }

    public function getAvailableProductsCount($clientId) {
        if ($this->type_id !== self::TYPE_SUPPLIER) {
            return 0;
        }
//        $count = CatalogGoods::find()
//                ->leftJoin('catalog', 'catalog_goods.cat_id=catalog.id')
//                ->leftJoin('relation_supp_rest', 'relation_supp_rest.cat_id=catalog_goods.cat_id')
//                ->leftJoin('catalog_base_goods', 'catalog_base_goods.id=catalog_goods.base_goods_id')
//                ->where([
//                    'relation_supp_rest.rest_org_id' => $clientId,
//                    'relation_supp_rest.supp_org_id' => $this->id,
//                    'catalog_base_goods.status' => CatalogBaseGoods::STATUS_ON,
//                    'catalog_base_goods.deleted' => CatalogBaseGoods::DELETED_OFF,
//                ])
//                ->count();
        $count = 0;
        $catalogs = Catalog::find()
                ->leftJoin('relation_supp_rest', 'relation_supp_rest.cat_id=catalog.id')
                ->where([
                    'relation_supp_rest.deleted' => false,
                    'relation_supp_rest.supp_org_id' => $this->id,
                    'relation_supp_rest.rest_org_id' => $clientId,
                ])
                ->all();
        foreach ($catalogs as $catalog) {
            if ($catalog->type === Catalog::BASE_CATALOG) {
                $count = CatalogBaseGoods::find()->where([
                            'cat_id' => $catalog->id,
                            'status' => CatalogBaseGoods::STATUS_ON,
                            'deleted' => CatalogBaseGoods::DELETED_OFF
                        ])->count();
            } else {
                $count += CatalogGoods::find()
                        ->leftJoin('catalog_base_goods', 'catalog_base_goods.id=catalog_goods.base_goods_id')
                        ->where([
                            'catalog_goods.cat_id' => $catalog->id,
                            'catalog_base_goods.status' => CatalogBaseGoods::STATUS_ON,
                            'catalog_base_goods.deleted' => CatalogBaseGoods::DELETED_OFF,
                        ])
                        ->count();
            }
        }
        return $count;
    }

    /**
     * return product if it is available to client
     *
     * @return CatalogBaseGoods
     */
    public function getProductIfAvailable($product_id) {
        if ($this->type_id !== self::TYPE_RESTAURANT) {
            return null;
        }

        $cgTable = CatalogGoods::tableName();
        $cbgTable = CatalogBaseGoods::tableName();
        $orgTable = Organization::tableName();
        $rsrTable = RelationSuppRest::tableName();
        $catTable = Catalog::tableName();

        $product = CatalogGoods::find()
                ->leftJoin($cbgTable, "$cbgTable.id = $cgTable.base_goods_id")
                ->leftJoin($orgTable, "$orgTable.id = $cbgTable.supp_org_id")
                ->leftJoin($rsrTable, "$rsrTable.cat_id = $cgTable.cat_id")
                ->leftJoin($catTable, "$catTable.id = $rsrTable.cat_id")
                ->where([
                    "$rsrTable.deleted" => false,
                    "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF,
                    "$cbgTable.status" => CatalogBaseGoods::STATUS_ON,
                    "$rsrTable.rest_org_id" => $this->id,
                    "$catTable.status" => Catalog::STATUS_ON,
                    "$cbgTable.id" => $product_id,
                ])
                ->one();
        if ($product) {
            return CatalogBaseGoods::findOne(['id' => $product_id]);
        }
        $product = CatalogBaseGoods::find()
                ->leftJoin($orgTable, "$orgTable.id = $cbgTable.supp_org_id")
                ->leftJoin($rsrTable, "$rsrTable.cat_id = $cbgTable.cat_id")
                ->leftJoin($catTable, "$catTable.id = $rsrTable.cat_id")
                ->where([
                    "$rsrTable.deleted" => false,
                    "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF,
                    "$cbgTable.status" => CatalogBaseGoods::STATUS_ON,
                    "$rsrTable.rest_org_id" => $this->id,
                    "$catTable.status" => Catalog::STATUS_ON,
                    "$cbgTable.id" => $product_id,
                ])
                ->one();
        if ($product) {
            return $product;
        }
        return null;
    }

    /**
     * Прикрепление организации к франчази
     * @param bool $delete_assoc удаление всех связей с франчайзи
     * @param bool $cancel_sorted удаление признака привязки к франчу
     */
    public function setFranchise($delete_assoc = false, $cancel_sorted = false) {
        /*         * *******Начальная проверка START**************************************** */
        //Если пустая страна, даже не будем ее никуда цеплять
        //При заполнении адреса они снова попадут сюда
        if (empty($this->country)) {
            return;
        }
        //Передавая этот флаг, можем перекрепить организацию
        if ($delete_assoc === true) {
            if (FranchiseeAssociate::find()->where(['organization_id' => $this->id])->exists()) {
                //Удаляем все связи
                Yii::$app->db->createCommand()
                        ->delete(FranchiseeAssociate::tableName(), ['organization_id' => $this->id])
                        ->execute();
            }
        }
        //Этот флаг снимает признак того что организация отсортирована
        if ($cancel_sorted === true) {
            //Ставим признак то что не отсортирован
            Yii::$app->db->createCommand()
                    ->update(self::tableName(), ['franchisee_sorted' => 0], ['id' => $this->id])
                    ->execute();
            $this->refresh();
        }
        //Проводим прикрепление только для неотсортированых организаций c адресом
        if ($this->franchisee_sorted === 1) {
            return;
        }
        /*         * *******Начальная проверка END****************************************** */

        //Если организация уже привязана
        if (FranchiseeAssociate::find()->where(['organization_id' => $this->id])->exists()) {
            Yii::$app->db->createCommand()
                    ->update(self::tableName(), ['franchisee_sorted' => 1], ['id' => $this->id])
                    ->execute();
        } else {
            //Есть, уже есть шанс что к кому то ее прилепим
            //Франчази по умолчанию
            $franchise = null;
            $default_id = 1;
            if (isset(Yii::$app->params['default_franchisee_id'])) {
                //Берем id из параметров
                $default_id = (integer) Yii::$app->params['default_franchisee_id'];
            }
            //Есть ли франшиза в стране организации
            if (FranchiseeGeo::find()->where(['country' => $this->country])->exists()) {
                //Поля для получения
                $fields = [
                    'franchisee_id',
                    'franchisee.type_id',
                    'exception',
                    'administrative_area_level_1',
                    'locality',
                    'franchisee.legal_email',
                    'franchisee.receiving_organization'
                ];
                //Поиск франчей в городе организации
                $franchise = FranchiseeGeo::find()->asArray()
                                ->select($fields)
                                ->leftJoin('franchisee', 'franchisee.id = franchisee_id')
                                ->where(['country' => $this->country, 'locality' => $this->locality])
                                ->andWhere('LENGTH(locality) > 2')->all();

                if (!$franchise) {
                    //Если не нашли франчей в этом городе, ищем в области
                    $franchise = FranchiseeGeo::find()->asArray()
                                    ->select($fields)
                                    ->leftJoin('franchisee', 'franchisee.id = franchisee_id')
                                    ->where([
                                        'country' => $this->country,
                                        'administrative_area_level_1' => $this->administrative_area_level_1
                                    ])->andWhere('LENGTH(administrative_area_level_1) > 2')->all();

                    if (!$franchise) {
                        //Если же не нашли даже в области, ищем в стране
                        $franchise = FranchiseeGeo::find()->asArray()
                                        ->select($fields)
                                        ->leftJoin('franchisee', 'franchisee.id = franchisee_id')
                                        ->where(['country' => $this->country])
                                        ->andWhere("locality ='' or locality is null")
                                        ->andWhere("administrative_area_level_1 ='' or administrative_area_level_1 is null")->all();
                    }
                }
            }
            //Если кого то нашли, крепим к нему, если нет, к франчу из параметров
            if ($this->setTypeFranchiseeAndSaveAssoc($franchise) === false) {
                //Создаем новую связь
                $associate = new FranchiseeAssociate([
                    'franchisee_id' => $default_id,
                    'organization_id' => $this->id,
                    'self_registered' => FranchiseeAssociate::SELF_REGISTERED
                ]);
                //Схраняем к дефолтному, и ставим знак что франч не отсортирован
                if ($associate->save()) {
                    Yii::$app->db->createCommand()
                            ->update(self::tableName(), ['franchisee_sorted' => 0], ['id' => $this->id])
                            ->execute();
                    //Обновляем атрибуты модели
                    $this->refresh();
                }
            }
        }
    }

    /**
     * @param $franchise_pull [
     *              [
     *                  'franchisee_id',
     *                  'franchisee.type_id',
     *                  'exception',
     *                  'administrative_area_level_1',
     *                  'locality',
     *                  'franchisee.legal_email',
     *                  'franchisee.receiving_organization'
     *              ], ... ]
     * @return bool
     */
    private function setTypeFranchiseeAndSaveAssoc($franchise_pull) {
        //Если нет франчей возвращаем false
        if (empty($franchise_pull) or is_null($franchise_pull)) {
            return false;
        }
        //Умолчание
        $franchise = null;
        $result = [];
        //Формируем массив франчей по рангу
        foreach ($franchise_pull as $f) {
            if ($f['exception'] == 1) {
                if (
                        $f['administrative_area_level_1'] == $this->administrative_area_level_1 ||
                        $f['locality'] == $this->locality
                ) {
                    continue;
                }
            }
            $result[$f['type_id']][] = $f;
        }
        //Если никого не нашли возвращаем false, организация прилипнет к франчу по умолчанию
        //Франч по умолчанию установлен в параметрах
        if (empty($result)) {
            return false;
        }
        //Сортируем по группам приоритетов 3,2,1
        krsort($result);
        //Сначала проставим колличество получаемых организаций - всем выбранным франчам
        //У кого еще небыло попыток, только null
        $result = $this->setReceivingOrganization($result);
        //Получаем подходящего франча
        $franchise = $this->getFranchiseeReceivingOrganization($result);
        //Если никого не нашлось, значит у всех кончились попытки получения
        if ($franchise === null) {
            //Обновляем всем попытки принудительно
            $result = $this->setReceivingOrganization($result, true);
            //Получаем подходящего франча, еще раз, так как обновили поля
            $franchise = $this->getFranchiseeReceivingOrganization($result);
            //Если и сейчас никого нет, отправляем к дефолту
            //но сюда доходить не должно :)
            if ($franchise === null) {
                return false;
            }
        }
        //Создаем связь организации с франчем
        $associate = new FranchiseeAssociate([
            'franchisee_id' => $franchise['franchisee_id'],
            'organization_id' => $this->id,
            'self_registered' => FranchiseeAssociate::SELF_REGISTERED
        ]);
        if ($associate->save()) {
            //После сохранения, у франча - уменьшаем количество попыток
            Yii::$app->db->createCommand()->update(
                    Franchisee::tableName(), ['receiving_organization' => ($franchise['receiving_organization'] - 1)], ['id' => $franchise['franchisee_id']]
            )->execute();
            //Ставим флаг что припарковали организацию
            Yii::$app->db->createCommand()->update(
                    self::tableName(), ['franchisee_sorted' => 1], ['id' => $this->id]
            )->execute();
            //Отправлем емайл франчу, если есть адрес почты
            if (!empty($franchise['legal_email'])) {
                $url = Yii::$app->params['protocol'] . ":" . Yii::$app->params['franchiseeHost'] . "/organization/show-";
                $url .= ($this->type_id == Organization::TYPE_RESTAURANT ? 'client' : 'vendor');
                $message = Yii::$app->mailer;
                $message->compose('franchiseeAssociateAdded', ["organization" => $this, "route" => $url . '/' . $this->id])
                        ->setTo($franchise['legal_email'])
                        ->setSubject(Yii::t('app', 'common.models.self_registered', ['ru' => 'Самостоятельно зарегистрировавшаяся организация добавлена во франчайзи']))
                        ->send();
            }
        }
        //Обновляем атрибуты модели организации
        //Так как были изменения напрямую в БД
        $this->refresh();
        return true;
    }

    /**
     * Получение следующего по очереди франча
     * @param $result
     * @return mixed
     */
    private function getFranchiseeReceivingOrganization($result) {
        $return = null;
        //Ищем следующего по очереди
        foreach ($result as &$franchiseeTypeArray) {
            foreach ($franchiseeTypeArray as &$franchisee) {
                //Вернем франча только если он не NULL и не 0
                if (!empty($franchisee['receiving_organization']) and $franchisee['receiving_organization'] !== 0) {
                    $return = $franchisee;
                    break;
                }
            }
            if ($return !== null) {
                break;
            }
        }
        return $return;
    }

    /**
     * Расставляем коэффициент получения, илил принудительно обновляем его
     * @param $result
     * @param bool $p - принудительно обновление
     * @return array
     */
    private function setReceivingOrganization($result, $p = false) {
        //Максимальное число попыток = числу типов франчей в выборке
        $receiving_organization = count($result);
        foreach ($result as &$franchiseeTypeArray) {
            foreach ($franchiseeTypeArray as &$franchisee) {
                //Обновляем число попыток, если оно NULL или стоит принудидетьльное обновление
                if ($franchisee['receiving_organization'] === null or $p === true) {
                    Yii::$app->db->createCommand()->update(
                            Franchisee::tableName(), ['receiving_organization' => $receiving_organization], ['id' => $franchisee['franchisee_id']]
                    )->execute();
                    //Обновляем значение, так как работаем с сылкой, и возвращаем тот же массив, зачем жрать память
                    $franchisee['receiving_organization'] = $receiving_organization;
                }
            }
            $receiving_organization = $receiving_organization - 1;
        }
        return $result;
    }

    public function getPayments() {
        return $this->hasMany(Payment::className(), ['organization_id' => 'id'])->orderBy('payment.payment_id DESC');
    }

    public function isEmpty() {
        return (empty($this->name) || empty($this->place_id));
    }

    /**
     * @return array
     */
    public function integrationOnly() {
        $return = [];

        $lic = \api\common\models\RkServicedata::find()->andWhere('org = :org', ['org' => $this->id])->one();
        $t = strtotime(date('Y-m-d H:i:s', time()));
        if ($lic) {
            if ($t >= strtotime($lic->fd) && $t <= strtotime($lic->td) && $lic->status_id === 1) {
                $return['rk'] = true;
            }
        }

        if (!empty(iikoService::getLicense())) {
            $return['iiko'] = true;
        }

        return $return;
    }

    public function getLicenseList() {
        $result = [];
        $lic = RkServicedata::getLicense();
        if ($lic != null) {
            $result['rkws'] = $lic;
            $org = $lic['service_id'];
            $lic_ucs = RkServicedata::getLicenseUcs($org);
            $result['rkws_ucs'] = $lic_ucs;
        }

        $lic = iikoService::getLicense();
        if ($lic != null)
            $result['iiko'] = $lic;

        $lic = mercService::getLicense();
        if ($lic != null)
            $result['mercury'] = $lic;

        $lic = OneSService::getLicense();
        if ($lic != null)
            $result['odinsobsh'] = $lic;

        return $result;
    }

    /**
     * @return integer
     */
    public function getVsdCount() {
        $lic = mercService::getLicense();
        if ($lic == null)
            return 0;

        try {
            $guid = mercDicconst::getSetting('enterprise_guid');
            return MercVsd::find()->where(['recipient_guid' => $guid, 'status' => 'CONFIRMED'])->count();
        }catch (\Exception $e)
        {
            return 0;
        }
    }

    public function getOrganizationManagersExportColumns(): array {
        return [
            [
                'label' => Yii::t('app', 'common.models.number', ['ru' => 'Номер']),
                'value' => 'id',
            ],
            [
                'label' => Yii::t('message', 'frontend.views.vendor.fio_two', ['ru' => 'ФИО']),
                'value' => 'profile.full_name',
            ],
            [
                'label' => Yii::t('app', 'franchise.views.organization.contact_email', ['ru' => 'Email контактного лица']),
                'value' => 'email',
            ],
            [
                'label' => Yii::t('app', 'common.models.phone_two', ['ru' => 'Телефон']),
                'value' => 'profile.phone',
            ],
        ];
    }

    public function getStatus() {

        switch ($this->blacklisted) {
            case self::STATUS_WHITELISTED:
                $result = 'Разрешено';
                break;
            case self::STATUS_BLACKISTED:
                $result = 'Заблокировано';
                break;
            case self::STATUS_UNSORTED:
                $result = 'Неотсортировано';
                break;
        }
        return $result;
    }

    public static function getStatusList() {
        return [
            self::STATUS_WHITELISTED => 'Разрешено',
            self::STATUS_BLACKISTED => 'Заблокировано',
            self::STATUS_UNSORTED => 'Неотсортировано',
        ];
    }

    /**
     * Temporary. To be removed after business rework.
     */
    public function setPrimary() {
        if (empty($this->parent_id)) {
            return true;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            self::updateAll(['parent_id' => $this->id], ['parent_id' => $this->parent_id]);
            $formerParent = self::findOne(['id' => $this->parent_id]);
            $formerParent->parent_id = $this->id;
            $this->parent_id = null;
            if ($formerParent->save() && $this->save()) {
                $transaction->commit();
                return true;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new Exception($e->getMessage(), $e->getCode());
        }
        return false;
    }

    public function wipeBusiness() {
        RelationUserOrganization::deleteAll(['organization_id' => $this->id]);
        $this->blacklisted = true;
        $this->parent_id = null;
        return $this->save();
    }
}
