<?php

namespace common\models\vetis;

use Yii;

/**
 * This is the model class for table "vetis_business_entity".
 *
 * @property string $uuid
 * @property string $guid
 * @property int $last
 * @property int $active
 * @property int $type
 * @property string $next
 * @property string $previous
 * @property string $name
 * @property string $fullname
 * @property string $fio
 * @property string $inn
 * @property string $kpp
 * @property string $addressView
 * @property object $businessEntity
 */
class VetisBusinessEntity extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_business_entity';
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
            [['last', 'active', 'type'], 'integer'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'fullname', 'fio', 'inn', 'kpp', 'addressView'], 'string', 'max' => 255],
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
            'type' => 'Type',
            'next' => 'Next',
            'previous' => 'Previous',
            'name' => 'Name',
            'fullname' => 'Fullname',
            'fio' => 'Fio',
            'inn' => 'Inn',
            'kpp' => 'Kpp',
            'addressView' => 'Address View',
        ];
    }

    public function getBusinessEntity()
    {
        return \yii\helpers\Json::decode($this->data);
    }

}
