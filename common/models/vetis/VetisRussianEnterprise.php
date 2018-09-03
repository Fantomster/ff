<?php

namespace common\models\vetis;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\UpdateDictInterface;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\Cerber;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\ListOptions;
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
            /*[['active','last'], 'filter', 'filter' => function ($value) {
                $value = ($value === 'true') ? 1 : 0;
                return $value;
            }],*/
            [['last', 'active', 'type'], 'integer'],
            [['data'], 'string'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'inn', 'kpp', 'addressView'], 'string', 'max' => 255],
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
            $load = new Cerber();

            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercRussianEnterpriseList'])->orderBy(['last_executed' => SORT_DESC])->one();
            if($queue == null) {
                $queue = new RabbitQueues();
                $queue->consumer_class_name = 'MercRussianEnterpriseList';
                $queue->save();
            }

            //Формируем данные для запроса
            $data['method'] = 'getRussianEnterpriseChangesList';
            $data['struct'] = ['listName' => 'enterpriseList',
                'listItemName' => 'enterprise'
            ];

            $listOptions = new ListOptions();
            $listOptions->count = 1000;
            $listOptions->offset = 0;

            $startDate =  ($queue === null) ?  date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)): $queue->last_executed;
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
            echo $e->getMessage().PHP_EOL;
        }
    }
}
