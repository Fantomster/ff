<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%outer_product_type_selected}}".
 *
 * @property int $id
 * @property int $outer_product_type_id
 * @property int $org_id
 * @property int $selected
 * @property OuterProductType $outerProductType
 */
class OuterProductTypeSelected extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%outer_product_type_selected}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
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
            [['outer_product_type_id', 'org_id'], 'required'],
            [['outer_product_type_id', 'org_id', 'selected'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                    => Yii::t('app', 'ID'),
            'outer_product_type_id' => Yii::t('app', 'Outer Product Type ID'),
            'org_id'                => Yii::t('app', 'Org ID'),
            'selected'              => Yii::t('app', 'Selected'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOuterProductType()
    {
        return $this->hasOne(OuterProductType::class, ['id' => 'outer_product_type_id']);
    }
}
