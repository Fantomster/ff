<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
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
 * @property Guide $favorite
 * @property Guide[] $guides
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

    public $resourceCategory = 'org-picture';
    public $manager_ids;

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
            ['name', 'required', 'on' => ['complete', 'settings'], 'message' => 'Пожалуйста, напишите название вашей организации'],
            ['name', 'required', 'on' => 'invite', 'message' => 'Пожалуйста, напишите название организации'],
            ['type_id', 'required', 'on' => 'register', 'message' => 'Укажите, Вы покупаете или продаете?'],
            [['type_id'], 'required'],
            //[['name', 'city', 'address'], 'required', 'on' => 'complete'],
            [['address','place_id','lat','lng'], 'required', 'on' => ['complete', 'settings'],'message' => 'Установите точку на карте, путем ввода адреса в поисковую строку.'],
            [['id', 'type_id', 'step', 'es_status', 'rating', 'franchisee_sorted'], 'integer'],
            [['created_at', 'updated_at', 'white_list', 'partnership'], 'safe'],
            [['name', 'city', 'address', 'zip_code', 'phone', 'email', 'website', 'legal_entity', 'contact_name', 'country', 'locality', 'route', 'street_number', 'place_id', 'formatted_address','administrative_area_level_1'], 'string', 'max' => 255],
            [['name', 'city', 'address', 'zip_code', 'phone', 'website', 'legal_entity', 'contact_name', 'about'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['phone'], \borales\extensions\phoneInput\PhoneInputValidator::className()],
            [['email'], 'email'],
            [['lat', 'lng'], 'number'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationType::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['picture'], 'image', 'extensions' => 'jpg, jpeg, gif, png', 'on' => 'settings'],
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
                'path' => '@app/web/upload/temp/',
                'url' => '/upload/temp/',
                'thumbs' => [
                    'picture' => ['width' => 420, 'height' => 236, 'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'type_id' => 'Тип бизнеса',
            'name' => 'Название организации',
            'city' => 'Город',
            'address' => 'Адрес',
            'zip_code' => 'Индекс',
            'phone' => 'Телефон',
            'email' => 'Email организации',
            'website' => 'Веб-сайт',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'legal_entity' => 'Название юридического лица',
            'contact_name' => 'ФИО контактного лица',
            'about' => 'Информация об организации',
            'picture' => 'Аватар',
            'white_list' => 'Одобрено для f-market',
            'partnership' => 'Партнерство',
            'lat' => 'Lat',
            'lng' => 'Lng',
            'country' => 'Страна',
            'administrative_area_level_1' => 'Область',
            'locality' => 'Город',
            'route' => 'Улица',
            'street_number' => 'Дом',
            'place_id' => 'Place ID',
            'formatted_address' => 'Formatted Address',
            'franchisee_sorted'=>'Назначен Франшизы'
        ];
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
    public function getSuppliers($category_id = '', $all = false) {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return [];
        }
        $query = RelationSuppRest::find()
                ->select(['organization.id', 'organization.name'])
                ->leftJoin('organization', 'organization.id = relation_supp_rest.supp_org_id')
                ->leftJoin('relation_category', 'relation_category.supp_org_id = relation_supp_rest.supp_org_id')
                ->where(['relation_supp_rest.rest_org_id' => $this->id, 'relation_supp_rest.deleted' => false]);
        if ($category_id) {
            $query = $query->andWhere(['relation_category.category_id' => $category_id]);
        }
        $vendors = ArrayHelper::map($query->orderBy(['organization.name' => SORT_ASC])
                                ->asArray()
                                ->all(), 'id', 'name');
        $vendors[''] = 'Все поставщики';
        ksort($vendors);
        return $vendors;
    }

    /**
     * get list of clients
     * 
     * @return array
     */
    public function getClients() {
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
        $clients[''] = 'Все рестораны';
        ksort($clients);
        return $clients;
    }

    /**
     *  get catalogs list for sqldataprovider for order creation
     *  
     *  @return string
     */
    public function getCatalogs($vendor_id) {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return '0';
        }
        //$vendor_id = (int)$vendor_id;
        $query = RelationSuppRest::find()
                ->select(['relation_supp_rest.cat_id as cat_id'])
                ->leftJoin('catalog', 'relation_supp_rest.cat_id = catalog.id')
                ->where(['relation_supp_rest.rest_org_id' => $this->id, 'relation_supp_rest.deleted' => false])
                ->andWhere(['catalog.status' => Catalog::STATUS_ON]);
        $query->andFilterWhere(['relation_supp_rest.supp_org_id' => $vendor_id]);
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
            return [];
        }
        return Order::find()->where(['client_id' => $this->id, 'status' => Order::STATUS_FORMING])->count();
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
        return $this->hasMany(User::className(), ['organization_id' => 'id']);
    }

    /*
     * @return \yii\db\ActiveQuery
     */

    public function getUnreadMessages() {

        $sql = 'SELECT `order_chat`.* FROM `order_chat` INNER JOIN '
                . '(SELECT MIN(`order_chat`.`id`) as id, `order_chat`.`order_id` FROM `order_chat` '
                . 'WHERE (`order_chat`.`recipient_id` = ' . $this->id . ') '
                . 'AND ((`order_chat`.`is_system`=0) '
                . 'AND (`order_chat`.`viewed`=0)) '
                . 'GROUP BY `order_chat`.`order_id` ) as oc2 ON `order_chat`.`id` = oc2.`id`'
                . 'ORDER BY `order_chat`.`created_at` DESC';

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
        $sql = 'SELECT `order_chat`.* FROM `order_chat` INNER JOIN '
                . '(SELECT MIN(`order_chat`.`id`) as id, `order_chat`.`order_id` FROM `order_chat` '
                . 'WHERE (`order_chat`.`recipient_id` = ' . $this->id . ') '
                . 'AND ((`order_chat`.`is_system`=1) '
                . 'AND (`order_chat`.`viewed`=0)) '
                . 'GROUP BY `order_chat`.`order_id` ) as oc2 ON `order_chat`.`id` = oc2.`id`'
                . 'ORDER BY `order_chat`.`created_at` DESC';

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
        }
        parent::afterSave($insert, $changedAttributes);
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
            return null;
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
    protected function getOrganizationQuery($organization_id, $type = 'supp'){
        $type_id = ($type=='supp') ? Organization::TYPE_SUPPLIER : Organization::TYPE_RESTAURANT;
        $prefix = ($type=='rest') ? 'supp' : 'rest';
        return "SELECT self_registered, org.id as id, org.name as name,
                org.created_at as created_at, org.contact_name as contact_name, org.phone as phone, (select count(id) from relation_supp_rest where ".$type."_org_id=org.id) as clientCount, 
                (select count(id) from relation_supp_rest where ".$type."_org_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY ) as clientCount_prev30, 
                (select count(id) from `order` where vendor_id=org.id and status in (1,2,3,4)) as orderCount,
                (select count(id) from `order` where vendor_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY ) as orderCount_prev30,
                (select sum(total_price) from `order` where vendor_id=org.id and status in (1,2,3,4)) as orderSum,
                (select sum(total_price) from `order` where vendor_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY ) as orderSum_prev30
                FROM `relation_supp_rest` AS rel
                LEFT JOIN  `organization` AS org ON org.id = rel.".$type."_org_id
                LEFT JOIN  `franchisee_associate` AS fa ON rel.".$type."_org_id = fa.organization_id
                WHERE rel.".$prefix."_org_id = ".$organization_id." and org.type_id=".$type_id;
    }


    public function sendGenerationPasswordEmail($user){
        $userToken = new UserToken();
        $userTokenType = $userToken::TYPE_PASSWORD_RESET;
        if ($userTokenType) {
            $userToken = $userToken::generate($user->id, $userTokenType);
        }
        $mailer = Yii::$app->mailer;
        $email = $user->email;
        $subject = Yii::$app->id . " - " . Yii::$app->params['password_generation'];
        $mailer->compose('changePassword', compact(['userToken']))
            ->setTo($email)
            ->setSubject($subject)
            ->send();
    }
}
