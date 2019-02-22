<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_analog}}".
 *
 * @property int              $id
 * @property int              $client_id       ID ресторана
 * @property int              $product_id      ID из таблицы catalog_base_goods
 * @property int              $parent_id       id из таблицы product_analog
 * @property int              $sort_value
 * @property string           $coefficient     Коэффициент
 * @property Organization     $client
 * @property CatalogBaseGoods $product
 * @property ProductAnalog    $parent
 * @property ProductAnalog    $firstAnalog
 */
class ProductAnalog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product_analog}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_id', 'product_id'], 'required'],
            [['client_id', 'product_id', 'parent_id', 'sort_value'], 'integer'],
            [['coefficient'], 'number'],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogBaseGoods::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'client_id'   => 'ID ресторана',
            'product_id'  => 'ID из таблицы catalog_base_goods',
            'parent_id'   => 'id из таблицы product_analog',
            'sort_value'  => 'Sort Value',
            'coefficient' => 'Коэффициент',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Organization::class, ['id' => 'client_id']);
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
    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * @return string
     */
    public function getFirstAnalog()
    {
        $gId = $this->parent_id ?? $this->id;

        $model = self::find()->where(['OR',
            ['id' => $gId],
            ['parent_id' => $gId],
        ])->orderBy(['sort_value' => SORT_ASC])->limit(1)->one();
        if ($model) {
            return $model;
        }
        return $this;
    }
}
