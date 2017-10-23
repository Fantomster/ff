<?php

namespace common\models\guides;

use Yii;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\Catalog;

/**
 * This is the model class for table "guide_product".
 *
 * @property integer $id
 * @property integer $guide_id
 * @property integer $cbg_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property CatalogBaseGoods $baseProduct
 * @property Guide $guide
 * @property string $price
 * @property string $note
 * @property string $formattedPrice
 */
class GuideProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'guide_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['guide_id', 'cbg_id'], 'required'],
            [['guide_id', 'cbg_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['cbg_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogBaseGoods::className(), 'targetAttribute' => ['cbg_id' => 'id']],
            [['guide_id'], 'exist', 'skipOnError' => true, 'targetClass' => Guide::className(), 'targetAttribute' => ['guide_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'guide_id' => Yii::t('app', 'Guide ID'),
            'cbg_id' => Yii::t('app', 'Cbg ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBaseProduct()
    {
        return $this->hasOne(CatalogBaseGoods::className(), ['id' => 'cbg_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGuide()
    {
        return $this->hasOne(Guide::className(), ['id' => 'guide_id']);
    }
    
    public function getPrice() {
        $product = \common\models\CatalogGoods::find()
                ->leftJoin('relation_supp_rest', 'catalog_goods.cat_id=relation_supp_rest.cat_id')
                ->where(['catalog_goods.base_goods_id' => $this->cbg_id, 'relation_supp_rest.rest_org_id' => $this->guide->client_id])
                ->one();
        if (empty($product)) {
            $product = CatalogBaseGoods::find()->where(['id' => $this->cbg_id])->one();
        }
        if (empty($product)) {
            return 0;
        } else {
            return $product->price;
        }
    }
    
    public function getNote() {
        $note = \common\models\GoodsNotes::findOne(['catalog_base_goods_id' => $this->cbg_id, 'rest_org_id' => $this->guide->client_id]);
        return isset($note) ? $note->note : '';
    }
    
    public function afterSave() {
        parent::afterDelete();
        if (!is_a(Yii::$app, 'yii\console\Application')) {
            \api\modules\v1\modules\mobile\components\NotificationHelper::actionGuideProduct($this->id);
        }
    }
    
    public function getFormattedPrice() {
        $cgTable = CatalogGoods::tableName();
        $cbgTable = CatalogBaseGoods::tableName();
        $orgTable = \common\models\Organization::tableName();
        $rsrTable = \common\models\RelationSuppRest::tableName();
        $catTable = Catalog::tableName();
        $currencySymbol = '';
        
        $product = CatalogGoods::find()
                ->leftJoin($cbgTable, "$cbgTable.id = $cgTable.base_goods_id")
                ->leftJoin($orgTable, "$orgTable.id = $cbgTable.supp_org_id")
                ->leftJoin($rsrTable, "$rsrTable.cat_id = $cgTable.cat_id")
                ->leftJoin($catTable, "$catTable.id = $rsrTable.cat_id")
                ->where([
                    "$rsrTable.deleted" => false,
                    "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF,
                    "$cbgTable.status" => CatalogBaseGoods::STATUS_ON,
                    "$rsrTable.rest_org_id" => $this->guide->client_id,
                    "$catTable.status" => Catalog::STATUS_ON,
                    "$cbgTable.id" => $this->cbg_id,
                ])
                ->one();
        if ($product) {
            $currencySymbol = $product->catalog->currency->symbol;
            return $this->price . ' ' . $currencySymbol;
        }

        $product = CatalogBaseGoods::find()
                ->leftJoin($orgTable, "$orgTable.id = $cbgTable.supp_org_id")
                ->leftJoin($rsrTable, "$rsrTable.cat_id = $cbgTable.cat_id")
                ->leftJoin($catTable, "$catTable.id = $rsrTable.cat_id")
                ->where([
                    "$rsrTable.deleted" => false,
                    "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF,
                    "$cbgTable.status" => CatalogBaseGoods::STATUS_ON,
                    "$rsrTable.rest_org_id" => $this->guide->client_id,
                    "$catTable.status" => Catalog::STATUS_ON,
                    "$cbgTable.id" => $this->cbg_id,
                ])
                ->one();
        if ($product) {
            $currencySymbol = $product->catalog->currency->symbol;
            return $this->price . ' ' . $currencySymbol;
        }
    }
}
