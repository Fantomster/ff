<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "{{%sms_code_change_mobile}}".
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $phone
 * @property int    $code
 * @property int    $attempt
 * @property string $created_at
 * @property string $updated_at
 * @property User   $user
 */
class SmsCodeChangeMobile extends \yii\db\ActiveRecord
{
    public $wait_time = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sms_code_change_mobile}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'phone', 'code'], 'required'],
            [['user_id', 'code', 'attempt'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['phone'], 'string', 'max' => 255],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'user_id'    => Yii::t('app', 'User ID'),
            'phone'      => Yii::t('app', 'Phone'),
            'code'       => Yii::t('app', 'Code'),
            'attempt'    => Yii::t('app', 'Attempt'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Проверка кода и смена номера
     *
     * @param $code
     * @return bool|false|int
     */
    public function checkCode($code)
    {
        if ($code == $this->code) {
            return true;
        }
        return false;
    }

    /**
     * Смена номера телефона
     *
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function changePhoneUser()
    {
        $this->user->profile->phone = $this->phone;
        if ($this->user->profile->save()) {
            return $this->delete();
        }
        return false;
    }

    /**
     * Следующая попытка
     */
    public function setAttempt()
    {
        if ($this->attempt == 10) {
            $this->attempt = 1;
        } else {
            $this->attempt = $this->attempt + 1;
        }

        $this->updated_at = new Expression('NOW()');
    }

    /**
     * Не заблокировали ли мы этот смс слальщика
     */
    public function accessAllow()
    {
        if ($this->attempt >= 10) {
            $this->date_diff(date('Y-m-d H:i:s'), $this->updated_at);
            if ($this->wait_time < 300 && $this->wait_time > 0) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    private function date_diff($date1, $date2)
    {
        $diff = strtotime($date1) - strtotime($date2);
        $this->wait_time = round($diff, 0);
        return $this->wait_time;
    }
}
