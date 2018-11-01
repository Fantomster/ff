<?php

/**
 * Class Outertask
 * @package api_web\module\integration
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "outer_task".
 *
 * @property int $id ID записи данных о задаче
 * @property int $service_id ID сервиса, к которому относится данная задача
 * @property int $org_id ID организации, к которой относится данная задача
 * @property string $outer_guid ID соотвествующей транзакции во внешнем обработчике
 * @property int $oper_code ID атомарного действия во внешнем обработчике [all_service_operation:id]
 * @property string $requested_at Метка времени - дата и время формирования запроса
 * @property string $responced_at Метка времени - дата и время получения ответа в синхронном режиме
 * @property string $callbacked_at Метка времени - дата и время получения ответа в асинхронном режиме
 * @property int $retry Порядковый номер последней попытки выполнения задачи в виде отправки запроса
 * @property string $inner_guid ID соотвествующей транзакции в нашей системе
 * @property string $salespoint_id ID сертификата/лицензии на подключение к системе
 * @property int $int_status_id ID статуса задачи в нашей системе
 * @property int $broker_status_id ID статуса задачи во внешнем обработчике
 * @property int $client_status_id ID конечного статуса задачи во внешнем обработчике
 * @property string $broker_version Версия механизма API, задействованного во внешнем обработчике
 * @property int $total_parts Общее количество порций получаемых данных (при загрузке данных из источника порциями)
 * @property int $current_part Текущий номер порции получаемых данных (при загрузке данных из источника порциями)
 * @property int $waybill_id Связь с накладной
 */
class OuterTask extends ActiveRecord
{

    const STATUS_REQUESTED = 1;
    const STATUS_CALLBACKED = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outer_task';
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
            [['service_id', 'org_id', 'oper_code', 'retry', 'int_status_id', 'broker_status_id', 'client_status_id', 'total_parts', 'current_part'], 'integer'],
            [['requested_at', 'responced_at', 'callbacked_at'], 'safe'],
            [['outer_guid', 'inner_guid'], 'string', 'max' => 45],
            [['salespoint_id', 'broker_version'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_id' => 'Service ID',
            'org_id' => 'Org ID',
            'outer_guid' => 'Outer Guid',
            'oper_code' => 'Oper Code',
            'requested_at' => 'Requested At',
            'responced_at' => 'Responced At',
            'callbacked_at' => 'Callbacked At',
            'retry' => 'Retry',
            'inner_guid' => 'Inner Guid',
            'salespoint_id' => 'Salespoint ID',
            'int_status_id' => 'Int Status ID',
            'broker_status_id' => 'Broker Status ID',
            'client_status_id' => 'Client Status ID',
            'broker_version' => 'Broker Version',
            'total_parts' => 'Total Parts',
            'current_part' => 'Current Part',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'requested_at',
                'updatedAtAttribute' => ['responced_at', 'callbacked_at'],
                'value' => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

}
