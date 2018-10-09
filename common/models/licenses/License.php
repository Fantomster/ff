<?php

namespace common\models\licenses;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "license".
 *
 * @property int $id Уникальный ID
 * @property string $name Наименование лицензии
 * @property int $is_active Флаг активности
 * @property string $created_at Дата создания
 * @property string $updated_at Дата обновления
 *
 * @property LicenseService[] $licenseServices
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
            'id' => 'Уникальный ID',
            'name' => 'Наименование лицензии',
            'is_active' => 'Флаг активности',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => \gmdate('Y-m-d H:i:s'),
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


    public static function checkLicenseByService(array $post): array
    {
        return self::checkLicense($post, true);
    }


    public static function checkLicenseByLicenseID(array $post): array
    {
        return self::checkLicense($post);
    }


    public static function checkLicense(array $post, bool $byServiceID = false): array
    {
        if ($byServiceID) {
            if (!isset($post['service_id'])) {
                throw new BadRequestHttpException("empty_param|service_id");
            }
        } else {
            if (!isset($post['license_id'])) {
                throw new BadRequestHttpException("empty_param|license_id");
            }
        }

        if (!isset($post['org_id'])) {
            throw new BadRequestHttpException("empty_param|org_id");
        }

        $licenses = License::find()->
        leftJoin('license_organization', 'license_organization.license_id=license.id')->
        leftJoin('license_service', 'license_service.license_id=license.id');
        if ($byServiceID) {
            $licenses = $licenses->where(['license_service.service_id' => $post['service_id']]);
        } else {
            $licenses = $licenses->where(['license.id' => $post['license_id']]);
        }

        $licenses = $licenses->andWhere(['license_organization.org_id' => $post['org_id']])->with('licenseOrganizations')->all();

        if (!$licenses) {
            throw new BadRequestHttpException("licenses not found");
        }

        $arr = [];
        $i = 0;
        foreach ($licenses as $license) {
            if ($license->licenseOrganizations) {
                $arr[$i]['license_id'] = $license->licenseOrganizations->license_id;
                $arr[$i]['td'] = $license->licenseOrganizations->td;
                $arr[$i]['object_id'] = $license->licenseOrganizations->object_id;
                $arr[$i]['status_id'] = $license->licenseOrganizations->status_id;
                $i++;
            }
        }

        return $arr;
    }


    public function getLicensesByServiceId(array $post): array
    {
        if (!isset($post['service_id'])) {
            throw new BadRequestHttpException("empty_param|service_id");
        }

        $licenses = License::find()->
        leftJoin('license_organization', 'license_organization.license_id=license.id')->
        leftJoin('license_service', 'license_service.license_id=license.id');
        $licenses = $licenses->where(['license_service.service_id' => $post['service_id']]);
        $licenses = $licenses->with('licenseOrganizations')->with('licenseServices')->asArray()->all();

        if (!$licenses) {
            throw new BadRequestHttpException("licenses not found");
        }
        return $licenses;
    }


    public function getLicensesByLicenseId(array $post): array
    {
        if (!isset($post['license_id'])) {
            throw new BadRequestHttpException("empty_param|license_id");
        }
        $db = Yii::$app->get('db_api');
        $services = (new Query())->select(['service_id'])
            ->from(['license_service'])->where(['license_id' => $post['license_id']])->all($db);
        if (!$services) {
            throw new BadRequestHttpException("licenses not found");
        }
        return $services;
    }
}
