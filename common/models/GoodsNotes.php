<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_notes".
 *
 * @property integer $id
 * @property integer $rest_org_id
 * @property integer $catalog_base_goods_id
 * @property string $note
 * @property string $created_at
 * @property string $updated_at
 */
class GoodsNotes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods_notes';
    }
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rest_org_id', 'catalog_base_goods_id'], 'required'],
            [['rest_org_id', 'catalog_base_goods_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['note'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rest_org_id' => 'Rest Org ID',
            'catalog_base_goods_id' => 'Catalog Goods ID',
            'note' => 'Note',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
