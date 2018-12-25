<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "franchisee_geo".
 *
 * @property int        $id                          Идентификатор записи в таблице
 * @property int        $franchisee_id               Идентификатор франчайзи
 * @property string     $country                     Наименование государства, в котором находится франчайзи
 * @property string     $locality                    Наименование населённого пункта, в котором находится франчайзи
 * @property int        $exception                   Показатель исключения населённого пункта из показа и поиска (0 -
 *           не исключать, 1 - исключать)
 * @property string     $created_at                  Дата и время создания записи в таблице
 * @property string     $updated_at                  Дата и время последнего изменения записи в таблице
 * @property string     $administrative_area_level_1 Наименование региона 1 уровня государства, в котором находится
 *           франчайзи
 * @property int        $status                      Показатель статуса активности (не используется)
 *
 * @property Franchisee $franchisee
 */
class FranchiseeGeo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%franchisee_geo}}';
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
