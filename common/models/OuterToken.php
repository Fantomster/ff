<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%outer_token}}".
 *
 * @property int    $id
 * @property int    $service_id
 * @property int    $organization_id
 * @property string $token
 * @property string $created_at
 */
class OuterToken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%outer_token}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
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
            [['service_id', 'token'], 'required'],
            [['service_id', 'organization_id'], 'integer'],
            [['created_at'], 'safe'],
            [['token'], 'string', 'max' => 550],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('app', 'ID'),
            'service_id'      => Yii::t('app', 'Service ID'),
            'organization_id' => Yii::t('app', 'Organization ID'),
            'token'           => Yii::t('app', 'Token'),
            'created_at'      => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null,
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }
}
