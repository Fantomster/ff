<?php
namespace common\models;

use Yii;
use amnah\yii2\user\models\UserToken as BaseModel;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class UserToken extends BaseModel
{
    public function rules() {
        $rules = parent::rules();
        $rules[] = [['pin'], 'required', 'on' => ['unique']];
        return $rules;
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), 
        [
            'pin' => Yii::t('user', 'PIN'),
        ]);
    }
    
    public static function generate($userId, $type, $data = null, $expireTime = null)
    {
        // attempt to find existing record
        // otherwise create new
        $checkExpiration = false;
        if ($userId) {
            $model = static::findByUser($userId, $type, $checkExpiration);
        } else {
            $model = static::findByData($data, $type, $checkExpiration);
        }
        if (!$model) {
            $model = new static();
        }

        // set/update data
        $model->user_id = $userId;
        $model->type = $type;
        $model->data = $data;
        $model->created_at = gmdate("Y-m-d H:i:s");
        $model->expired_at = $expireTime;
        $model->token = Yii::$app->security->generateRandomString();
        do{
        $model->pin = rand(1000,9999);
        }while (!$model->validate('pin'));
        $model->save();
        return $model;
    }
    
    public static function findByPIN($pin, $type, $checkExpiration = true)
    {
        return static::findBy("pin", $pin, $type, $checkExpiration);
    }
}
