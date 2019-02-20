<?php

namespace common\models;

use api_web\components\Registry;
use api_web\helpers\{CurrencyHelper, WebApiHelper};

/**
 * This is the model class for table "{{%preorder}}".
 *
 * @property int               $id              Идентификатор записи в таблице
 * @property int               $organization_id id организации, которая сделала предзаказ
 * @property int               $user_id         id пользователя, который создал предзаказ
 * @property int               $is_active       активен ли данный предзаказ
 * @property string            $created_at      Дата и время создания записи в таблице
 * @property string            $updated_at      Дата и время последнего изменения записи в таблице
 * @property string            $sum             Сумма предзаказа
 * @property Order[]           $orders
 * @property Organization      $organization
 * @property User              $user
 * @property PreorderContent[] $preorderContents
 * @property Currency          $currency
 */
class Preorder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%preorder}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => \yii\behaviors\TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'user_id', 'is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::class, 'targetAttribute' => ['organization_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'organization_id' => 'Organization ID',
            'user_id'         => 'User ID',
            'is_active'       => 'Is Active',
            'created_at'      => 'Created At',
            'updated_at'      => 'Updated At',
        ];
    }

    /**
     * Список заказов в предзаказе
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['preorder_id' => 'id']);
    }

    /**
     * Ресторан
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }

    /**
     * Пользователь
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Продукты в предзаказе
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreorderContents()
    {
        return $this->hasMany(PreorderContent::class, ['preorder_id' => 'id'])->andWhere('parent_product_id is null');
    }

    /**
     * Сумма всех заказов в предзаказе
     *
     * @return string
     */
    public function getSum()
    {
        $sum = 0;
        /** @var Order[] $orders */
        $orders = $this->orders;
        if (!empty($orders)) {
            /** @var Order $order */
            foreach (WebApiHelper::generator($orders) as $order) {
                $sum = $sum + (float)$order->getTotalPrice();
            }
        }
        return CurrencyHelper::asDecimal($sum);
    }

    /**
     * Валюта в предзаказе, по умолчанию RUB
     *
     * @return Currency
     */
    public function getCurrency()
    {
        /** @var Order $order */
        $order = $this->getOrders()->limit(1)->one();
        if ($order) {
            return $order->currency;
        }
        return Currency::findOne(Registry::DEFAULT_CURRENCY_ID);
    }

    /**
     * @param $product_id
     * @return int|mixed|string
     */
    public function getQuantityWithCoefficient($product_id)
    {
        $q = 0;
        $orders = $this->getOrders()->andWhere(['not in', 'status', [
            Order::STATUS_REJECTED,
            Order::STATUS_CANCELLED
        ]])->all();
        /** @var Order $order */
        foreach ($orders as $order) {
            $orderContents = $order->getOrderContent()->where(['product_id' => $product_id])->all();
            if ($orderContents) {
                foreach ($orderContents as $orderContent) {
                    $q += $orderContent->quantity;
                }
            }
        }
        return $q;
    }
}
