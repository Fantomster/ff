<?php

namespace common\models\egais;

use common\models\Organization;
use Yii;

/**
 * This is the model class for table "egais_write_off_history".
 *
 * @property int                   $id                Идентификатор записи в таблице
 * @property int                   $org_id            Идентификатор организации
 * @property int                   $act_id            Идентификатор акта о списании товаров
 * @property int                   $product_id        Идентификатор товара в системе Mixcart
 * @property int                   $type_write_off_id Идентификатор типа списания товаров
 * @property int                   $status            Показатель статуса списания товара (0 - не списан, 1 - списан)
 * @property string                $created_at        Дата и время создания записи в таблице
 * @property string                $updated_at        Дата и время последнего изменения записи в таблице
 * @property EgaisWriteOff         $act
 * @property EgaisProductOnBalance $product
 * @property EgaisTypeWriteOff     $typeWriteOff
 * @property Organization          $organization
 */
class EgaisWriteOffHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'egais_write_off_history';
    }

    /**
     * @return object
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
            [['org_id', 'act_id', 'product_id', 'type_write_off_id'], 'required'],
            [['org_id', 'act_id', 'product_id', 'type_write_off_id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['act_id'], 'exist', 'skipOnError' => true, 'targetClass' => EgaisWriteOff::class, 'targetAttribute' => ['act_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => EgaisProductOnBalance::class, 'targetAttribute' => ['product_id' => 'id']],
            [['type_write_off_id'], 'exist', 'skipOnError' => true, 'targetClass' => EgaisTypeWriteOff::class, 'targetAttribute' => ['type_write_off_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'org_id'            => 'Org ID',
            'act_id'            => 'Act ID',
            'product_id'        => 'Product ID',
            'type_write_off_id' => 'Type Write Off ID',
            'status'            => 'Status',
            'created_at'        => 'Created At',
            'updated_at'        => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAct()
    {
        return $this->hasOne(EgaisWriteOff::class, ['id' => 'act_id']);
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
    public function getProduct()
    {
        return $this->hasOne(EgaisProductOnBalance::class, ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypeWriteOff()
    {
        return $this->hasOne(EgaisTypeWriteOff::class, ['id' => 'type_write_off_id']);
    }

    public function prepareProduct()
    {
        return [
            "id"             => $this->id,
            "organization"   => [
                'id'   => $this->organization->id,
                'name' => $this->organization->name
            ],
            "act"            => $this->act,
            "product"        => $this->product,
            "type_write_off" => [
                'id'   => $this->typeWriteOff->id,
                'name' => $this->typeWriteOff->type
            ],
            "status"         => $this->status,
            "created_at"     => $this->created_at,
            "updated_at"     => $this->updated_at,
        ];
    }
}
