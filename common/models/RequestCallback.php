<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "request_callback".
 *
 * @property integer      $id
 * @property integer      $request_id
 * @property integer      $supp_org_id
 * @property integer      $supp_user_id
 * @property string       $price
 * @property string       $comment
 * @property string       $created_at
 * @property string       $updated_at
 * @property Organization $organization
 * @property Request      $request
 * 
 * @property User[]       $recipientsListForVendor
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
            [['request_id', 'supp_org_id', 'supp_user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['price'], 'string', 'max' => 100],
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
            'id'           => 'ID',
            'request_id'   => 'Request ID',
            'supp_org_id'  => 'Supp Org ID',
            'supp_user_id' => 'Supp User ID',
            'price'        => 'Price',
            'comment'      => 'Comment',
            'created_at'   => 'Created At',
            'updated_at'   => 'Updated At',
        ];
    }

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
     * @return \yii\db\ActiveQuery
     */
    public function getRequest()
    {
        return $this->hasOne(Request::className(), ['id' => 'request_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'supp_user_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!is_a(Yii::$app, 'yii\console\Application')) {
            if ($insert) {
                \api\modules\v1\modules\mobile\components\notifications\NotificationRequest::actionRequestCallback($this, true);
            }
        }
    }
    
    public function getRecipientsListForVendor()
    {
        return User::find()
                        ->join('LEFT JOIN', RelationUserOrganization::tableName() . ' as ruo', User::tableName() . '.organization_id = ruo.organization_id')
                        ->where([
                            'ruo.organization_id' => $this->supp_org_id,
                            'ruo.role_id'         => Role::ROLE_SUPPLIER_MANAGER,
                        ])
                        ->all();
    }

}
