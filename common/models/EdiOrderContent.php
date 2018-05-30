<?php

namespace common\models;


/**
 * This is the model class for table "edi_order_content".
 *
 * @property integer $order_content_id
 * @property integer $doc_type
 * @property string $edi_supplier_article
 */
class EdiOrderContent extends \yii\db\ActiveRecord
{
    const DESADV = 1;
    const ALCDES = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'edi_order_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_content_id', 'doc_type'], 'integer'],
            [['order_content_id'], 'unique'],
            [['edi_supplier_article'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderConent()
    {
        return $this->hasOne(OrderContent::className(), ['id' => 'order_content_id']);
    }
}
