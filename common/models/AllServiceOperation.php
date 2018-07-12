<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%all_service_operation}}".
 *
 * @property int $id
 * @property int $service_id
 * @property int $code
 * @property string $denom
 * @property string $comment
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
            'id' => Yii::t('app', 'ID'),
            'service_id' => Yii::t('app', 'Service ID'),
            'code' => Yii::t('app', 'Code'),
            'denom' => Yii::t('app', 'Denom'),
            'comment' => Yii::t('app', 'Comment'),
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
