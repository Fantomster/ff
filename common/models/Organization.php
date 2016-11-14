<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

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
 *
 * @property OrganizationType $type
 * @property Delivery $delivery
 * @property User $users
 * @property OrderChat $unreadMessages
 * @property OrderChat $unreadSystem
 */
class Organization extends \yii\db\ActiveRecord {

    const TYPE_RESTAURANT = 1;
    const TYPE_SUPPLIER = 2;
    
    const STEP_OK = 0;
    const STEP_SET_INFO = 1;
    const STEP_ADD_VENDOR = 2; //restaurants only
    const STEP_ADD_CATALOG = 3; //vendors only

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
            ['name', 'required', 'on' => 'register', 'message' => 'Пожалуйста, напишите название вашей организации'],
            ['type_id', 'required', 'on' => 'register', 'message' => 'Укажите, Вы "Ресторан" или "Поставщик"?'],
            [['type_id', 'name'], 'required'],
            [['type_id', 'step'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'city', 'address', 'zip_code', 'phone', 'email', 'website'], 'string', 'max' => 255],
            [['name', 'city', 'address', 'zip_code', 'phone', 'website'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['email'], 'email'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationType::className(), 'targetAttribute' => ['type_id' => 'id']],
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
        ];
    }

    public static function getOrganization($id) {
        $getOrganization = Organization::find()
                        ->where(['id' => $id])->one();
        return $getOrganization;
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
                ->where(['relation_supp_rest.rest_org_id' => $this->id]);
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
        $query = RelationCategory::find()
                ->select(['organization.id', 'organization.name'])
                ->distinct()
                ->leftJoin('relation_supp_rest', 'relation_category.rest_org_id = relation_supp_rest.rest_org_id')
                ->joinWith('client', false)
                ->where(['relation_category.supp_org_id' => $this->id]);

        $clients = ArrayHelper::map($query->orderBy(['organization.name' => SORT_ASC])
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
    public function getCatalogs($vendor_id = '', $category_id = '') {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return '0';
        }
        $query = RelationSuppRest::find()
                ->select(['relation_supp_rest.cat_id'])
                ->where(['relation_supp_rest.rest_org_id' => $this->id]);
        if ($category_id) {
            $query = $query->leftJoin('relation_category', 'relation_category.supp_org_id = relation_supp_rest.supp_org_id AND relation_category.rest_org_id = relation_supp_rest.rest_org_id')
                    ->andWhere(['relation_category.category_id' => $category_id]);
        }
        if ($vendor_id) {
            $query = $query->andWhere(['relation_supp_rest.supp_org_id' => $vendor_id]);
        }
        $catalogs = ArrayHelper::getColumn($query->asArray()->all(), 'cat_id');
        if (empty($catalogs)) {
            return '0';
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
    public function getNewOrdersCount() {
        $result = 0;
        switch ($this->type_id) {
            case self::TYPE_RESTAURANT:
                $result = Order::find()->where([
                    'client_id' => $this->id, 
                    'status' => [Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT]]
                        )->count();
                break;
            case self::TYPE_SUPPLIER:
                $result = Order::find()->where([
                    'vendor_id' => $this->id, 
                    'status' => [Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT]]
                        )->count();
                break;
        }
        return $result;
    }
    
    public function getNewClientCount() {
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
                . 'WHERE (`order_chat`.`recipient_id` = '.$this->id.') '
                . 'AND ((`order_chat`.`is_system`=0) '
                . 'AND (`order_chat`.`viewed`=0)) '
                . 'GROUP BY `order_chat`.`order_id` ) as oc2 ON `order_chat`.`id` = oc2.`id`'
                . 'ORDER BY `order_chat`.`created_at`';

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
                . 'WHERE (`order_chat`.`recipient_id` = '.$this->id.') '
                . 'AND ((`order_chat`.`is_system`=1) '
                . 'AND (`order_chat`.`viewed`=0)) '
                . 'GROUP BY `order_chat`.`order_id` ) as oc2 ON `order_chat`.`id` = oc2.`id`'
                . 'ORDER BY `order_chat`.`created_at`';

        return OrderChat::findBySql($sql)->all();  
//        return OrderChat::find()
//                ->leftJoin('order', 'order.id = order_chat.order_id')
//                ->where('(order.client_id=' . $this->id . ') OR (order.vendor_id=' . $this->id . ')')
//                ->andWhere(['order_chat.is_system' => 1, 'order_chat.viewed' => 0])
//                ->all();
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
        if ($insert && ($this->type_id = self::TYPE_SUPPLIER)) {
            $delivery = new Delivery();
            $delivery->vendor_id = $this->id;
            $delivery->save();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function markViewed($orderId) {
        return OrderChat::updateAll(['viewed' => 1], ['order_id' => $orderId, 'recipient_id'=>$this->id]);
    }
}
