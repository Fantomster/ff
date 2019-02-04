<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%preorder_content}}".
 *
 * @property int $id
 * @property int $preorder_id id предзаказа из таблицы preorder
 * @property int $product_id id предзаказа из таблицы preorder
 * @property string $plan_quantity планируемое для заказа количество
 * @property string $created_at Дата и время создания записи в таблице
 * @property string $updated_at Дата и время последнего изменения записи в таблице
 *
 * @property CatalogBaseGoods $product
 * @property Preorder $preorder
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
    public function rules()
    {
        return [
            [['preorder_id', 'product_id'], 'integer'],
            [['plan_quantity'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
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
            'id' => 'ID',
            'preorder_id' => 'Preorder ID',
            'product_id' => 'Product ID',
            'plan_quantity' => 'Plan Quantity',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(CatalogBaseGoods::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPreorder()
    {
        return $this->hasOne(Preorder::className(), ['id' => 'preorder_id']);
    }
}
