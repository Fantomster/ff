<?php

namespace common\models\vetis;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "vetis_subproduct_by_product".
 *
 * @property string $uuid
 * @property string $guid
 * @property int $last
 * @property int $active
 * @property int $status
 * @property string $next
 * @property string $previous
 * @property string $name
 * @property string $code
 * @property string $productGuid
 * @property string $createDate
 * @property string $updateDate
 * @property object $subProduct
 */
class VetisSubproductByProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_subproduct_by_product';
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
            [['uuid', 'guid'], 'required'],
            [['uuid'], 'unique'],
            [['last', 'active', 'status'], 'integer'],
            [['createDate', 'updateDate'], 'safe'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'code', 'productGuid'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uuid' => 'Uuid',
            'guid' => 'Guid',
            'last' => 'Last',
            'active' => 'Active',
            'status' => 'Status',
            'next' => 'Next',
            'previous' => 'Previous',
            'name' => 'Name',
            'code' => 'Code',
            'productGuid' => 'Product Guid',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
        ];
    }
    
    public function getSubProduct()
    {
        return \yii\helpers\Json::decode($this->data);
    }
    
    public static function getSubProductByProductList($product_guid) {
        $models = self::find()
                ->select(['uuid', 'name', 'code'])
                ->where(['active' => true, 'last' => true, 'productGuid' => $product_guid])
                ->asArray()
                ->all();

        return ArrayHelper::map($models, 'uuid', function($model) {
            return $model['name'] . ' (' . $model['code'] . ')';
        });
    }
}
