<?php

namespace common\models;

use common\helpers\DBNameHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "outer_agent".
 *
 * @property int                         $id
 * @property string                      $outer_uid     Внешний ID
 * @property int                         $service_id    ID сервиса
 * @property string                      $name          Название
 * @property string                      $comment       Комментарий
 * @property int                         $vendor_id
 * @property int                         $store_id      ID склада
 * @property int                         $payment_delay Отложенная оплата в днях
 * @property int                         $org_id        ID организации
 * @property int                         $is_deleted    Статус удаления
 * @property string                      $created_at    Создано по GMT-0
 * @property string                      $updated_at    Изменено по GMT-0
 * @property string                      $inn           ИНН
 * @property string                      $kpp           КПП
 * @property Organization                $vendor
 * @property OuterStore                  $store
 * @property OuterAgentNameWaybill|array $nameWaybills
 */
class OuterAgent extends \yii\db\ActiveRecord
{

    const IS_DELETED_FALSE = 0;
    const IS_DELETED_TRUE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%outer_agent}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
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
            [['service_id', 'vendor_id', 'store_id', 'payment_delay', 'org_id', 'is_deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'comment'], 'string', 'max' => 255],
            [['inn', 'kpp'], 'string', 'max' => 15],
            [['outer_uid'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'outer_uid'     => 'Outer Uid',
            'service_id'    => 'Service ID',
            'name'          => 'Name',
            'comment'       => 'Comment',
            'vendor_id'     => 'Vendor ID',
            'store_id'      => 'Store ID',
            'payment_delay' => 'Payment Delay',
            'org_id'        => 'Org ID',
            'is_deleted'    => 'Is Deleted',
            'created_at'    => 'Created At',
            'updated_at'    => 'Updated At',
            'inn'           => 'Inn',
            'kpp'           => 'Kpp',
        ];
    }

    /**
     * @return array
     */
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
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getVendor()
    {
        if(empty($this->vendor_id)) {
            return null;
        }

        return (new ActiveQuery(Organization::class))
            ->from(DBNameHelper::getMainName() . '.' . Organization::tableName() . ' o')
            ->onCondition([
                'o.id' => $this->vendor_id
            ])->one();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(OuterStore::class, ['id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNameWaybills()
    {
        return $this->hasMany(OuterAgentNameWaybill::class, ['agent_id' => 'id']);
    }
}
