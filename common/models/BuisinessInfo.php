<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "buisiness_info".
 *
 * @property int          $id                    Идентификатор записи в таблице
 * @property int          $organization_id       Идентификатор организации
 * @property string       $info                  Поле для заметок об организации
 * @property string       $created_at            Дата и время создания записи в таблице
 * @property string       $updated_at            Дата и время последнего изменения записи в таблице
 * @property string       $signed                Показатель состояния подписи организации на франшизу (0 - не
 *           подписана, 1 - подписана)
 * @property string       $legal_entity          Юридическое название организации
 * @property string       $legal_address         Юридический адрес организации
 * @property string       $legal_email           Официальный электронный ящик организации
 * @property string       $inn                   ИНН организации
 * @property string       $kpp                   КПП организации
 * @property string       $ogrn                  ОГРН организации
 * @property string       $bank_name             Наименование банка, в котором обслуживается организация
 * @property string       $bik                   БИК банка, в котором обслуживается организация
 * @property string       $correspondent_account Корреспондентский счёт банка, в котором обслуживается организация
 * @property string       $checking_account      Расчётный счёт организации в данном банке
 * @property string       $phone                 Телефон организации
 * @property string       $reward                Процент с оборота организации
 *
 * @property Organization $organization
 */
class BuisinessInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%buisiness_info}}';
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
            [['organization_id'], 'integer'],
            [['organization_id'], 'unique'],
            [['info'], 'string'],
            [['reward'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['signed', 'legal_entity', 'legal_address', 'legal_email', 'inn', 'kpp', 'ogrn', 'bank_name', 'bik', 'correspondent_account', 'checking_account', 'phone'], 'string', 'max' => 255],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'organization_id'       => 'Organization ID',
            'info'                  => Yii::t('app', 'common.models.field', ['ru' => 'Поле для заметок']),
            'created_at'            => Yii::t('app', 'common.models.created', ['ru' => 'Создано']),
            'updated_at'            => Yii::t('app', 'common.models.refreshed', ['ru' => 'Обновлено']),
            'signed'                => Yii::t('app', 'common.models.subscriber', ['ru' => 'Подписант']),
            'legal_entity'          => Yii::t('app', 'common.models.jur_name', ['ru' => 'Юридическое название']),
            'legal_address'         => Yii::t('app', 'common.models.jur_address', ['ru' => 'Юридический адрес']),
            'legal_email'           => Yii::t('app', 'common.models.official_email', ['ru' => 'Официальный email']),
            'inn'                   => Yii::t('app', 'common.models.inn', ['ru' => 'ИНН']),
            'kpp'                   => Yii::t('app', 'common.models.kpp', ['ru' => 'КПП']),
            'ogrn'                  => Yii::t('app', 'common.models.ogrn', ['ru' => 'ОГРН']),
            'bank_name'             => Yii::t('app', 'common.models.bank', ['ru' => 'Банк']),
            'bik'                   => Yii::t('app', 'common.models.bik', ['ru' => 'БИК']),
            'correspondent_account' => Yii::t('app', 'common.models.rs', ['ru' => 'р/с']),
            'checking_account'      => Yii::t('app', 'common.models.ks', ['ru' => 'к/с']),
            'phone'                 => Yii::t('app', 'common.models.phone', ['ru' => 'Телефон']),
            'reward'                => Yii::t('app', 'common.models.percent', ['ru' => 'Процент с оборота']),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

    /**
     * Sets organization
     *
     * @param $organization
     * @return bool
     */
    public function setOrganization($organization)
    {
        $this->organization_id = $organization->id;
        return $this->save();
    }
}
