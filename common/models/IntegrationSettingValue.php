<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "integration_setting_value".
 *
 * @property int                $id         Уникальный идентификатор
 * @property int                $setting_id Указатель на настройку
 * @property int                $org_id     Указатель на организацию
 * @property string             $value      Значение настройки для данной организации
 * @property string             $created_at
 * @property string             $updated_at
 * @property Organization       $organization
 * @property IntegrationSetting $setting
 */
class IntegrationSettingValue extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integration_setting_value}}';
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
            [['setting_id', 'org_id'], 'required'],
            [['setting_id', 'org_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['value'], 'string', 'max' => 255],
            [['setting_id'], 'exist', 'skipOnError'     => true, 'targetClass' => IntegrationSetting::class,
                                      'targetAttribute' => ['setting_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'Уникальный идентификатор',
            'setting_id' => 'Указатель на настройку',
            'org_id'     => 'Указатель на организацию',
            'value'      => 'Значение настройки для данной организации',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'org_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSetting()
    {
        return $this->hasOne(IntegrationSetting::class, ['id' => 'setting_id']);
    }

    /**
     * Get settings for organization Id, make filtering by integration_settings.name in array $settingsNames parameter.
     * Usage:
     *        IntegrationSettingValue::getSettingsForOrg(1, $order->client_id, [
     *            'rkws_useWinEncoding',
     *            'iiko_auto_unload_invoice',
     *            'rkws_outer_address',
     *            'merc_outer_phone',
     *            'merc_auth_password',
     *            'merc_api_key',
     *        ]);
     *
     * @param int|null $orgId
     * @param array    $settingNames for filtering by needed setting name
     * @return array|string [key = integration_setting.name => value] | if count($settingNames) == 1 -> return string
     */
    public static function getSettingsByServiceId(int $serviceId, int $orgId = null, array $settingNames = [])
    {
        $orgId = $orgId ?? \Yii::$app->user->identity->organization_id;
        $settingNames = $settingNames ?? ['*'];
        $dbResult = (new Query())->select(['isv.value', 'is.name name'])->from(self::tableName() . ' isv')->leftJoin
        (IntegrationSetting::tableName() . ' is', '`is`.`id`=`isv`.`setting_id`')
            ->where(['isv.org_id' => $orgId])
            ->andFilterWhere(['is.name' => $settingNames, 'is.service_id' => $serviceId])
            ->all(\Yii::$app->db_api);

        if (count($dbResult) > 1 && (count($settingNames) > 1 || empty($settingNames))) {
            foreach ($dbResult as $item) {
                $result[$item['name']] = $item['value'];
            }
        } else {
            $result = reset($dbResult)['value'];
        }

        return $result;
    }
}
