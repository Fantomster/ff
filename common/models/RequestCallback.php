<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "request_callback".
 *
 * @property integer $id
 * @property integer $request_id
 * @property integer $supp_org_id
 * @property integer $price
 * @property string $comment
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Request $request
 */
class RequestCallback extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'request_callback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'supp_org_id', 'price'], 'required'],
            [['request_id', 'supp_org_id', 'price'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['comment'], 'string', 'max' => 255],
            [['request_id'], 'exist', 'skipOnError' => true, 'targetClass' => Request::className(), 'targetAttribute' => ['request_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'request_id' => 'Request ID',
            'supp_org_id' => 'Supp Org ID',
            'price' => 'Price',
            'comment' => 'Comment',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRequest()
    {
        return $this->hasOne(Request::className(), ['id' => 'request_id']);
    }
}
