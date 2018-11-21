<?php

namespace common\models\licenses;

use api_web\components\Registry;
use api_web\helpers\WebApiHelper;
use Exception;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

/**
 * This is the model class for table "license".
 *
 * @property int                   $id         Уникальный ID
 * @property string                $name       Наименование лицензии
 * @property int                   $is_active  Флаг активности
 * @property int                   $service_id id service
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
            [['is_active', 'service_id', 'login_allowed'], 'integer'],
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
            ->where(['lo.org_id' => $orgId, 'license.service_id' => $serviceId, 'license.is_active' => 1])
            ->andWhere(['>', 'lo.td', $now->format('Y-m-d h:s:i')])
            ->groupBy(['license.id', 'license.name', 'license.is_active', 'license.created_at', 'license.updated_at', 'license.login_allowed'])
            ->indexBy('id')
            ->all(\Yii::$app->db_api);

        if (count($license) > 1) {
            throw new Exception('Organization having more than one same licenses, please delete not actual');
        }

        return current($license);
    }

    /**
     * Список всех лицензий, активных и просроченных
     *
     * @param       $orgId
     * @param array $service_ids
     * @param null  $is_active
     * @return array
     */
    public static function getAllLicense($orgId, $service_ids = [], $is_active = null)
    {
        $license = (new Query())
            ->select([
                'license.id',
                'license.name',
                '(CASE WHEN license.is_active = 1 AND lo.td > NOW() THEN 1 ELSE 0 END) as  is_active',
                'license.created_at',
                'license.updated_at',
                'license.login_allowed',
                'max(lo.td) as to_date',
                'license.service_id',
            ])
            ->from(self::tableName())
            ->leftJoin('license_organization lo', 'lo.license_id=license.id')
            ->where(['lo.org_id' => $orgId])
            ->groupBy([
                'license.id',
                'license.name',
                'license.is_active',
                'license.created_at',
                'license.updated_at',
                'license.login_allowed',
                'license.service_id',
            ])
            ->indexBy('id');

        if (!empty($service_ids)) {
            $license->andWhere(['in', 'license.service_id', $service_ids]);
        }

        if (!is_null($is_active)) {
            $license->andWhere(['=', 'is_active', (int)$is_active]);
            $license->orderBy(['to_date' => SORT_DESC]);
        }

        return $license->all(\Yii::$app->db_api);
    }

    /**
     * Проверка на активную лицензию микскарта
     *
     * @param $orgId
     * @return string
     */
    public static function getDateMixCartLicense($orgId)
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

        $license->andWhere(['in', 'license.id', Registry::$mc_licenses_id]);
        $license->andWhere(['=', 'is_active', 1]);
        $license->orderBy(['to_date' => SORT_DESC]);

        $result = $license->all(\Yii::$app->db_api);

        if (!empty($result)) {
            return current($result)['to_date'];
        } else {
            return date('Y-m-d H:i:s', strtotime("-1 day"));
        }
    }

    /**
     * Проверка на активную лицензию микскарта нескольких организаций
     *
     * @param $orgIds
     * @return array
     */
    public static function getMixCartLicenses($orgIds = [])
    {
        if (empty($orgIds)) {
            return [];
        }

        $license = (new Query())
            ->select([
                'license.id',
                'license.name',
                '(CASE WHEN license.is_active = 1 AND lo.td > NOW() THEN 1 ELSE 0 END) as  is_active',
                'license.created_at',
                'license.updated_at',
                'license.login_allowed',
                'max(lo.td) as to_date',
                'lo.org_id',
            ])
            ->from(self::tableName())
            ->leftJoin('license_organization lo', 'lo.license_id=license.id')
            ->where(['lo.org_id' => $orgIds])
            ->groupBy([
                'license.id',
                'license.name',
                'license.is_active',
                'license.created_at',
                'license.updated_at',
                'license.login_allowed',
                'lo.org_id'
            ])
            ->indexBy('org_id');

        $license->andWhere(['in', 'license.id', Registry::$mc_licenses_id]);
        $license->andWhere(['=', 'is_active', 1]);
        $license->orderBy(['to_date' => SORT_DESC]);

        return $license->all(\Yii::$app->db_api);
    }

    /**
     * Проверка лицензии MixCart
     *
     * @param $org_id
     * @throws HttpException
     */
    public static function checkMixCartLicenseResponse($org_id)
    {
        $licenseDate = self::getDateMixCartLicense($org_id);
        \Yii::$app->response->headers->add('License-Expire', \Yii::$app->formatter->asDatetime($licenseDate, WebApiHelper::$formatDate));
        \Yii::$app->response->headers->add('License-Manager-Phone', \Yii::$app->params['licenseManagerPhone']);
        #Проверяем, не стухла ли лицензия
        if (strtotime($licenseDate) < strtotime(date('Y-m-d H:i:s'))) {
            throw new HttpException(402, 'license.payment_required', 402);
        }
    }

    /**
     * @param       $org_id
     * @param array $service_ids
     * @throws HttpException
     */
    public static function checkLicense($org_id, $service_ids = [])
    {
        $result = self::getAllLicense($org_id, $service_ids, true);
        if (!empty($result)) {
            $l = current($result);
            $licenseDate = $l['to_date'];
            $licenseName = $l['name'];
        } else {
            $licenseDate = date('Y-m-d H:i:s', strtotime("-1 day"));
            $licenseName = "";
        }

        \Yii::$app->response->headers->add('License-Expire', \Yii::$app->formatter->asDatetime($licenseDate, WebApiHelper::$formatDate));
        \Yii::$app->response->headers->add('License-Manager-Phone', \Yii::$app->params['licenseManagerPhone']);
        #Проверяем, не стухла ли лицензия
        if (strtotime($licenseDate) < strtotime(date('Y-m-d H:i:s'))) {
            $message = \Yii::t('api_web', 'license.payment_required');
            if ($licenseName) {
                $message .= ': ' . $licenseName;
            }
            throw new HttpException(400, $message, 400);
        }
    }
}
