<?php

namespace common\models\licenses;

use api_web\components\Registry;
use api_web\helpers\WaybillHelper;
use api_web\helpers\WebApiHelper;
use Exception;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

/**
 * This is the model class for table "license".
 *
 * @property int                   $id          Уникальный ID
 * @property string                $name        Наименование лицензии
 * @property int                   $is_active   Флаг активности
 * @property int                   $sort_index  Индекс сортировки
 * @property int                   $service_id  id service
 * @property string                $created_at  Дата создания
 * @property string                $updated_at  Дата обновления
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
            [['is_active', 'service_id', 'login_allowed', 'sort_index'], 'integer'],
            [['created_at', 'updated_at', 'sort_index'], 'safe'],
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
            'sort_index' => 'Индекс сортировки'
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
            ->andWhere('lo.is_deleted = 0 OR lo.is_deleted is null')
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
                '(CASE WHEN license.is_active = 1 AND max(lo.td) > NOW() THEN 1 ELSE 0 END) as  is_active_license',
                'license.created_at',
                'license.updated_at',
                'license.login_allowed',
                'max(lo.td) as to_date',
                'license.service_id',
                'lo.org_id',
            ])
            ->from(self::tableName())
            ->leftJoin('license_organization lo', 'lo.license_id=license.id')
            ->where(['lo.org_id' => $orgId])
            ->andWhere('coalesce(lo.is_deleted, 0) <> 1')
            ->groupBy([
                'license.id',
                'license.name',
                'license.is_active',
                'license.created_at',
                'license.updated_at',
                'license.login_allowed',
                'license.service_id',
                'lo.org_id'
            ])
            ->orderBy(['license.sort_index' => SORT_DESC]);

        if (!empty($service_ids)) {
            $license->andWhere(['in', 'license.service_id', $service_ids]);
        }

        if (!is_null($is_active)) {
            $license->having(['=', 'is_active_license', (int)$is_active]);
        }

        return $license->all(\Yii::$app->db_api);
    }

    /**
     * Возвращает дату, до которой активна лицензия МиксКарт
     *
     * @param $orgId
     * @return string
     */
    public static function getDateMixCartLicense($orgId)
    {
        $result = self::getMixCartLicenses($orgId);
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
        $licenses = self::getAllLicense($orgIds, Registry::$mc_licenses_id, true);
        $licenses = ArrayHelper::index($licenses, 'org_id');
        foreach ($licenses as &$item) {
            $item['phone_manager'] = \Yii::$app->params['licenseManagerPhone'];
        }
        return $licenses;
    }

    /**
     * @param       $org_id
     * @param array $service_ids
     * @return false|string
     * @throws HttpException
     */
    public static function checkLicense($org_id, $service_ids = [])
    {
        $result = self::getAllLicense($org_id, $service_ids, true);
        if (!empty($result)) {
            $l = current($result);
            $licenseDate = $l['to_date'];
        } else {
            $licenseDate = date('Y-m-d H:i:s', strtotime("-1 day"));
        }

        $licenseName = null;
        if (!empty($service_ids)) {
            $service_id = is_array($service_ids) ? current($service_ids) : $service_ids;
            $l = self::findOne(['service_id' => $service_id]);
            if (!empty($l)) {
                $licenseName = $l->name;
            }
        }

        #Проверяем, не стухла ли лицензия
        if (strtotime($licenseDate) < strtotime(date('Y-m-d H:i:s'))) {
            $message = \Yii::t('api_web', 'license.payment_required');
            if ($licenseName) {
                $message .= ': ' . $licenseName;
            }
            throw new HttpException(400, $message, 400);
        }
        return $licenseDate;
    }

    /**
     * Проверка лицензии для входа в систему
     *
     * @param $org_id
     * @throws HttpException
     */
    public static function checkEnterLicenseResponse($org_id)
    {
        try {
            $licenseDate = self::checkLicense($org_id, Registry::$allow_enter_services);
            $h = \Yii::$app->response->headers;
            if (!$h->has('License-Expire')) {
                $h->add('License-Expire', WebApiHelper::asDatetime($licenseDate));
            }
            if (!$h->has('License-Manager-Phone')) {
                $h->add('License-Manager-Phone', \Yii::$app->params['licenseManagerPhone']);
            }
        } catch (HttpException $e) {
            throw new HttpException(402, $e->getMessage(), 402);
        }
    }
}
