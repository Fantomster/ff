<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "catalog_goods".
 *
 * @property int              $id               Идентификатор записи в таблице
 * @property int              $cat_id           Идентификатор каталога
 * @property int              $base_goods_id    Идентификатор товара в основном каталоге поставщика
 * @property string           $created_at       Дата и время создания записи в таблице
 * @property string           $updated_at       Дата и время последнего изменения записи в таблице
 * @property int              $discount_percent Скидка на товар в процентах
 * @property string           $discount         Скидка на товар в денежных единицах (рублях)
 * @property string           $discount_fixed   Фиксированная цена на товар
 * @property string           $price            Цена на товар
 * @property int              $vat              Ставка НДС
 * @property int              $service_id       Указатель на ID сервиса
 *
 * @property CatalogBaseGoods $baseProduct
 * @property GoodsNotes       $goodsNotes
 * @property Organization     $organization
 * @property Catalog          $catalog
 */
class CatalogGoods extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%catalog_goods}}';
    }

    public function behaviors()
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
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['update'] = ['discount_percent', 'cat_id'];

        return $scenarios;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->price = str_replace(",", ".", $this->price);
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['cat_id', 'base_goods_id'], 'required'],
            [['cat_id', 'base_goods_id', 'vat', 'service_id'], 'integer'],
            [['price'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['discount'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/', 'min' => 0],
            [['discount_percent'], 'number', 'min' => -100, 'max' => 100],
            [['discount_fixed'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/', 'min' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'cat_id'           => Yii::t('app', 'Cat ID'),
            'base_goods_id'    => Yii::t('app', 'Cat Base Goods ID'),
            'price'            => Yii::t('app', 'common.models.price_two', ['ru' => 'Цена']),
            'discount'         => Yii::t('app', 'common.models.discount_rouble', ['ru' => 'Скидка (руб.)']),
            'discount_percent' => Yii::t('app', 'common.models.discount_percent', ['ru' => 'Скидка %']),
            'discount_fixed'   => Yii::t('app', 'common.models.fix_price', ['ru' => 'Фиксированная цена']),
            'vat'              => Yii::t('app', 'Ставка НДС'),
            'service_id'       => 'Service id'
        ];
    }

    /**
     * @param $params
     * @param $id
     * @return ActiveDataProvider
     */
    public function search($params, $id)
    {
        $query = CatalogGoods::find()->where(['cat_id' => $id])->andWhere(['not in', 'base_goods_id', CatalogBaseGoods::find()->select('id')->where(['supp_org_id' => User::findIdentity(Yii::$app->user->id)->organization_id, 'deleted' => 1])]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'attributes' => [
                'id',
                'cat_id',
                'base_goods_id',
                'price',
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

    /**
     * @param $id
     * @param $cat_id
     * @return bool
     */
    public static function searchProductFromCatalogGoods($id, $cat_id)
    {
        if (CatalogGoods::find()->where(['base_goods_id' => $id, 'cat_id' => $cat_id])->exists()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBaseProduct()
    {
        return $this->hasOne(CatalogBaseGoods::className(), ['id' => 'base_goods_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsNotes()
    {
        return $this->hasOne(GoodsNotes::className(), ['catalog_base_goods_id' => 'base_goods_id']);

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id'])->via('baseProduct');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalog()
    {
        return $this->hasOne(Catalog::className(), ['id' => 'cat_id']);
    }

    /**
     * @return string
     */
    public function formatPrice()
    {
        return $this->price . " " . $this->catalog->currency->symbol;
    }

    /**
     * @return float
     */
    public function getDiscountPrice()
    {
        $price = $this->price;
        if (isset($this->discount_fixed) && $this->discount_fixed > 0) {
            return round($price - $this->discount_fixed, 2);
        }

        if (isset($this->discount_percent) && $this->discount_percent > 0) {
            return round(($price - ($price / 100) * $this->discount_percent), 2);
        }

        return round($price, 2);
    }
}
