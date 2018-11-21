<?php

namespace common\models;

use common\models\licenses\License;
use common\models\licenses\LicenseOrganization;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%all_service}}".
 *
 * @property int $id
 * @property int $type_id
 * @property int $is_active
 * @property string $denom
 * @property string $vendor
 * @property string $created_at
 * @property string $updated_at
 * @property string $log_table
 * @property string $log_field
 *
 * @property AllServiceOperation[] $allServiceOperations
 */
class AllService extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%all_service}}';
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
            [['type_id', 'is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['denom', 'vendor', 'log_table', 'log_field'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type_id' => Yii::t('app', 'Type ID'),
            'is_active' => Yii::t('app', 'Is Active'),
            'denom' => Yii::t('app', 'Denom'),
            'vendor' => Yii::t('app', 'Vendor'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'log_table' => Yii::t('app', 'Log Table'),
            'log_field' => Yii::t('app', 'Log Field'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAllServiceOperations()
    {
        return $this->hasMany(AllServiceOperation::className(), ['service_id' => 'id']);
    }

    /**
     * @param $org_id
     * @param array $service_ids
     * @param null $is_active
     * @return array
     */
    public static function getAllServiceAndLicense($org_id, $service_ids = [], $is_active = null)
    {
        $services = self::find();

        if (!empty($service_ids)) {
            $services->andWhere(['in', 'id', $service_ids]);
        }

        return ArrayHelper::getColumn($services->all(\Yii::$app->db_api), function (self $service) use ($org_id, $is_active){
            return $service->getLicense($org_id, $is_active);
        });
    }

    /**
     * @param $org_id
     * @param $is_active
     * @return array
     */
    private function getLicense($org_id, $is_active) {
        $license = (new Query())
            ->select([
                'license.id',
                'license.name',
                '(CASE WHEN license.is_active = 1 AND lo.td > NOW() THEN 1 ELSE 0 END) as is_active',
                'license.created_at',
                'license.updated_at',
                'license.login_allowed',
                'max(lo.td) as to_date',
            ])
            ->from(License::tableName())
            ->leftJoin(['lo' => LicenseOrganization::tableName()], 'lo.license_id=license.id')
            ->where(['lo.org_id' => $org_id, 'license.service_id' => $this->id])
            ->groupBy([
                'license.id',
                'license.name',
                'license.is_active',
                'license.created_at',
                'license.updated_at',
                'license.login_allowed',
            ])
            ->indexBy('id');

        if (!is_null($is_active)) {
            $license->andWhere(['=', 'is_active', (int)$is_active]);
        }

        return [
            "id" => $this->id,
            "type_id" => $this->type_id,
            "is_active" => $this->is_active,
            "denom" => $this->denom,
            "vendor" => $this->vendor,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "license" => $license->one(\Yii::$app->db_api) ?: null
        ];
    }
}
