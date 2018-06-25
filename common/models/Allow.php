<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "allow".
 *
 * @property integer $id
 * @property string $name_allow
 *
 */
class Allow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'allow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_allow'], 'required'],
            [['name_allow'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name_allow' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfilesAtEmailAllow()
    {
        return $this->hasMany(Profile::className(), ['email_allow' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfilesAtSmsAllow()
    {
        return $this->hasMany(Profile::className(), ['sms_allow' => 'id']);
    }
    
    /**
     * array of all allows
     * 
     * @return array
     */
    public static function getList() {
        $models = Allow::find()
                ->select(['id', 'name_allow'])
                ->asArray()
                ->all();

        return 
//        ArrayHelper::merge(
//                        [null => null], 
                ArrayHelper::map($models, 'id', 'name_allow');
       // );
    }
}
