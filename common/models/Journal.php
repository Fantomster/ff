<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "journal".
 *
 * @property int                 $id
 * @property int                 $service_id      идентификатор сервиса
 * @property string              $operation_code  Код операции
 * @property int                 $user_id         идентификатор пользователя
 * @property int                 $organization_id идентификатор организации
 * @property string              $response        ответ
 * @property string              $log_guide       уникальный индентификатор записи в логе
 * @property string              $type            тип операции
 * @property string              $created_at      дата записи
 *
 * @property AllService          $service
 * @property AllServiceOperation $operation
 * @property User                $user
 * @property Organization        $organization
 */
class Journal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%journal}}';
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
            [['service_id', 'operation_code'], 'required'],
            [['service_id', 'user_id', 'organization_id'], 'integer'],
            [['response'], 'string'],
            [['created_at'], 'safe'],
            [['operation_code', 'log_guide', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'      => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ],
                'value'      => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                 => Yii::t('app', 'ID'),
            'service_id'         => Yii::t('app', 'Сервис'),
            'operation_code'     => Yii::t('app', 'Операция'),
            'user_id'            => Yii::t('app', 'User ID'),
            'organization_id'    => Yii::t('app', 'Организация'),
            'response'           => Yii::t('app', 'Response'),
            'log_guide'          => Yii::t('app', 'Log Guide'),
            'type'               => Yii::t('app', 'Результат'),
            'operation.denom'    => Yii::t('app', 'Операция'),
            'operation.comment'  => Yii::t('app', 'Комментарий к операции'),
            'record.response'    => Yii::t('app', 'Ответ сервера'),
            'record.request'     => Yii::t('app', 'Запрос'),
            'record.request_at'  => Yii::t('app', 'Дата запроса'),
            'record.response_at' => Yii::t('app', 'Дата ответа'),
            'created_at'         => Yii::t('app', 'Created At')
        ];
    }

    /**
     * Информация о сервисе
     *
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(AllService::className(), ['id' => 'service_id']);
    }

    /**
     * Информация об операции
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperation()
    {
        return $this->hasOne(AllServiceOperation::className(), ['service_id' => 'service_id', 'code' => 'operation_code']);
    }

    /**
     * Информация о пользователе
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Организация
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

    /**
     * Запись из внутреннего журнала, с подробными данными
     *
     * @return array
     */
    public function getRecord()
    {
        $log = (new Query())->select('log_table, log_field')
            ->from('all_service')
            ->where(['id' => $this->service_id])
            ->one(\Yii::$app->db_api);

        return (new Query())->select('*')
            ->from($log['log_table'])
            ->where([$log['log_field'] => $this->log_guide])
            ->one(\Yii::$app->db_api);
    }
}
