<?php

namespace common\models\vetis;

use Yii;

/**
 * This is the model class for table "vetis_transport".
 *
 * @property int    $id
 * @property int    $org_id                 ИД организации
 * @property string $vehicle_number         Номер машины
 * @property string $trailer_number         Номер полуприцепа
 * @property string $container_number       Номер контейнера
 * @property int    $transport_storage_type Способ хранения
 */
class VetisTransport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_transport';
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
            [['org_id'], 'required'],
            [['org_id', 'transport_storage_type'], 'integer'],
            [['vehicle_number', 'trailer_number', 'container_number'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                     => 'ID',
            'org_id'                 => 'ИД организации',
            'vehicle_number'         => 'Номер машины',
            'trailer_number'         => 'Номер полуприцепа',
            'container_number'       => 'Номер контейнера',
            'transport_storage_type' => 'Способ хранения',
        ];
    }
}
