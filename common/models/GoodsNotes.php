<?php

namespace common\models;

/**
 * This is the model class for table "goods_notes".
 *
 * @property int    $id                    Идентификатор записи в таблице
 * @property int    $rest_org_id           Идентификатор организации-ресторана
 * @property string $note                  Пометка ресторана о товаре
 * @property string $created_at            Дата и время создания записи в таблице
 * @property string $updated_at            Дата и время последнего изменения записи в таблице
 * @property string $catalog_base_goods_id Идентификатор товара в главном каталоге (catalog_base_goods)
 */
class GoodsNotes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_notes}}';
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
            [['rest_org_id', 'catalog_base_goods_id'], 'required'],
            [['rest_org_id', 'catalog_base_goods_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['note'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'rest_org_id'           => 'Rest Org ID',
            'catalog_base_goods_id' => 'Catalog Goods ID',
            'note'                  => 'Note',
            'created_at'            => 'Created At',
            'updated_at'            => 'Updated At',
        ];
    }
}
