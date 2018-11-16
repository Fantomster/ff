<?php

namespace common\models\vetis;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\UpdateDictInterface;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use Yii;

/**
 * This is the model class for table "vetis_russian_enterprise".
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
 * @property string $addressView
 * @property string $data
 * @property object $enterprise
 * @property string $owner_guid
 * @property string $owner_uuid
 * 
 * @property object $enterprise
 */
class VetisRussianEnterprise extends \yii\db\ActiveRecord implements UpdateDictInterface
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_russian_enterprise';
    }

    public static function primaryKey()
    {
        return ['uuid'];
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
            [['uuid', 'guid'], 'required'],
            [['uuid'], 'unique'],
            [['last', 'active', 'type'], 'integer'],
            [['data'], 'string'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'inn', 'kpp', 'owner_guid', 'owner_uuid'], 'string', 'max' => 255],
            [['addressView'], 'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uuid'        => 'Uuid',
            'guid'        => 'Guid',
            'last'        => 'Last',
            'active'      => 'Active',
            'type'        => 'Type',
            'next'        => 'Next',
            'previous'    => 'Previous',
            'name'        => 'Name',
            'inn'         => 'Inn',
            'kpp'         => 'Kpp',
            'addressView' => 'Address View',
            'data'        => 'Data',
        ];
    }

    public function getEnterprise()
    {
        // временно, потом только json
        require_once __DIR__ . '/../../../frontend/modules/clientintegr/modules/merc/helpers/api/cerber/Cerber.php';
        try {
            $result = \yii\helpers\Json::decode($this->data, false);
            if (isset($result->guid)) {
                return $result;
            }
        } catch (\Exception $e) {
            return \unserialize($this->data);
        }
        return null;
    }

    public static function getUpdateData($org_id)
    {
        try {
            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercRussianEnterpriseList'])->one();
            if ($queue == null) {
                $queue                      = new RabbitQueues();
                $queue->consumer_class_name = 'MercRussianEnterpriseList';
                $queue->save();
            }

            //Формируем данные для запроса
            $data['method'] = 'getRussianEnterpriseChangesList';
            $data['struct'] = ['listName'     => 'enterpriseList',
                'listItemName' => 'enterprise'
            ];

            $listOptions['count']  = 1000;
            $listOptions['offset'] = 0;

            $queueDate = $queue->last_executed ?? $queue->start_executing;

            $startDate       = gmdate("Y-m-d H:i:s", time() - 60*60*24*80); //!isset($queueDate) ? date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)) : $queueDate;
            $instance        = cerberApi::getInstance($org_id);
            $data['request'] = json_encode($instance->{$data['method']}(['listOptions' => $listOptions, 'startDate' => $startDate]));

            if (!empty($queue->organization_id)) {
                $queueName = $queue->consumer_class_name . '_' . $queue->organization_id;
            } else {
                $queueName = $queue->consumer_class_name;
            }

            //ставим задачу в очередь
            //\Yii::$app->get('sqsQueue')->sendMessage(Yii::$app->params['sqsQueues']['vetis']['enterprise'], $data);
            \Yii::$app->get('rabbit')
                ->setQueue($queueName)
                ->addRabbitQueue(json_encode($data));
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            echo $e->getMessage() . PHP_EOL;
        }
    }

}
