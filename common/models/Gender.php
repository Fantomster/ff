<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "gender".
 *
 * @property integer $id
 * @property string $name_gender
 *
 */
class Gender extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'gender';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_gender'], 'required'],
            [['name_gender'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name_gender' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfilesAtGender()
    {
        return $this->hasMany(Profile::className(), ['gender' => 'id']);
    }
    
    /**
     * array of all genders
     * 
     * @return array
     */
    public static function getList() {
        $models = Gender::find()
                ->select(['id', 'name_gender'])
                ->asArray()
                ->all();

        return 
//        ArrayHelper::merge(
//                        [null => null], 
                ArrayHelper::map($models, 'id', 'name_gender');
       // );
    }
}
