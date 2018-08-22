<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog_goods_blocked".
 *
 * @property int $id
 * @property int $cbg_id
 * @property int $owner_organization_id
 * @property string $created_at
 */
class CatalogGoodsBlocked extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'catalog_goods_blocked';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cbg_id', 'owner_organization_id'], 'required'],
            [['cbg_id', 'owner_organization_id'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'updatedAtAttribute' => false,
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('messages', 'ID'),
            'cbg_id' => Yii::t('messages', 'Cbg ID'),
            'owner_organization_id' => Yii::t('messages', 'Owner Organization ID'),
            'created_at' => Yii::t('messages', 'Created At'),
        ];
    }

    public static function getBlockedList($clientId)
    {
        $client = Organization::findOne($clientId);
        $root = isset($client->parent_id) ? Organization::findOne($client->parent_id) : $client;
        $result =  (new \yii\db\Query)
            ->select('cbg_id')
            ->from(CatalogGoodsBlocked::tableName())
            ->where(['owner_organization_id' => $root->id])
            ->createCommand()
            ->queryColumn();
        return !empty($result) ? $result : [0];
    }
}
