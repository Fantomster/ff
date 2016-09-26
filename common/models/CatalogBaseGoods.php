<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "catalog_base_goods".
 *
 * @property integer $id
 * @property integer $cat_id
 * @property integer $category_id
 * @property string $article
 * @property string $product
 * @property integer $units
 * @property integer $price
 * @property integer $status
 * @property integer $market_place
 * @property integer $deleted
 * @property string $created_at
 * @property string $updated_at
 * @property file $importCatalog
 * 
 * @property Organization $vendor
 */
class CatalogBaseGoods extends \yii\db\ActiveRecord {

    const STATUS_ON = 1;
    const STATUS_OFF = 0;
    const MARKETPLACE_ON = 1;
    const MARKETPLACE_OFF = 0;
    const DELETED_ON = 1;
    const DELETED_OFF = 0;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'catalog_base_goods';
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
    public function rules() {
        return [
            [['cat_id','article','price','product'], 'required'],
            [['cat_id', 'category_id', 'status', 'market_place', 'deleted'], 'integer'],
            [['article'], 'string', 'max' => 50],
            [['product'], 'string', 'max' => 255],
            [['units'], 'number'],
            [['price'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
                /* [['price'],'filter','filter'=>function ($value) {
                  $price = $value/100;
                  return $value;
                  }], */
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'cat_id' => 'Каталог',
            'category_id' => 'Категория',
            'article' => 'Артикул',
            'product' => 'Продукт',
            'units' => 'Кратность',
            'price' => 'Цена (руб.)',
            'status' => 'Статус',
            'market_place' => 'Market_place',
            'deleted' => 'Deleted',
                //'importCatalog'=>'Files'
        ];
    }

    public function beforeSave($insert)
    {
    if (parent::beforeSave($insert)) {
            $this->price = str_replace(",", ".", $this->price);
            return true;
        }
        return false;
    }
    

    /* public function beforeSave($insert)
      {
      if (parent::beforeSave($insert)) {
      $price = $this->price;
      $price = str_replace(',', '.', $price);
      if(substr($price, -3, 1) == '.')
      {
      $price = explode('.', $price);
      $last = array_pop($price);
      $price = join($price, '').'.'.$last;
      }
      else
      {
      $price = str_replace('.', '', $price);
      }
      $this->price = $price;
      return true;
      } else {
      return false;
      }
      } */

    public function search($params, $id) {
        $query = CatalogBaseGoods::find()->select(['id', 'cat_id', 'category_id', 'article', 'product', 'units', 'price', 'status', 'market_place'])->where(['cat_id' => $id, 'deleted' => '0']);
        //$query->andFilterWhere(['like', 'product', '']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'attributes' => [
                'id',
                'cat_id',
                'category_id',
                'article',
                'product',
                'units',
                'price',
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        return $dataProvider;
    }

    public static function GetCatalog() {
        $catalog = CatalogBaseGoods::find()
                        ->where(['supp_org_id' => \common\models\User::getOrganizationUser(Yii::$app->user->id), 'type' => \common\models\Catalog::BASE_CATALOG])->all();
        return $catalog;
    }

    public static function get_value($id) {
        $model = CatalogBaseGoods::find()->where(["id" => $id])->one();
        if (!empty($model)) {
            return $model;
        }
        return null;
    }

    public static function get_no_active_product($id) {
        $model = CatalogBaseGoods::find()->select('id')->where(["id" => $id, 'status' => CatalogBaseGoods::STATUS_OFF])->all();
        return $model;
    }

    public static function getImageurl() {
        return \Yii::$app->urlManager->createUrl('@web/path/to/logo/' . $this->image);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor() {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id']);
    }

}
