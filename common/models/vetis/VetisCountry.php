<?php

namespace common\models\vetis;

use Yii;

/**
 * This is the model class for table "vetis_country".
 *
 * @property string $uuid
 * @property string $guid
 * @property int $last
 * @property int $active
 * @property int $status
 * @property string $next
 * @property string $previous
 * @property string $name
 * @property string $fullName
 * @property string $englishName
 * @property string $code
 * @property string $code3
 * @property string $createDate
 * @property string $updateDate
 * @property object $country
 */
class VetisCountry extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_country';
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
            [['uuid', 'guid', 'next', 'previous', 'name', 'fullName', 'englishName'], 'string', 'max' => 255],
            [['code', 'code3'], 'string', 'max' => 5],
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
            'fullName' => 'Full Name',
            'englishName' => 'English Name',
            'code' => 'Code',
            'code3' => 'Code3',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
        ];
    }
    
    public function getCountry()
    {
        return \yii\helpers\Json::decode($this->data);
    }
    
    public static function getCountryList() {
        $models = self::find()
                ->select(['uuid', 'name'])
                ->where(['active' => true, 'last' => true])
                ->asArray()
                ->all();

        return ArrayHelper::map($models, 'uuid', 'name');
    }
}
