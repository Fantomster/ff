<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "outer_product_type".
 *
 * @property int            $id
 * @property int            $service_id
 * @property string         $value
 * @property string         $comment
 * @property OuterProduct[] $outerProducts
 */
class OuterProductType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%outer_product_type}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['service_id'], 'integer'],
            [['value', 'comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'service_id' => 'Service ID',
            'value'      => 'Value',
            'comment'    => 'Comment',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOuterProducts()
    {
        return $this->hasMany(OuterProduct::class, ['outer_product_type_id' => 'id']);
    }
}
