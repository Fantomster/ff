<?php

namespace common\models;

use common\models\licenses\License;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "all_service".
 *
 * @property int                   $id         Идентификатор записи в таблице
 * @property int                   $type_id    Тип учётного сервиса
 * @property int                   $is_active  Показатель состояния активности учётного сервиса (0 - не активен, 1 -
 *           активен)
 * @property string                $denom      Наименование учётного сервиса
 * @property string                $vendor     Название организации, которой принадлежит учётная система
 * @property string                $created_at Дата и время создания записи в таблице
 * @property string                $updated_at Дата и время последнего изменения записи в таблице
 * @property string                $log_table  Наименование таблицы лога учётного сервиса
 * @property string                $log_field  Наименование поля, где хранится идентификатор запроса в учётной системе
 *
 * @property AllServiceOperation[] $allServiceOperations
 * @property OuterDictionary[]     $outerDictionaries
 * @property OuterProductMap[]     $outerProductMaps
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
            'id'         => Yii::t('app', 'ID'),
            'type_id'    => Yii::t('app', 'Type ID'),
            'is_active'  => Yii::t('app', 'Is Active'),
            'denom'      => Yii::t('app', 'Denom'),
            'vendor'     => Yii::t('app', 'Vendor'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'log_table'  => Yii::t('app', 'Log Table'),
            'log_field'  => Yii::t('app', 'Log Field'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getOuterDictionaries()
    {
        return $this->hasMany(OuterDictionary::className(), ['service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOuterProductMaps()
    {
        return $this->hasMany(OuterProductMap::className(), ['service_id' => 'id']);
    }

    /**
     * @param       $org_id
     * @param array $service_ids
     * @param null  $is_active
     * @return array
     */
    public static function getAllServiceAndLicense($org_id, $service_ids = [], $is_active = null)
    {
        $services = self::find();

        if (!empty($service_ids)) {
            $services->andWhere(['in', 'id', $service_ids]);
        }

        $result = $services->asArray()->all(\Yii::$app->db_api);
        $licenses = ArrayHelper::index(License::getAllLicense($org_id, $service_ids, $is_active), 'service_id');

        foreach ($result as &$service) {
            if (isset($licenses[$service['id']])) {
                $service['license'] = $licenses[$service['id']];
            } else {
                $service['license'] = null;
            }
        }

        return $result;
    }
}
