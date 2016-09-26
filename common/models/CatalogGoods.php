<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "catalog_goods".
 *
 * @property integer $id
 * @property integer $cat_id
 * @property integer $base_goods_id
 * @property integer $price
 * @property integer $discount
 * @property integer $discount_percent
 * @property integer $discount_fixed
 * @property string $note
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property CatalogBaseGoods $baseProduct
 * @property Organization $organization
 */
class CatalogGoods extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'catalog_goods';
    }
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function scenarios() {
        $scenarios = parent::scenarios();
        $scenarios['update'] = ['discount_percent', 'cat_id'];

        return $scenarios;
    }

    public function rules() {
        return [
            [['cat_id', 'base_goods_id'], 'required'],
            [['cat_id', 'base_goods_id'], 'integer'],
            [['price'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],           
            [['note'], 'string', 'max' => 500],
            [['discount'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/', 'min' => 0],
            [['discount_percent'], 'number', 'min' => -100, 'max' => 100],
            [['discount_fixed'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/', 'min' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'cat_id' => 'Cat ID',
            'base_goods_id' => 'Cat Base Goods ID',
            'price' => 'Цена',
            'note' => 'Комментарий',
            'discount' => 'Скидка (руб.)',
            'discount_percent' => 'Скидка %',
            'discount_fixed' => 'Фиксированная цена',
        ];
    }

    public function search($params, $id) {
        $query = CatalogGoods::find()->where(['cat_id' => $id]);
        $query->andWhere(['not in', 'base_goods_id', CatalogBaseGoods::find()->select('id')->where(['supp_org_id' => 'supp_org_id','deleted' => 1])]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'attributes' => [
                'id',
                'cat_id',
                'base_goods_id',
                'price',
                'note',
                'discount',
                'discount_percent',
                'discount_fixed'
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        return $dataProvider;
    }

    public static function searchProductFromCatalogGoods($id, $cat_id) {
        if (CatalogGoods::find()->where(['base_goods_id' => $id, 'cat_id' => $cat_id])->exists()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBaseProduct() {
        return $this->hasOne(CatalogBaseGoods::className(), ['id' => 'base_goods_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization() {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id'])->via('baseProduct');
    }

}
