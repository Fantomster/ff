<?php

namespace common\models\guides;

use Yii;
use common\models\CatalogBaseGoods;

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
                ->where(['catalog_goods.base_goods_id' => $this->cbg_id, 'relation.supp_rest.rest_org_id' => $this->guide->client_id])
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
}
