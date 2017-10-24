<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use common\behaviors\UploadBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "relation_supp_rest".
 *
 * @property integer $id
 * @property integer $manager_id
 * @property integer $leader_id
 */
class OrderParticipants extends \yii\db\ActiveRecord {


    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'order_participants';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['order_id', 'profile_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'order_id' => 'ID заказа',
            'profile_id' => 'ID профиля пользователя',
        ];
    }


    public function getProfile(){
        return $this->hasOne(Profile::className(), ['id'=>'profile_id']);
    }
}
