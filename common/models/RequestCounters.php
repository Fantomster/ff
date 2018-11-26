<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "request_counters".
 *
 * @property integer $id
 * @property integer $request_id
 * @property integer $user_id
 * @property string $created_at
 * @property string $updated_at
 * @property Request $request
 * @property User $user
 */
class RequestCounters extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'request_counters';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'user_id'], 'required'],
            [['request_id', 'user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['request_id'], 'exist', 'skipOnError' => true, 'targetClass' => Request::className(), 'targetAttribute' => ['request_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Get hits count
     *
     * @param $request_id
     */
    public static function hits($request_id) {
        return self::find()->where(['request_id' => $request_id])->count();
    }

    /**
     * @param $request_id
     * @param $user_id
     */
    public static function hit($request_id, $user_id) {
        if (!self::find()->where(['request_id' => $request_id, 'user_id' => $user_id])->exists()) {
            $requestCounters = new self();
            $requestCounters->request_id = $request_id;
            $requestCounters->user_id = $user_id;
            $requestCounters->save();
        }
    }
}
