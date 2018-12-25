<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "allow".
 *
 * @property int    $id         Идентификатор записи в таблице
 * @property string $name_allow Наименование состояния согласия на определённые действия
 *
 * @property User        $usersAtEmailAllow
 * @property User        $usersAtSmsAllow
 */
class Allow extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%allow}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name_allow'], 'required'],
            [['name_allow'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'name_allow' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsersAtEmailAllow()
    {
        return $this->hasMany(User::className(), ['subscribe' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsersAtSmsAllow()
    {
        return $this->hasMany(User::className(), ['sms_subscribe' => 'id']);
    }

    /**
     * array of all allows
     *
     * @return array
     */
    public static function getList()
    {
        $models = Allow::find()
            ->select(['id', 'name_allow'])
            ->asArray()
            ->all();

        $models[] = ['id' => 0, 'name_allow' => 'Не указано'];
        return ArrayHelper::map($models, 'id', 'name_allow');
    }
}
