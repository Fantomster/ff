<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "request_callback".
 *
 * @property integer $id
 * @property integer $request_id
 * @property integer $supp_org_id
 * @property integer $supp_user_id
 * @property string $price
 * @property string $comment
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organization $organization
 * @property Request $request
 */
class RequestCallback extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'request_callback';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['request_id', 'supp_org_id', 'price'], 'required'],
            [['request_id', 'supp_org_id', 'supp_user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['price'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['price'], 'number', 'min' => 0.1],
            [['comment'], 'string', 'max' => 255],
            [['request_id'], 'exist', 'skipOnError' => true, 'targetClass' => Request::className(), 'targetAttribute' => ['request_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'request_id' => 'Request ID',
            'supp_org_id' => 'Supp Org ID',
            'supp_user_id' => 'Supp User ID',
            'price' => 'Price',
            'comment' => 'Comment',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRequest() {
        return $this->hasOne(Request::className(), ['id' => 'request_id']);
    }

    public function getOrganization() {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'supp_user_id']);
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if (!is_a(Yii::$app, 'yii\console\Application')) {
             if ($insert) {
                \api\modules\v1\modules\mobile\components\notifications\NotificationRequest::actionRequestCallback($this, true);
            }
        }
    }

}
