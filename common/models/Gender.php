<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "gender".
 *
 * @property int       $id          Идентификатор записи в таблице
 * @property string    $name_gender Наименование гендерного пола
 *
 * @property Profile[] $profilesAtGender
 */
class Gender extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%gender}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name_gender'], 'required'],
            [['name_gender'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
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
    public static function getList()
    {
        $models = Gender::find()
            ->select(['id', 'name_gender'])
            ->asArray()
            ->all();

        $models[] = ['id' => '0', 'name_allow' => 'Не указано'];
        return ArrayHelper::map($models, 'id', 'name_gender');
    }
}
