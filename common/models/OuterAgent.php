<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "outer_agent".
 *
 * @property int $id
 * @property string $outer_uid Внешний ID
 * @property int $service_id ID сервиса
 * @property string $name Название
 * @property string $comment Комментарий
 * @property int $vendor_id
 * @property int $store_id ID склада
 * @property int $payment_delay Отложенная оплата в днях
 * @property int $org_id ID организации
 * @property int $is_deleted Статус удаления
 * @property string $created_at Создано по GMT-0
 * @property string $updated_at Изменено по GMT-0
 * @property string $inn ИНН
 * @property string $kpp КПП
 */
class OuterAgent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outer_agent';
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
            'id' => 'ID',
            'outer_uid' => 'Outer Uid',
            'service_id' => 'Service ID',
            'name' => 'Name',
            'comment' => 'Comment',
            'vendor_id' => 'Vendor ID',
            'store_id' => 'Store ID',
            'payment_delay' => 'Payment Delay',
            'org_id' => 'Org ID',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'inn' => 'Inn',
            'kpp' => 'Kpp',
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

    public function getVendor(){
    	return $this->hasOne(Organization::class, ['id' => 'vendor_id']);
    }

    public function getStore(){
    	return $this->hasOne(OuterStore::class, ['id' => 'store_id']);
    }

    public function getNameWaybills(){
    	return $this->hasMany(OuterAgentNameWaybill::class, ['agent_id' => 'id']);
    }
}
