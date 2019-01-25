<?php

namespace common\models;

use common\models\vetis\VetisCountry;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "country_vat".
 *
 * @property int          $id
 * @property string       $uuid
 * @property string       $vats
 * @property string       $created_at
 * @property string       $updated_at
 * @property int          $created_by_id
 * @property int          $updated_by_id
 * @property VetisCountry $country
 */
class CountryVat extends \yii\db\ActiveRecord
{

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'country_vat';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'vats'], 'required'],
            [['id', 'created_by_id', 'updated_by_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['uuid', 'vats'], 'string'],
            [['vats'], 'match', 'pattern' => '/^[0-9]{1,2}[.]?[0-9]{0,2}([;]{1}[0-9]{1,2}[.]?[0-9]{0,2})*$/',
                                'message' => Yii::t('error', 'backend.controllers.vats.not.correct', ['ru' => 'Перечень ставок налогов введён некорректно!'])],
            [['country'], 'exist', 'skipOnError' => true, 'targetClass' => VetisCountry::className(), 'targetAttribute' => ['uuid' => 'uuid']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('app', 'api.common.models.id', ['ru' => 'Идентификатор записи в таблице']),
            'uuid'          => Yii::t('app', 'api.common.models.country.uuid', ['ru' => 'Идентификатор государства']),
            'vats'          => Yii::t('app', 'api.common.models.vats', ['ru' => 'Величины налога']),
            'created_at'    => Yii::t('app', 'api.common.models.created.at', ['ru' => 'Дата и время создания записи в таблице']),
            'updated_at'    => Yii::t('app', 'api.common.models.updated.at', ['ru' => 'Дата и время последнего изменения записи в таблице']),
            'created_by_id' => Yii::t('app', 'api.common.models.created.by.id', ['ru' => 'Идентификатор пользователя, создавшего запись']),
            'updated_by_id' => Yii::t('app', 'api.common.models.updated.by.id', ['ru' => 'Идентификатор пользователя, последним изменившим запись']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(VetisCountry::class, ['uuid' => 'uuid']);
    }

    /** Возвращает массив стран, у которых не указан перечень ставок налогов
     *
     * @return \yii\db\ActiveQuery
     */
    public function getListNotVatCountries()
    {
        $ListVatCountries = CountryVat::find()->select('uuid')->column();
        $ListNotVatCountries = VetisCountry::find()->where(['not in', 'uuid', $ListVatCountries])->andWhere(['active' => 1])->orderBy('name')->all();
        return $ListNotVatCountries;
    }

    /** Возвращает количество стран, у которых не указан перечень ставок налогов
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCountNotVatCountries()
    {
        $ListVatCountries = CountryVat::find()->select('uuid')->column();
        $CountNotVatCountries = VetisCountry::find()->where(['not in', 'uuid', $ListVatCountries])->andWhere(['active' => 1])->count();
        return $CountNotVatCountries;
    }

}
