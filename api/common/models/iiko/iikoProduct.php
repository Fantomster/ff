<?php

namespace api\common\models\iiko;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "iiko_product".
 *
 * @property integer $id
 * @property string $uuid
 * @property string $denom
 * @property string $parent_uuid
 * @property integer $org_id
 * @property string $num
 * @property string $code
 * @property string $product_type
 * @property string $cooking_place_type
 * @property string $unit
 * @property string $containers
 * @property integer $is_active
 * @property string $created_at
 * @property string $updated_at
 */
class iikoProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'iiko_product';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'org_id'], 'required'],
            [['org_id', 'is_active'], 'integer'],
            [['containers'], 'string'],
            [['created_at', 'updated_at','code'], 'safe'],
            [['uuid', 'parent_uuid'], 'string', 'max' => 36],
            [['denom'], 'string', 'max' => 255],
            [['num', 'code', 'product_type', 'cooking_place_type', 'unit'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'uuid' => Yii::t('app', 'Uuid'),
            'denom' => Yii::t('app', 'Denom'),
            'parent_uuid' => Yii::t('app', 'Parent Uuid'),
            'org_id' => Yii::t('app', 'Org ID'),
            'num' => Yii::t('app', 'Num'),
            'code' => Yii::t('app', 'Code'),
            'product_type' => Yii::t('app', 'Product Type'),
            'cooking_place_type' => Yii::t('app', 'Cooking Place Type'),
            'unit' => Yii::t('app', 'Unit'),
            'containers' => Yii::t('app', 'Containers'),
            'is_active' => Yii::t('app', 'Is Active'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function beforeSave($insert)
    {
        if($insert) {
            $this->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        }

        $this->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory() {
        return $this->hasOne(iikoCategory::className(), ['uuid' => 'parent_uuid']);
    }
}
