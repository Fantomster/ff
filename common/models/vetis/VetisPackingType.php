<?php

namespace common\models\vetis;

use Yii;

/**
 * This is the model class for table "vetis_packing_type".
 *
 * @property string $uuid Идентификатор версии типа упаковки
 * @property string $guid Глобальный идентификатор упаковки
 * @property string $name Наименование упаковки
 * @property string $globalID Уникальный идентификатор упаковки
 */
class VetisPackingType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_packing_type';
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
            [['uuid', 'guid', 'name', 'globalID'], 'required'],
            [['uuid', 'guid', 'name'], 'string', 'max' => 255],
            [['globalID'], 'string', 'max' => 2],
            [['uuid'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uuid' => Yii::t('messages', 'Идентификатор версии типа упаковки'),
            'guid' => Yii::t('messages', 'Глобальный идентификатор упаковки'),
            'name' => Yii::t('messages', 'Наименование упаковки'),
            'globalID' => Yii::t('messages', 'Уникальный идентификатор упаковки'),
        ];
    }
}
