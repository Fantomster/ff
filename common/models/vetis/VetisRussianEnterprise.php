<?php

namespace common\models\vetis;

use Yii;

/**
 * This is the model class for table "vetis_russian_enterprise".
 *
 * @property string $uuid
 * @property string $guid
 * @property int $last
 * @property int $active
 * @property int $type
 * @property string $next
 * @property string $previous
 * @property string $name
 * @property string $inn
 * @property string $kpp
 * @property string $addressView
 * @property string $data
 * @property object $enterprise
 */
class VetisRussianEnterprise extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_russian_enterprise';
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
            [['data'], 'string'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'inn', 'kpp', 'addressView'], 'string', 'max' => 255],
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
            'inn' => 'Inn',
            'kpp' => 'Kpp',
            'addressView' => 'Address View',
            'data' => 'Data',
        ];
    }

    public function getEnterprise()
    {
        return \yii\helpers\Json::decode($this->data);
    }

}
