<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "franchisee".
 *
 * @property integer    $id
 * @property integer    $franchisee_id
 * @property string     $exception
 * @property string     $country
 * @property string     $locality
 * @property string     $administrative_area_level_1
 * @property string     $created_at
 * @property string     $updated_at
 * @property integer    $status
 * @property Franchisee $franchisee
 */
class FranchiseeGeo extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'franchisee_geo';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['franchisee_id', 'exception', 'status'], 'integer'],
            [['franchisee_id', 'country'], 'required'],
            [['country', 'locality', 'administrative_area_level_1'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                          => 'ID',
            'franchisee_id'               => Yii::t('app', 'common.models.franch_id', ['ru' => 'ID франчайзи']),
            'country'                     => Yii::t('app', 'common.models.country_two', ['ru' => 'Страна']),
            'locality'                    => Yii::t('app', 'common.models.city_two', ['ru' => 'Город']),
            'exception'                   => Yii::t('app', 'common.models.exception_two', ['ru' => 'Исключение']),
            'administrative_area_level_1' => Yii::t('app', 'common.models.region_two', ['ru' => 'Область']),
            'created_at'                  => Yii::t('app', 'common.models.created_three', ['ru' => 'Создано']),
            'updated_at'                  => Yii::t('app', 'common.models.refreshed_four', ['ru' => 'Обновлено']),
            'status'                      => Yii::t('app', 'common.models.status_franchesee_geo', ['ru' => 'Статус']),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchisee()
    {
        return $this->hasOne(Franchisee::className(), ['id' => 'franchisee_id']);
    }
}
