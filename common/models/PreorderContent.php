<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\Query;

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
 * @property ProductAnalog    $productAnalog
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
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogBaseGoods::class, 'targetAttribute' => ['product_id' => 'id']],
            [['preorder_id'], 'exist', 'skipOnError' => true, 'targetClass' => Preorder::class, 'targetAttribute' => ['preorder_id' => 'id']],
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
        $orders = $this->preorder->getOrders()->andWhere(['not in', 'status', [
            Order::STATUS_REJECTED,
            Order::STATUS_CANCELLED
        ]])->all();

        if ($orders) {
            /** @var Order $order */
            foreach ($orders as $order) {
                /** @var OrderContent $orderContent */
                $orderContent = $order->getOrderContent()->where([
                    'product_id' => $this->product_id
                ])->one();
                if ($orderContent) {
                    $coefficient = $this->getCoefficients();
                    $quantity += round($orderContent->quantity * ($coefficient['my_coefficient'] / $coefficient['parent_coefficient']), 3);
                }
            }

            if ($r) {
                $parent_id = $this->getFirstProductAnalog($this->product_id, $this->preorder->organization_id);
                if ($parent_id) {
                    $analogsPreorderContent = self::find()
                        ->where("parent_product_id = :parent_id OR product_id = :pid", [
                            ":parent_id" => $parent_id,
                            ":pid"       => $parent_id
                        ])
                        ->andWhere(['preorder_id' => $this->preorder_id])
                        ->andWhere(['!=', 'product_id', $this->product_id])
                        ->all();

                    if ($analogsPreorderContent) {
                        foreach ($analogsPreorderContent as $analog) {
                            $quantity += $analog->getAllQuantity(false);
                        }
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
                $parent_id = $this->getFirstProductAnalog($this->product_id, $this->preorder->organization_id);
                if ($parent_id) {
                    $analogsPreorderContent = self::find()
                        ->where("parent_product_id = :parent_id OR product_id = :pid", [
                            ":parent_id" => $parent_id,
                            ":pid"       => $parent_id
                        ])
                        ->andWhere(['preorder_id' => $this->preorder_id])
                        ->andWhere(['!=', 'product_id', $this->product_id])
                        ->all();

                    if ($analogsPreorderContent) {
                        foreach ($analogsPreorderContent as $analog) {
                            $sum += $analog->getAllSum(false);
                        }
                    }
                }
            }
        }
        return round($sum, 3);
    }

    /**
     * @return array
     */
    private function getCoefficients()
    {
        $result = ['parent_coefficient' => 1, 'my_coefficient' => 1];

        $analogFirst = $this->productAnalog;
        if ($analogFirst) {
            $result['my_coefficient'] = floatval($analogFirst->coefficient);
        }

        $result['parent_coefficient'] = $result['my_coefficient'];
        $analogFirstCoefficient = $this->getFirstProductAnalogCoefficient();
        if ($analogFirstCoefficient) {
            $result['parent_coefficient'] = $analogFirstCoefficient;
        }

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductAnalog()
    {
        return $this->hasOne(ProductAnalog::class, ['product_id' => 'product_id'])->onCondition(['client_id' => $this->preorder->organization_id]);
    }

    /**
     *
     */
    public function getPlanQuantity()
    {
        $analog = $this->productAnalog;
        if ($analog) {
            $pIds = (new Query())
                ->select('product_id')
                ->from(ProductAnalog::tableName())
                ->where([
                    "OR",
                    ['id' => $analog->firstAnalog->id],
                    ['parent_id' => $analog->firstAnalog->id]
                ])
                ->column();

            return self::find()->where([
                'preorder_id' => $this->preorder_id,
                'product_id'  => $pIds
            ])->sum('plan_quantity');
        }
        return $this->plan_quantity;
    }

    /**
     * @return float|null
     */
    private function getFirstProductAnalogCoefficient()
    {
        $r = (new Query())
            ->select('b.coefficient')
            ->from(ProductAnalog::tableName() . ' as a')
            ->leftJoin(ProductAnalog::tableName() . ' as b', 'b.id = a.parent_id')
            ->where([
                'a.product_id' => $this->product_id,
                'a.client_id'  => $this->preorder->organization_id
            ])
            ->scalar();
        return ($r > 0) ? round($r, 6) : null;
    }

    /**
     * @param $product_id
     * @param $client_id
     * @return int|null
     */
    private function getFirstProductAnalog($product_id, $client_id)
    {
        $r = (new Query())
            ->select('b.product_id')
            ->from(ProductAnalog::tableName() . ' as a')
            ->leftJoin(ProductAnalog::tableName() . ' as b', 'b.id = a.parent_id')
            ->where([
                'a.product_id' => $product_id,
                'a.client_id'  => $client_id
            ])
            ->scalar();
        return ($r > 0) ? (int)$r : null;
    }
}
