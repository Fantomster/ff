<?php

namespace common\models\vetis;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\UpdateDictInterface;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use Yii;

/**
 * This is the model class for table "vetis_foreign_enterprise".
 *
 * @property string $uuid
 * @property string $guid
 * @property int $last
 * @property int $active
 * @property int $type
 * @property string $next
 * @property string $previous
 * @property string $name
 * @property string $inn
 * @property string $kpp
 * @property string $country_guid
 * @property string $addressView
 * @property string $data
 * @property object
 * @property string $owner_guid
 * @property string $owner_uuid
 */
class VetisForeignEnterprise extends \yii\db\ActiveRecord implements UpdateDictInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_foreign_enterprise';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    public static function primaryKey()
    {
        return ['uuid'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'guid'], 'required'],
            [['uuid'], 'unique'],
            [['last', 'active', 'type'], 'integer'],
            [['data'], 'string'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'inn', 'kpp', 'country_guid', 'owner_guid', 'owner_uuid'], 'string', 'max' => 255],
            [['addressView'], 'string']
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uuid' => 'Uuid',
            'guid' => 'Guid',
            'last' => 'Last',
            'active' => 'Active',
            'type' => 'Type',
            'next' => 'Next',
            'previous' => 'Previous',
            'name' => 'Name',
            'inn' => 'Inn',
            'kpp' => 'Kpp',
            'country_guid' => 'Country Guid',
            'addressView' => 'Address View',
            'data' => 'Data',
        ];
    }
    
    public function getEnterprise()
    {
        return \yii\helpers\Json::decode($this->data);
    }

    public static function getUpdateData($org_id)
    {
        try {
            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercForeignEnterpriseList'])->one();
            if($queue == null) {
                $queue = new RabbitQueues();
                $queue->consumer_class_name = 'MercForeignEnterpriseList';
                $queue->save();
            }

            //Формируем данные для запроса
            $data['method'] = 'getForeignEnterpriseChangesList';
            $data['struct'] = ['listName' => 'enterpriseList',
                'listItemName' => 'enterprise'
            ];

            $listOptions['count'] = 1000;
            $listOptions['offset'] = 0;

            $queueDate = $queue->last_executed ?? $queue->start_executing;

            $startDate =  !isset($queueDate) ?  date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)): $queueDate;
            $instance = cerberApi::getInstance($org_id);
            $data['request'] = json_encode($instance->{$data['method']}(['listOptions' => $listOptions, 'startDate' => $startDate]));

            if (!empty($queue->organization_id)) {
                $queueName = $queue->consumer_class_name . '_' . $queue->organization_id;
            }
            else {
                $queueName = $queue->consumer_class_name;
            }

            //ставим задачу в очередь
            \Yii::$app->get('rabbit')
                ->setQueue($queueName)
                ->addRabbitQueue(json_encode($data));

        } catch (\Exception $e) {
            Yii::error($e->getMessage());
        }
    }
}
