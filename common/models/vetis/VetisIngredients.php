<?php

namespace common\models\vetis;

/**
 * This is the model class for table "vetis_ingredients".
 *
 * @property int              $id
 * @property string           $guid         GUID из vetis_product_item продукции к которому принадлежит ингредиент
 * @property string           $product_name Название продукта из таблицы merc_stock_entry.product_name
 * @property string           $amount       Кол-во ингредиента необходимое для переработки в одну единицу продукции
 * @property VetisProductItem $productItem
 */
class VetisIngredients extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_ingredients';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return \Yii::$app->get('db_api');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount'], 'required'],
            [['amount'], 'number'],
            [['guid', 'product_name'], 'string', 'max' => 255],
            [['guid'], 'exist', 'skipOnError' => true, 'targetClass' => VetisProductItem::class, 'targetAttribute' => ['guid' => 'guid']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'guid'         => 'GUID из vetis_product_item продукции к которому принадлежит ингредиент',
            'product_name' => 'Название продукта из таблицы merc_stock_entry.product_name',
            'amount'       => 'Кол-во ингредиента необходимое для переработки в одну единицу продукции',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductItem()
    {
        return $this->hasOne(VetisProductItem::class, ['guid' => 'guid']);
    }
}
