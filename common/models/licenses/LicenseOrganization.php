<?php

namespace common\models\licenses;

use Yii;
use yii\db\ActiveRecord;
use common\models\Organization;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "license_organization".
 *
 * @property int     $id
 * @property int     $license_id        Указатель на ID лицензии
 * @property int     $org_id            Указатель на организацию
 * @property string  $fd                Начало действия услуги
 * @property string  $td                Окончание действия услуги
 * @property string  $created_at        Дата создания
 * @property string  $updated_at        Дата обновления
 * @property string  $object_id         (Идентификатор объекта во внешней системе
 * @property string  $outer_last_active Время последней зарегистрированной активности
 * @property int     $status_id         Статус лицензии - идентификатор
 * @property int     $is_deleted        Признак soft-delete
 *
 * @property License $license
 * @property LicenseService $licenseService
 */
class LicenseOrganization extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'license_organization';
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
            [['license_id', 'org_id', 'status_id', 'is_deleted'], 'integer'],
            [['fd', 'td', 'created_at', 'updated_at', 'outer_last_active'], 'safe'],
            [['object_id'], 'string', 'max' => 64],
            [['license_id'], 'exist', 'skipOnError' => true, 'targetClass' => License::class, 'targetAttribute' => ['license_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'license_id'        => 'Указатель на ID лицензии',
            'org_id'            => 'Указатель на организацию',
            'fd'                => 'Начало действия услуги',
            'td'                => 'Окончание действия услуги',
            'created_at'        => 'Дата создания',
            'updated_at'        => 'Дата обновления',
            'object_id'         => '(Идентификатор объекта во внешней системе',
            'outer_last_active' => 'Время последней зарегистрированной активности',
            'status_id'         => 'Статус лицензии - идентификатор',
            'is_deleted'        => 'Признак soft-delete',
        ];
    }


    public function behaviors()
    {
        return [
            [
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
    public function getLicense()
    {
        return $this->hasOne(License::class, ['id' => 'license_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'org_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicenseService()
    {
        return $this->hasOne(LicenseService::class, ['license_id' => 'license_id']);
    }
}
