<?php

namespace common\models;

use api_web\behaviors\OrderContentBehavior;
use common\helpers\DBNameHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "order_content".
 *
 * @property integer                   $id
 * @property integer                   $order_id
 * @property integer                   $product_id
 * @property string                    $quantity
 * @property string                    $initial_quantity
 * @property string                    $price
 * @property string                    $plan_price
 * @property string                    $plan_quantity
 * @property string                    $product_name
 * @property string                    $article
 * @property integer                   $units
 * @property string                    $comment
 * @property string                    $merc_uuid
 * @property integer                   $vat_product
 * @property string                    $edi_desadv
 * @property string                    $edi_alcdes
 * @property string                    $edi_number
 * @property string                    $edi_recadv
 * @property string                    $edi_invoice
 * @property string                    $updated_at
 * @property float                     $into_quantity
 * @property float                     $into_price
 * @property float                     $into_price_vat
 * @property float                     $into_price_sum
 * @property float                     $into_price_sum_vat
 * @property integer                   $invoice_content_id
 * @property Order                     $order
 * @property CatalogBaseGoods          $product
 * @property string                    $total
 * @property string                    $note
 * @property CatalogGoods              $productFromCatalog
 * @property IntegrationInvoiceContent $invoiceContent
 * @property WaybillContent            $waybillContent
 */
class OrderContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'product_id', 'quantity', 'price', 'product_name'], 'required'],
            [['order_id', 'product_id', 'updated_user_id', 'vat_product', 'invoice_content_id'], 'integer'],
            [['quantity', 'initial_quantity', 'units', 'plan_price', 'plan_quantity'], 'number'],
            [['price'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['merc_uuid', 'edi_desadv', 'edi_alcdes', 'edi_number', 'edi_recadv', 'edi_invoice'], 'safe'],
            [['comment'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogBaseGoods::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id'         => 'Order ID',
            'product_id'       => 'Product ID',
            'product_name'     => Yii::t('app', 'common.models.product_name', ['ru' => 'Продукт']),
            'quantity'         => Yii::t('app', 'common.models.amount', ['ru' => 'Количество']),
            'initial_quantity' => Yii::t('app', 'common.models.asked_amount', ['ru' => 'Запрошенное количество']),
            'price'            => Yii::t('app', 'common.models.price_three', ['ru' => 'Цена']),
            'total'            => Yii::t('app', 'common.models.sum', ['ru' => 'Сумма']),
            'comment'          => 'Comment',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdiOrderContent()
    {
        return $this->hasOne(EdiOrderContent::class, ['order_content_id' => 'id']);
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
    public function getInvoiceContent()
    {
        return $this->hasOne(IntegrationInvoiceContent::class, ['id' => 'invoice_content_id']);
    }

    /**
     * @return float|int
     */
    public function getTotal()
    {
        return $this->quantity * $this->price;
    }

    /**
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getProductFromCatalog()
    {
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
                "$rsrTable.status"      => RelationSuppRest::CATALOG_STATUS_ON,
                "$rsrTable.deleted"     => false,
                "$cbgTable.deleted"     => CatalogBaseGoods::DELETED_OFF,
                "$cbgTable.status"      => CatalogBaseGoods::STATUS_ON,
                "$rsrTable.supp_org_id" => $this->order->vendor_id,
                "$rsrTable.rest_org_id" => $this->order->client_id,
                "$catTable.status"      => Catalog::STATUS_ON,
                "$cbgTable.id"          => $this->product_id,
            ])
            ->one();
        return $product;
    }

    /**
     * @return array
     */
    public function copyIfPossible()
    {
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
                "$rsrTable.deleted"     => false,
                "$cbgTable.deleted"     => CatalogBaseGoods::DELETED_OFF,
                "$cbgTable.status"      => CatalogBaseGoods::STATUS_ON,
                "$rsrTable.supp_org_id" => $this->order->vendor_id,
                "$rsrTable.rest_org_id" => $this->order->client_id,
                "$catTable.status"      => Catalog::STATUS_ON,
                "$cbgTable.id"          => $this->product_id,
            ])
            ->one();
        /**@var \common\models\CatalogGoods $product */
        if ($product) {
            return [
                'product_id'   => $product->baseProduct->id,
                'quantity'     => $this->quantity,
                'price'        => $product->price,
                'product_name' => $product->baseProduct->product,
                'units'        => $product->baseProduct->units,
                'article'      => $product->baseProduct->article,
            ];
        }
        $product = CatalogBaseGoods::find()
            ->leftJoin($orgTable, "$orgTable.id = $cbgTable.supp_org_id")
            ->leftJoin($rsrTable, "$rsrTable.cat_id = $cbgTable.cat_id")
            ->leftJoin($catTable, "$catTable.id = $rsrTable.cat_id")
            ->where([
                "$rsrTable.deleted"     => false,
                "$cbgTable.deleted"     => CatalogBaseGoods::DELETED_OFF,
                "$cbgTable.status"      => CatalogBaseGoods::STATUS_ON,
                "$rsrTable.supp_org_id" => $this->order->vendor_id,
                "$rsrTable.rest_org_id" => $this->order->client_id,
                "$catTable.status"      => Catalog::STATUS_ON,
                "$cbgTable.id"          => $this->product_id,
            ])
            ->one();
        /**@var CatalogBaseGoods $product */
        if ($product) {
            return [
                'product_id'   => $product->id,
                'quantity'     => $this->quantity,
                'price'        => $product->price,
                'product_name' => $product->product,
                'units'        => $product->units,
                'article'      => $product->article,
            ];
        }
        return [];
    }

    /**
     * @return \common\models\GoodsNotes|null
     */
    public function getNote()
    {
        return GoodsNotes::findOne(['catalog_base_goods_id' => $this->product_id, 'rest_org_id' => $this->order->client_id]);
    }

    /**
     * @return string
     */
    public function formatPrice()
    {
        return $this->price . " " . $this->order->currency->symbol;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);
        /*if(!$insert){
            if($this->plan_quantity == 0.000){
                $this->plan_quantity = $this->quantity;
            }
            if($this->plan_price == 0.00){
                $this->plan_price = $this->price;
            }
        }else{
            $this->plan_price = $this->price;
        }*/
        return $result;
    }

    /**
     * @param bool  $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $product = $this->productFromCatalog;
        if (!empty($product)) {
            $catalog = $product->catalog;
        } else {
            $catalog = $this->product->catalog;
        }

        if ($catalog->currency_id !== $this->order->currency_id) {
            $order = $this->order;
            $order->currency_id = $catalog->currency_id;
            $order->save();
        }

        if (!is_a(Yii::$app, 'yii\console\Application')) {
            if ($this->order->status == OrderStatus::STATUS_FORMING)
                \api\modules\v1\modules\mobile\components\notifications\NotificationCart::actionCartContent($this->id);
        }

    }

    /**
     * @return \common\models\Currency|null
     */
    public function getCurrency()
    {
        return Currency::findOne($this->order->currency_id);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        $result = parent::beforeDelete();

        if (!is_a(Yii::$app, 'yii\console\Application')) {
            if ($this->order->status == OrderStatus::STATUS_FORMING)
                \api\modules\v1\modules\mobile\components\notifications\NotificationCart::actionCartContentDelete($this);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => OrderContentBehavior::class,
                'model' => $this
            ],
            [
                'class'              => TimestampBehavior::class,
                'updatedAtAttribute' => 'updated_at',
                'createdAtAttribute' => false,
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWaybillContent()
    {
        #В случае если связь один ко многим, выдергиваем запись, которая последняя обновилась
        return $this->hasOne(WaybillContent::class, ['order_content_id' => 'id'])->orderBy(['updated_at' => SORT_DESC])->limit(1);
    }

    /**
     * @param $serviceId
     * @return bool
     */
    public function isComparised($serviceId)
    {
        return (new Query())->from(self::tableName() . ' as oc')
            ->leftJoin(DBNameHelper::getApiName() . '.' . OuterProductMap::tableName() . ' as opm', 'opm.product_id=oc.product_id AND opm.service_id = :serviceId', [':serviceId' => $serviceId])
            ->where([
                'oc.id'           => $this->id,
                'organization_id' => $this->order->client_id,
                'vendor_id'       => $this->order->vendor_id,
            ])->exists();
    }
}
