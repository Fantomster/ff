<?php

namespace common\models\licenses;

use Exception;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "license".
 *
 * @property int                   $id         Уникальный ID
 * @property string                $name       Наименование лицензии
 * @property int                   $is_active  Флаг активности
 * @property string                $created_at Дата создания
 * @property string                $updated_at Дата обновления
 * @property LicenseService[]      $licenseServices
 * @property LicenseOrganization[] $licenseOrganizations
 */
class License extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'license';
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
            [['is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'Уникальный ID',
            'name'       => 'Наименование лицензии',
            'is_active'  => 'Флаг активности',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
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
    public function getLicenseServices()
    {
        return $this->hasMany(LicenseService::class, ['license_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicenseOrganizations()
    {
        return $this->hasOne(LicenseOrganization::class, ['license_id' => 'id']);
    }

    /**
     * @param $orgId
     * @param $serviceId
     * @return array
     * @throws \Exception
     */
    public static function checkByServiceId($orgId, $serviceId)
    {
        $now = new \DateTime();
        $license = (new Query())->select(['license.id', 'license.name', 'license.is_active', 'license.created_at', 'license.updated_at', 'license.login_allowed', 'max(lo.td) as td'])->from(self::tableName())
            ->leftJoin('license_organization lo', 'lo.license_id=license.id')
            ->leftJoin('license_service ls', 'ls.license_id=license.id')
            ->where(['lo.org_id' => $orgId, 'ls.service_id' => $serviceId, 'license.is_active' => 1])
            ->andWhere(['>', 'lo.td', $now->format('Y-m-d h:s:i')])
            ->groupBy(['license.id', 'license.name', 'license.is_active', 'license.created_at', 'license.updated_at', 'license.login_allowed'])
            ->indexBy('id')
            ->all(\Yii::$app->db_api);

        if (count($license) > 1) {
            throw new Exception('Organization having more than one different licenses');
        }

        return $license;
    }

    /**
     * Список всех лицензий, активных и просроченных
     *
     * @param       $orgId
     * @param array $service_ids
     * @return array
     */
    public static function getAllLicense($orgId, $service_ids = [])
    {
        $license = (new Query())
            ->select([
                'license.id',
                'license.name',
                '(CASE WHEN license.is_active = 1 AND lo.td > NOW() THEN 1 ELSE 0 END) as  is_active',
                'license.created_at',
                'license.updated_at',
                'license.login_allowed',
                'max(lo.td) as to_date'
            ])
            ->from(self::tableName())
            ->leftJoin('license_organization lo', 'lo.license_id=license.id')
            ->leftJoin('license_service ls', 'ls.license_id=license.id')
            ->where(['lo.org_id' => $orgId])
            ->groupBy([
                'license.id',
                'license.name',
                'license.is_active',
                'license.created_at',
                'license.updated_at',
                'license.login_allowed'
            ])
            ->indexBy('id');

        if (!empty($service_ids)) {
            $license->andWhere(['in', 'ls.service_id', $service_ids]);
        }

        return $license->all(\Yii::$app->db_api);
    }
}
