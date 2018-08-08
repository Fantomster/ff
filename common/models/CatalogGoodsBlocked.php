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
}
