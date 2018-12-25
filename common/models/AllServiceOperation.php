<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "all_service_operation".
 *
 * @property int        $id         Идентификатор записи в таблице
 * @property int        $service_id Идентификатор учётного сервиса (таблица all_service)
 * @property int        $code       Код операции
 * @property string     $denom      slug-псевдоним операции
 * @property string     $comment    Комментарий, описание сути операции
 *
 * @property AllService $service
 */
class AllServiceOperation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%all_service_operation}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['service_id', 'code'], 'integer'],
            [['comment'], 'string'],
            [['denom'], 'string', 'max' => 120],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => AllService::className(), 'targetAttribute' => ['service_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'service_id' => Yii::t('app', 'Service ID'),
            'code'       => Yii::t('app', 'Code'),
            'denom'      => Yii::t('app', 'Denom'),
            'comment'    => Yii::t('app', 'Comment'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(AllService::className(), ['id' => 'service_id']);
    }
}
