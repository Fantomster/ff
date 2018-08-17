<?php

namespace common\models\vetis;

use Yii;

/**
 * This is the model class for table "vetis_product_by_type".
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
 * @property int $productType
 * @property string $createDate
 * @property string $updateDate
 * @property object product
 */
class VetisProductByType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_product_by_type';
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
            [['last', 'active', 'status', 'productType'], 'integer'],
            [['createDate', 'updateDate'], 'safe'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'code'], 'string', 'max' => 255],
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
            'productType' => 'Product Type',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
        ];
    }
    
    public function getProduct()
    {
        return \yii\helpers\Json::decode($this->data);
    }
    
    public static function getProductByTypeList($type)
    {
        $models = self::find()
                ->select(['uuid', 'name'])
                ->where(['active' => true, 'last' => true, 'productType' => $type])
                ->asArray()
                ->all();

        return ArrayHelper::map($models, 'uuid', 'name');
    }
}
