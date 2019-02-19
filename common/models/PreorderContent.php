<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%preorder_content}}".
 *
 * @property int              $id
 * @property int              $preorder_id          id предзаказа из таблицы preorder
 * @property int              $product_id           id предзаказа из таблицы preorder
 * @property int              $parent_product_id    id предзаказа из таблицы preorder
 * @property string           $plan_quantity        планируемое для заказа количество
 * @property string           $created_at           Дата и время создания записи в таблице
 * @property string           $updated_at           Дата и время последнего изменения записи в таблице
 * @property CatalogBaseGoods $product
 * @property Preorder         $preorder
 */
class PreorderContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%preorder_content}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
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
            [['preorder_id', 'product_id', 'parent_product_id'], 'integer'],
            [['plan_quantity'], 'number'],
            [['created_at', 'updated_at', 'parent_product_id'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogBaseGoods::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['preorder_id'], 'exist', 'skipOnError' => true, 'targetClass' => Preorder::className(), 'targetAttribute' => ['preorder_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'preorder_id'       => 'Preorder ID',
            'product_id'        => 'Product ID',
            'parent_product_id' => 'Parent Product ID',
            'plan_quantity'     => 'Plan Quantity',
            'created_at'        => 'Created At',
            'updated_at'        => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(CatalogBaseGoods::class, ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentProduct()
    {
        return $this->hasOne(CatalogBaseGoods::class, ['id' => 'parent_product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPreorder()
    {
        return $this->hasOne(Preorder::class, ['id' => 'preorder_id']);
    }

    /**
     * @param bool $r
     * @return float
     */
    public function getAllQuantity($r = true)
    {
        $quantity = 0;
        $orders = $this->preorder->orders;
        if ($orders) {
            foreach ($orders as $order) {
                /** @var OrderContent $orderContent */
                $orderContent = $order->getOrderContent()->where([
                    'product_id' => $this->product_id
                ])->one();
                if ($orderContent) {
                    $quantity += round($orderContent->quantity, 3);
                }
            }
            if ($r) {
                $analogsPreorderContent = self::find()->where([
                    'preorder_id'       => $this->preorder_id,
                    'parent_product_id' => $this->product_id
                ])->all();

                if ($analogsPreorderContent) {
                    foreach ($analogsPreorderContent as $analog) {
                        $quantity += floatval($analog->getAllQuantity(false));
                    }
                }
            }
        }
        return round($quantity, 3);
    }

    /**
     * @param bool $r
     * @return float
     */
    public function getAllSum($r = true)
    {
        $sum = 0;
        $orders = $this->preorder->orders;
        if ($orders) {
            foreach ($orders as $order) {
                /** @var OrderContent $orderContent */
                $orderContent = $order->getOrderContent()->where(['product_id' => $this->product_id])->one();
                if ($orderContent) {
                    $sum += round($orderContent->price * $orderContent->quantity, 3);
                }
            }

            if ($r) {
                $analogsPreorderContent = self::find()->where([
                    'preorder_id'       => $this->preorder_id,
                    'parent_product_id' => $this->product_id
                ])->all();

                if ($analogsPreorderContent) {
                    foreach ($analogsPreorderContent as $analog) {
                        $sum += floatval($analog->getAllSum(false));
                    }
                }
            }
        }
        return round($sum, 3);
    }
}
