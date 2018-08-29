<?php

namespace common\models\vetis;

use Yii;

/**
 * This is the model class for table "vetis_product_item".
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
 * @property string $globalID
 * @property int $productType
 * @property string $product_uuid
 * @property string $product_guid
 * @property string $subproduct_uuid
 * @property string $subproduct_guid
 * @property int $correspondsToGost
 * @property string $gost
 * @property string $producer_uuid
 * @property string $producer_guid
 * @property string $tmOwner_uuid
 * @property string $tmOwner_guid
 * @property string $createDate
 * @property string $updateDate
 * @property object $productItem
 */
class VetisProductItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_product_item';
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
            [['last', 'active', 'status', 'productType', 'correspondsToGost'], 'integer'],
            [['createDate', 'updateDate'], 'safe'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'code', 'globalID', 'product_uuid', 'product_guid', 'subproduct_uuid', 'subproduct_guid', 'gost', 'producer_uuid', 'producer_guid', 'tmOwner_uuid', 'tmOwner_guid'], 'string', 'max' => 255],
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
            'globalID' => 'Global ID',
            'productType' => 'Product Type',
            'product_uuid' => 'Product Uuid',
            'product_guid' => 'Product Guid',
            'subproduct_uuid' => 'Subproduct Uuid',
            'subproduct_guid' => 'Subproduct Guid',
            'correspondsToGost' => 'Corresponds To Gost',
            'gost' => 'Gost',
            'producer_uuid' => 'Producer Uuid',
            'producer_guid' => 'Producer Guid',
            'tmOwner_uuid' => 'Tm Owner Uuid',
            'tmOwner_guid' => 'Tm Owner Guid',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
        ];
    }
    
    public function getProductItem()
    {
        return \yii\helpers\Json::decode($this->data);
    }
}
