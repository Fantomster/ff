<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "cart".
 *
 * @property int $id
 * @property int $organization_id
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organization $organization
 * @property User $user
 * @property CartContent[] $cartContents
 */
class Cart extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cart';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['organization_id', 'user_id'], 'required'],
            [['organization_id', 'user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['organization_id', 'user_id'], 'unique', 'targetAttribute' => ['organization_id', 'user_id']],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'organization_id' => Yii::t('app', 'Organization ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return array
     */
    public function getVendors()
    {
        $vendors = $this->getCartContents()->select('vendor_id as id')->distinct()->all();
        $result = [];
        foreach ($vendors as $vendor) {
            $result[] = Organization::findOne($vendor['id']);
        }
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCartContents()
    {
        return $this->hasMany(CartContent::className(), ['cart_id' => 'id']);
    }

    /**
     * Стоимость доставки от конкретного вендора из корзины
     * @param $vendor_id
     * @return int
     */
    public function calculateDelivery($vendor_id)
    {
        $vendor = Organization::findOne($vendor_id);
        $total_price = $this->getRawPrice($vendor->id); //CartContent::find()->select('SUM(quantity*price)')->where(['cart_id' => $this->id, 'vendor_id' => $vendor->id])->scalar();
        if (isset($vendor->delivery)) {
            $free_delivery = $vendor->delivery->min_free_delivery_charge;
        } else {
            $free_delivery = 0;
        }
        if ((($free_delivery > 0) && ($total_price < $free_delivery)) || ($free_delivery == 0)) {
            return round($vendor->delivery->delivery_charge, 2);
        }
        return 0;
    }

    public function forFreeDelivery($vendor_id, $rawPrice = null) {
        $vendor = Organization::findOne($vendor_id);
        if ($vendor->delivery->min_free_delivery_charge == 0) {
            return -1;
        }
        if (isset($vendor->delivery)) {
            $diff = $vendor->delivery->min_free_delivery_charge - (!isset($rawPrice) ? $this->rawPrice : $rawPrice);
        } else {
            $diff = 0;
        }
        return ceil((($diff > 0) ? $diff : 0) * 100) / 100;
    }

    public function forMinCartPrice($vendor_id, $rawPrice = null) {
        $vendor = Organization::findOne($vendor_id);
        if (isset($vendor->delivery)) {
            $diff = $vendor->delivery->min_order_price - (!isset($rawPrice) ? $this->getRawPrice($vendor_id) : $rawPrice);
        } else {
            $diff = 0;
        }
        return ceil((($diff > 0) ? $diff : 0) * 100) / 100;
    }

    public function getRawPrice($vendor_id) {
        return CartContent::find()->select('SUM(quantity*price)')->where(['cart_id' => $this->id, 'vendor_id' => $vendor_id])->scalar();
    }

    public function calculateTotalPrice($vendor_id, $rawPrice = null) {
        $total_price = !isset($rawPrice) ? $this->getRawPrice($vendor_id) : $rawPrice;
        $total_price += $this->calculateDelivery($vendor_id);
        return number_format($total_price, 2, '.', '');
    }
}
