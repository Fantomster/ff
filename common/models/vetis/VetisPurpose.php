<?php

namespace common\models\vetis;

use console\modules\daemons\components\UpdateDictInterface;
use api\common\models\RabbitQueues;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use Yii;
use yii\helpers\ArrayHelper;

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
     * Returns the primary key name(s) for this AR class.
     * The default implementation will return the primary key(s) as declared
     * in the DB table that is associated with this AR class.
     *
     * If the DB table does not declare any primary key, you should override
     * this method to return the attributes that you want to use as primary keys
     * for this AR class.
     *
     * Note that an array should be returned even for a table with single primary key.
     *
     * @return string[] the primary keys of the associated database table.
     */
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
            /*[['active','last'], 'filter', 'filter' => function ($value) {
                $value = ($value === 'true') ? 1 : 0;
                return $value;
            }],*/
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
                ->select(['guid', 'name'])
                ->where(['active' => true, 'last' => true])
                ->asArray()
                ->all();

        return ArrayHelper::map($models, 'guid', 'name');
    }


    public static function getUpdateData($org_id)
    {
        try {
            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercPurposeList'])->one();
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

            $listOptions['count'] = 1000;
            $listOptions['offset'] = 0;

            $queueDate = $queue->last_executed ?? $queue->start_executing;

            $startDate =  !isset($queueDate) ?  date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)): $queueDate;
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
        }
    }
}
