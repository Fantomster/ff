<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "{{%journal}}".
 *
 * @property int $id
 * @property int $service_id
 * @property string $operation_code
 * @property int $user_id
 * @property int $organization_id
 * @property string $response
 * @property string $log_guide
 * @property string $type
 * @property string $created_at
 * @property AllServiceOperation $operation
 * @property array $record
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
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'service_id' => Yii::t('app', 'Service ID'),
            'operation_code' => Yii::t('app', 'Operation Code'),
            'user_id' => Yii::t('app', 'User ID'),
            'organization_id' => Yii::t('app', 'Organization ID'),
            'response' => Yii::t('app', 'Response'),
            'log_guide' => Yii::t('app', 'Log Guide'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Created At')
        ];
    }

    /**
     * Информация об операции
     * @return \yii\db\ActiveQuery
     */
    public function getOperation() {
        return $this->hasOne(AllServiceOperation::className(), ['service_id' => 'service_id', 'code' => 'operation_code']);
    }

    /**
     * Запись из внутреннего журнала, с подробными данными
     * @return array
     */
    public function getRecord() {
        $tableLog = (new Query())->select('log_table')
            ->from('all_service')
            ->where(['id' => $this->service_id])
            ->column(\Yii::$app->db_api);

        return (new Query())->select('*')
            ->from($tableLog)
            ->where(['guide' => $this->log_guide])
            ->one(\Yii::$app->db_api);
    }
}
