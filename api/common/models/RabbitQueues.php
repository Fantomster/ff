<?php

namespace api\common\models;

use Yii;

/**
 * This is the model class for table "rabbit_queues".
 *
 * @property int $id
 * @property string $consumer_class_name
 * @property int $organization_id
 * @property string $last_executed
 * @property string $start_executing
 * @property string $data_request
 * @property string $store_id
 */
class RabbitQueues extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rabbit_queues';
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
            [['consumer_class_name'], 'required'],
            [['organization_id'], 'integer'],
            [['last_executed', 'start_executing'], 'safe'],
            [['consumer_class_name', 'store_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('messages', 'ID'),
            'consumer_class_name' => Yii::t('messages', 'Consumer Class Name'),
            'organization_id' => Yii::t('messages', 'Organization ID'),
            'last_executed' => Yii::t('messages', 'Last Executed'),
            'start_executing' => Yii::t('messages', 'Start Executing'),
        ];
    }
}
