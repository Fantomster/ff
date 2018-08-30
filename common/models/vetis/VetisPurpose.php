<?php

namespace common\models\vetis;

use console\modules\daemons\components\UpdateDictInterface;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\Dicts;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\ListOptions;
use api\common\models\RabbitQueues;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use Yii;

/**
 * This is the model class for table "vetis_purpose".
 *
 * @property string $uuid
 * @property string $guid
 * @property int $last
 * @property int $active
 * @property int $status
 * @property string $next
 * @property string $previous
 * @property string $name
 * @property string $createDate
 * @property string $updateDate
 * @property object $purpose
 */
class VetisPurpose extends \yii\db\ActiveRecord implements UpdateDictInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_purpose';
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
            [['last', 'active', 'status'], 'integer'],
            [['createDate', 'updateDate'], 'safe'],
            [['uuid', 'guid', 'next', 'previous', 'name'], 'string', 'max' => 255],
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
            'status' => 'Status',
            'next' => 'Next',
            'previous' => 'Previous',
            'name' => 'Name',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
        ];
    }
    
    public function getPurpose()
    {
        return \yii\helpers\Json::decode($this->data);
    }
    
    public static function getPurposeList() {
        $models = self::find()
                ->select(['uuid', 'name'])
                ->where(['active' => true, 'last' => true])
                ->asArray()
                ->all();

        return ArrayHelper::map($models, 'uuid', 'name');
    }


    public static function getUpdateData($org_id)
    {
        try {
            $load = new Dicts();

            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercPurposeList'])->orderBy(['last_executed' => SORT_DESC])->one();
            if($queue == null) {
                $queue = new RabbitQueues();
                $queue->consumer_class_name = 'MercPurposeList';
                $queue->save();
            }

            //Формируем данные для запроса
            $data['method'] = 'getPurposeChangesList';
            $data['struct'] = ['listName' => 'purposeList',
                'listItemName' => 'purpose'
            ];

            $listOptions = new ListOptions();
            $listOptions->count = 100;
            $listOptions->offset = 0;

            $startDate =  ($queue === null) ?  date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)): $queue->last_executed;
            $instance = dictsApi::getInstance($org_id);
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
