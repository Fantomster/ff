<?php
namespace common\models;

use Yii;
/**
 * This is the model class for table "user_fcm_token".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $token
 * @property string $device_id
 * @property string $created_at
 * @property string $updated_at
 */
class UserFcmToken extends \yii\db\ActiveRecord
{
     /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_fcm_token}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token','device_id'],'required'],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['token', 'device_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'device_id' => 'Device Id',
            'token' => 'FCM Token',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления'

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
                'updatedAtAttribute' => 'updated_at',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($this->isNewRecord)
            {
                $this->user_id = Yii::$app->user->id;
                $this->created_at = gmdate("Y-m-d H:i:s");
            }
            return true;
        } else {
            return false;
        }
    }
}